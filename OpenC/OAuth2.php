<?php
namespace OpenC;

use Exception;

class OAuth2
{
    protected function curl_options($url, $postfields = null) {
        $options = [
            CURLOPT_VERBOSE => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_URL => $url
        ];
        if ($postfields === null) unset($options[CURLOPT_POSTFIELDS]);
        return $options;
    }
    protected function access_provider($url, $request_params, $cb, $method = 'POST') {
        $request = http_build_query($request_params);
        if ($method === 'GET') {
            $url .= $request;
            $request = null;
        }
        $ch = curl_init();
        $this->curl = $ch;
        $curl_options = $this->curl_options($url, $request);
        curl_setopt_array($ch, $curl_options);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        }
        else {
            curl_close($ch);
            try {
                $response = $cb($response);
            }
            catch (Exception $e) {
                echo $e->getMessage();
                return false;
            }
            return $response;
        }
    }
}