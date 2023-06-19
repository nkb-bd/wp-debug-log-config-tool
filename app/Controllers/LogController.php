<?php
namespace DebugLogConfigTool\Controllers;

class LogController
{
    public function get(){
      
        try {
            $file = WP_CONTENT_DIR . '/debug.log';
            if(!file_exists( $file )){
                $file = ABSPATH . 'wp-config.php';
            }
            $file =  apply_filters('wp_dlct_log_file_path', $file);
            if( !file_exists( $file )){
                wp_send_json_error(['message' => 'Debug file not found']);
            }
            
            wp_send_json_success( [
                'logs'    => $this->loadLogs(),
                'file_size' => $this->getFilesize(),
            ]);
        }
        catch (\Exception $e) {
            wp_send_json_success( [
                'message'    => $e->getMessage(),
            ]);
        }
        
    }
    public function getFilesize()
    {
        
        $file = WP_CONTENT_DIR . '/debug.log';
        if (file_exists($file)) {
            return filesize($file);
        }
        return false;
    }
    public function loadLogs()
    {
        
        $file = WP_CONTENT_DIR . '/debug.log';
        $file =  apply_filters('wp_debuglog_log_file_path', $file);
        if(!file_exists($file)) {
            return [];
        }
        $fh = fopen($file, 'r');
        if (!$fh) {
            return '';
        }
        $logs = [];
        $i = 0;
        while ($line = @fgets($fh)) {
            $sep = '$!$';
    
            $line = preg_replace("/^\[([0-9a-zA-Z-]+) ([0-9:]+) ([a-zA-Z_\/]+)\] (.*)$/i", "$1".$sep."$2".$sep."$3".$sep."$4", $line);
            $parts = explode($sep, $line);
            if (count($parts) >= 4) {
                $info = stripslashes($parts[3]);
                $time = strtotime($parts[1] );
                
                $logs[] = [
                    'date' => date('d/m/y', strtotime($parts[0])),
                    'time' => human_time_diff($time,time()) .' ago',
                    'timezone' => $parts[2],
                    'details' => $info,
                ];
    
            }
    
    
        }
        
        @fclose($fh);
        return $logs;
    }
    
    public function clear()
    {
        $file = WP_CONTENT_DIR . '/debug.log';
        $file = apply_filters('wp_debuglog_log_file_path', $file);
        if (file_exists($file)) {
            $open = fopen($file, "r+");
            if ($open != true) {
                $msg = 'Could not open file!';
            } else {
                file_put_contents($file, "");
                $msg = 'Log cleared';
            }
        } else {
            $msg = 'No log file yet available!';
        }
    
        wp_send_json_success($msg);
        
    }
    public function gmt_to_local_timestamp($gmt_timestamp)
    {
        $iso_date = strtotime('Y-m-d H:i:s', $gmt_timestamp);
        return get_date_from_gmt($iso_date, 'Y-m-d h:i a');
    }
}
