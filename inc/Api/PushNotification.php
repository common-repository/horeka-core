<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Api;

use HorekaCore\Base\BaseController;

class PushNotification
{
    public static function makeNotification( $route, $data = array() )
    {
        $args = array();
        $homeUrl = get_home_url();
        
        $baseController = new BaseController();
        $plugin_options = $baseController->getPluginOptions();

        $apiUrl = $plugin_options['api_url'];
        $apiKey = $plugin_options['api_key'];

        if( $apiUrl != '' && $apiKey != '' ) {
            $apiUrl = $apiUrl . $route;

            $args['body'] = json_encode($data);
            $args['timeout'] = '120';
            $args['redirection'] = '5';
            $args['httpversion'] = '1.0';
            $args['blocking'] = true;

            $args['headers'] = array(
                'ApiKey' => $apiKey,
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
                'Origin' => $homeUrl
            );

            $current_language = apply_filters( 'wpml_current_language', NULL );

            if( $current_language && $current_language != 'ro' ) {
				$args['headers']['Accept-Language'] = $current_language;
			}
            
            $response = wp_remote_post( $apiUrl, $args );

            return json_decode($response['body']);
        }

        return false;
    }

    public static function makeNotificationGet( $route, $data = array() )
    {
        $args = array();
        $homeUrl = get_home_url();
        
        $baseController = new BaseController();
        $plugin_options = $baseController->getPluginOptions();

        $apiUrl = $plugin_options['api_url'];
        $apiKey = $plugin_options['api_key'];

        if( $apiUrl != '' && $apiKey != '' ) {
            $apiUrl = $apiUrl . $route;

            $args['body'] = json_encode($data);

            $args['headers'] = array( 
                'ApiKey' => $apiKey
            );

            $current_language = apply_filters( 'wpml_current_language', NULL );

            if( $current_language && $current_language != 'ro' ) {
				$args['headers']['Accept-Language'] = $current_language;
			}
            
            $response = (new self)->wp_request_get_custom( $apiUrl, $apiKey, $data );

            return json_decode($response);
        }

        return false;
    }

    private function wp_request_get_custom( $apiUrl, $apiKey, $data )
    {
        $ch = curl_init();

        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'ApiKey: ' . $apiKey
        );

        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        
        $body = json_encode($data);
    
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
        curl_setopt($ch, CURLOPT_POSTFIELDS,$body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
        $response = curl_exec($ch);
        
        return $response;
    }

}