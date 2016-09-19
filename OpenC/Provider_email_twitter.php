<?php
namespace OpenC;

use OpenC\OAuth1;
use Exception;

class Provider_email_twitter extends OAuth1
{
    public function __construct() {
        $this->twitter_api_token = 'https://api.twitter.com/oauth/request_token';
        $this->twitter_api_auth = 'https://api.twitter.com/oauth/access_token';
        $this->twitter_api_verify = 'https://api.twitter.com/1.1/account/verify_credentials.json';
        $this->twitter_params = [
            'consumer_key' => '',
            'client_secret' => '',
            'redirect_uri' => ''
        ];
    }
    public function retrieve_email() {
        if (isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])) {
            if ($_SESSION['token'] !== $_GET['oauth_token']) die();
            return $this->access_provider('auth', $_GET['oauth_token'], $_GET['oauth_verifier']);
        }
        $this->access_provider('token');
    }
}