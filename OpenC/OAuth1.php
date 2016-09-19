<?php
namespace OpenC;

use Exception;

class OAuth1
{
    private function build_params($params, $separator) {
        $base = '';
        foreach ($params as $key => $value) {
            $base .= $key . '=' . $value . $separator;
        }
        $base = rtrim($base, $separator);
        return $base;
    }
    public function request_token($api = 'token', $token = null, $token_secret = null) {
        if ($api === 'token') $url = $this->twitter_api_token;
        if ($api === 'auth') $url = $this->twitter_api_auth;
        if ($api === 'verify') $url = $this->twitter_api_verify;
        $method = 'POST';
        $curl_options = [
            CURLOPT_USERAGENT => 'development testing',
            CURLOPT_ENCODING => 'gzip',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => ''
        ];
        $verify_params = [
            'include_email' => 'true'
        ];
        $oauth_params = [
            'oauth_callback' => rawurlencode($this->twitter_params['redirect_uri']),
            'oauth_consumer_key' => $this->twitter_params['consumer_key'],
            'oauth_nonce' => md5(openssl_random_pseudo_bytes(16)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_token' => $token,
            'oauth_verifier' => $token_secret,
            'oauth_version' => '1.0'
        ];
        $base_url = $url;
        $base_params = $oauth_params;
        if ($api === 'verify') {
            unset($oauth_params['oauth_callback']);
            $method = 'GET';
            $base_params = $verify_params + $oauth_params;
            $url .= '?include_email=true';
        }

        $base = "$method&" . rawurlencode($base_url) . '&' . rawurlencode($this->build_params($base_params, '&'));
        $oauth_params['oauth_signature'] = rawurlencode(base64_encode(hash_hmac('sha1', $base, $this->twitter_params['client_secret'] . '&' . $token_secret, true)));
        $auth_header = $this->build_params($oauth_params, ',');
        $ch = curl_init();

        $curl_options[CURLOPT_HTTPHEADER] = ['Accept: application/json', 'Authorization: OAuth ' . $auth_header];
        $curl_options[CURLOPT_URL] = $url;
        curl_setopt_array($ch, $curl_options);
        if ($api === 'verify') curl_setopt($ch, CURLOPT_HTTPGET, true);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        }
        else {
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($http_code !== 200) {
                return false;
            }
            if ($api === 'verify') {
                $response = json_decode($response, true);
                if (!isset($response['email'])) throw new Exception('No email field.');
                return $response['email'];
            }
            $response = explode('&', $response);
            $token = explode('=', $response[0]);
            $secret = explode('=', $response[1]);
            $oauth_token = $token[1];
            $oauth_token_secret = $secret[1];
            if ($api === 'auth') {
                if ($token[0] !== 'oauth_token' || $secret[0] !== 'oauth_token_secret') {
                    throw new Exception('Auth token mismatch.');
                }
                return $this->request_token('verify', $oauth_token, $oauth_token_secret);
            }
            if ($api === 'token') {
                $token = explode('=', $response[0]);
                if ($token[0] !== 'oauth_token' || $secret[0] !== 'oauth_token_secret') {
                    throw new Exception('Token mismatch.');
                }
                $_SESSION['token'] = $token[1];
                header("Location: https://api.twitter.com/oauth/authenticate?$response[0]");
                die();
            }
        }
    }
    public function access_provider($api = 'token', $token = null, $token_secret = null) {
        try {
            $response = $this->request_token($api, $token, $token_secret);
        }
        catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
        return $response;
    }
}