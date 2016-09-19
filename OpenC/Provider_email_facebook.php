<?php
namespace OpenC;

use OpenC\OAuth2;
use Exception;

class Provider_email_facebook extends OAuth2
{
    public function __construct() {
        $this->facebook_authorise = 'https://graph.facebook.com/v2.7/oauth/access_token';
        $this->facebook_graph = 'https://graph.facebook.com/v2.7/me?';
        $this->facebook_params = [
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => '',
            'auth_url' => 'https://www.facebook.com/dialog/oauth?'
        ];
    }
    public function retrieve_email() {
        if (isset($_GET['code']) && isset($_GET['state'])) {
            if ($_GET['state'] !== $_SESSION['state']) die();
            $request_params = $this->facebook_params;
            $request_params['code'] = $_GET['code'];
            return $this->access_provider(
                $this->facebook_authorise,
                $request_params,
                function ($response) {
                    $response = json_decode($response, true);
                    if (!isset($response['access_token'])) throw new Exception('No access token.');
                    $access_token = $response['access_token'];
                    $request_params = [
                        'fields' => 'email',
                        'access_token' => $access_token
                    ];
                    return $this->access_provider(
                        $this->facebook_graph,
                        $request_params,
                        function ($response) {
                            $response = json_decode($response, true);
                            if (!isset($response['email'])) throw new Exception('No email field.');
                            return $response['email'];
                        },
                        'GET'
                    );
                }
            );
        }
        else {
            $state = md5(openssl_random_pseudo_bytes(16));
            $_SESSION['state'] = $state;
            $facebook_params = $this->facebook_params;
            $params = [
                'client_id' => $facebook_params['client_id'],
                'redirect_uri' => rawurlencode($facebook_params['redirect_uri']),
                'state' => $state,
                'scope' => 'email'
            ];
            $uri = $facebook_params['auth_url'];
            foreach ($params as $key => $value) {
                $uri .= "$key=$value&";
            }
            $uri = rtrim($uri, '&');
            header("Location: $uri");
            die();
        }
    }
}