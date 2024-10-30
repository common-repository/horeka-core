<?php 
/**
 * @package  HorekaCore
 */
namespace HorekaCore\Base;

class ErrorLog
{
    public static function log( array $data, array $meta = array() )
    {
        $uploads  = wp_upload_dir( null, false );
        $logs_dir = $uploads['basedir'] . '/restaurant-solution-logs';

        if ( ! is_dir( $logs_dir ) ){
            mkdir( $logs_dir, 0755, true );
        }

        $file = fopen( $logs_dir . '/' . 'restaurant-solution.log', 'a+' );

        rewind($file);

        $error = date('d-m-Y H:i:s', strtotime('+3 hour')) . ' ' . $log_data['error_title'] . ': ' . $log_data['error_content'] . PHP_EOL;
        
        if( !empty($log_meta) ) {
            $error .= date('d-m-Y H:i:s', strtotime('+3 hour')) . ' ';
            if( $log_meta['customer_ip'] !== NULL ) {
                $error .= $log_meta['customer_ip'];
            }

            if( $log_meta['user_id'] !== NULL ) {
                $error .= ' - wp user_id: ' . $log_meta['user_id'];
            }

            if( $log_meta['status'] !== NULL ) {
                $error .= ' - API Response Status: ' . $log_meta['status'];
            }
            
            $error .= PHP_EOL;
        }
        
        fwrite($file, $error); 
        fclose($file);
    }
}