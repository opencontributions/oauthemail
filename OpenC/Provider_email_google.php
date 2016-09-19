<?php
namespace OpenC;

use OpenC\OAuth2;
use Exception;

class Provider_email_google extends OAuth2
{
    public function __construct() {
        $this->google_authorise = 'https://accounts.google.com/o/oauth2/token';
        $this->google_params = [
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => '',
            'grant_type' => 'authorization_code',
            'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth?'
        ];
    }
    public function retrieve_email() {
        if (isset($_GET['code']) && isset($_GET['state'])) {
            if ($_GET['state'] !== $_SESSION['state']) die();
            $request_params = $this->google_params;
            $request_params['code'] = $_GET['code'];
            return $this->access_provider(
                $this->google_authorise,
                $request_params,
                function($response) {
                    $response = json_decode($response, true);
                    if (!isset($response['id_token'])) throw new Exception('No ID token.');
                    $id_token = $response['id_token'];
                    $token = explode('.', $id_token);
                    if (!isset($token[1])) throw new Exception('No JWT payload.');
                    $response = json_decode(base64_decode($token[1]), true);
                    if (!isset($response['email'])) throw new Exception('No email field.');
                    return $response['email'];
                }
            );
        }
        else {
            $state = md5(openssl_random_pseudo_bytes(16));
            $_SESSION['state'] = $state;
            $google_params = $this->google_params;
            $params = [
                'client_id' => $google_params['client_id'],
                'response_type' => 'code',
                'scope' => 'email', 
                'redirect_uri' => rawurlencode($google_params['redirect_uri']),
                'state' => $state,
            ];
            $uri = $google_params['auth_url'];
            foreach ($params as $key => $value) {
                $uri .= "$key=$value&";
            }
            $uri = rtrim($uri, '&');
            header("Location: $uri");
            die();
        }    
    }
}