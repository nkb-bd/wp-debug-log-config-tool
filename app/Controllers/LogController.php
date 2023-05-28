<?php
namespace DebugLogConfigTool\Controllers;

class LogController
{
    public function get(){
        $file = WP_CONTENT_DIR . '/debug.log';
        if(!file_exists( $file )){
            $file = ABSPATH . 'wp-config.php';
        }
        $file =  apply_filters('wp_dlct_log_file_path', $file);
        if( !file_exists( $file )){
            wp_send_json_error(['message' => 'Debug file not found']);
        }
    
        wp_send_json_success( [
                'workflow' => "ok",
            'logs'    => $this->loadDebugLog(),
            'file_size' => $this->getFilesize(),
        ]);
    }
    public function getFilesize()
    {
        
        $file = WP_CONTENT_DIR . '/debug.log';
        if (file_exists($file)) {
            return filesize($file);
        }
        return false;
    }
    public function loadDebugLog()
    {
        
        $file = WP_CONTENT_DIR . '/debug.log';
        $file =  apply_filters('wp_debuglog_log_file_path', $file);
        if(!file_exists($file)) {
            return [];
        }
        $fh = fopen($file, 'r');
        if (!$fh) {
            return 'fail_open';
        }
        $logs = [];
        $i = 0;
        while ($line = @fgets($fh)) {
            $sep = '$!$';
            $line = preg_replace("/^\[([0-9a-zA-Z-]+) ([0-9:]+) ([a-zA-Z_\/]+)\] (.*)$/i", "$1" . $sep . "$2" . $sep . "$3" . $sep . "$4", $line);
            $parts = explode($sep, $line);
            
            if (count($parts) >= 4) {
                array_push($logs, array(
                    'line'     => $i++,
                    'date'     => date('Y-m-d', strtotime($parts[0])),
                    'time'     => $parts[1],
                    'timezone' => $parts[2],
                    'details'  => stripslashes($parts[3]),
                ));
                
            }
            if ($i >= 50) {
                break; //stop loading more for now @todo
            }
            
            
        }
        fclose($fh);
        return $logs;
    }
}
