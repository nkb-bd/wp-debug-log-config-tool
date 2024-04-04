<?php

namespace DebugLogConfigTool\Controllers;

use DebugLogConfigTool\Classes\DLCT_Bootstrap;

class LogController
{
    private $logFilePath;
    private $originalLogFilePath;
    
    public function __construct()
    {
        $dlct_bootstrap = DLCT_Bootstrap::getInstance();
        $debugPath = $dlct_bootstrap->setRandomLogPath();
        $this->logFilePath = apply_filters('wp_dlct_log_file_path', $debugPath);
    }
    
    public function get()
    {
        Helper::verifyRequest();
        try {
            if (!file_exists($this->logFilePath)) {
                wp_send_json_error(['message' => 'Debug file not found']);
            }
            
            $logData = $this->loadLogs();
    
            wp_send_json_success([
                'success' => true,
                'log_path'    => $this->logFilePath,
                'logs'        => $logData['logs'] ?? '',
                'error_types' => $logData['unique_error_types'] ?? '',
                'file_size'   => $this->getFilesize(),
            ]);
        } catch (\Exception $e) {
            wp_send_json_success([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    public function getFilesize()
    {
        return file_exists($this->logFilePath) ? filesize($this->logFilePath) : false;
    }
    
    public function loadLogs($limit = false)
    {
        if (!file_exists($this->logFilePath)) {
            return [];
        }
        self::maybeCopyLogFromDefaultLogFile();
        
        $fh = fopen($this->logFilePath, 'r');
        
        if (!$fh) {
            return '';
        }
        
        $logs = [];
        $errorTypes = []; // Initialize error types array
        $i = 0;
        
        while ($line = @fgets($fh)) {
            $logEntry = $this->parseLogLine($line);
         
            
            if ($logEntry !== false) {
                $logs[] = $logEntry;
                
                // Extract and store error type
                if (is_array($logEntry) && !empty($logEntry['error_type'])) {
                    $errorTypes[$logEntry['error_type']] = true;
                }
                
                $i++; // Increment the counter
                
                if ($limit && $i >= $limit) {
                    break;
                }
            }
        }
       
        fclose($fh);
        $uniqueErrorTypes = array_keys($errorTypes);
        
        return [
            'logs'               => array_reverse($logs),
            'unique_error_types' => $uniqueErrorTypes,
        ];
    }
    
    
    
    private function parseLogLine($line)
    {
        $sep = '$!$';
        $line = preg_replace("/^\[([0-9a-zA-Z-]+) ([0-9:]+) ([a-zA-Z_\/]+)\] (.*)$/i", "$1" . $sep . "$2" . $sep . "$3" . $sep . "$4", $line);
        $parts = explode($sep, $line);
        
        if (count($parts) >= 4) {
            $info = stripslashes($parts[3]);
            $time = strtotime($parts[1]);
            
            $pluginName = $this->extractPluginName($info);
            
            // Check if the line contains a PHP array
            if (preg_match('/\[(.*?)\]/', $info, $matches)) {
                // Extracting the PHP array string
                $arrayString = $matches[1];
                return [
                    'date' => '',
                    'time' => '',
                    'timezone' => '',
                    'details' => $arrayString,
                    'plugin_name' => '',
                    'data_array' => ''
                ];
            } else {
                // Extract file location and line number
                preg_match('/^(.*?) on line (\d+)/', $info, $errorDetails);
                $fileLocation = isset($errorDetails[1]) ? trim($errorDetails[1]) : '';
                // Extract file location using regular expression
                preg_match('/in\s(.*?)(?=\(\d+\))/', $fileLocation, $locationMatches);
                $fileLocation = isset($locationMatches[1]) ? $locationMatches[1] : '';
                // Extracted file location
                $lineNumber = isset($errorDetails[2]) ? $errorDetails[2] : '';
                
                $errorType = $this->extractErrorType($line);
                return [
                    'date' => date('d/m/y', strtotime($parts[0])),
                    'time' => human_time_diff($time, current_time('U')) . ' ago',
                    'timezone' => $parts[2],
                    'details' => $info,
                    'error_type' => $errorType,
                    'plugin_name' => ucwords(str_replace('-', ' ', $pluginName)),
                    'file_location' => $fileLocation,
                    'line_number' => $lineNumber
                ];
            }
        }
        
        return false;
    }
    
    private function extractErrorType($logLine)
    {
        $pattern = '/PHP (Fatal error|Notice):/';
        if (preg_match($pattern, $logLine, $matches)) {
            return trim($matches[1]);
        }
        
        return '';
    }
    
    private function extractPluginName($info)
    {
        $pluginName = '';
        if (preg_match('/\/plugins\/([^\/]+)\//', $info, $pluginMatches)) {
            $pluginName = $pluginMatches[1];
            if (!file_exists(WP_PLUGIN_DIR . '/' . $pluginName . '/' . $pluginName . '.php')) {
                $pluginName = '';
            }
        }
        
        return $pluginName;
    }
    
    public function clear()
    {
        Helper::verifyRequest();
    
        if (file_exists($this->logFilePath)) {
            $open = fopen($this->logFilePath, "r+");
            
            if (!$open) {
                $msg = 'Could not open file!';
            } else {
                file_put_contents($this->logFilePath, "");
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
    
    /**
     * @param mixed $logFilePath
     */
    public function setLogFilePath($logFilePath)
    {
        $this->logFilePath = $logFilePath;
    }
    
    private function getRandomPath()
    {
        if (get_option('dlct_debug_file_path_generated') == 'yes') {
            $debugPath = get_option('dlct_debug_file_path');
        } else {
            $randomString = uniqid();
            $debugPath = apply_filters('dlct_debug_file_path', ABSPATH . "wp-content/debug-" . $randomString . ".log");
            update_option('dlct_ddebug_file_path', $debugPath, false);
            update_option('dlct_debug_file_path_generated', 'yes', false);
        }
        return $debugPath;
    }
    
    public function maybeCopyLogFromDefaultLogFile()
    {
        if (get_option('dlct_debug_file_path_generated') !== 'yes') {
            return; // If the debug file path is not generated, exit the function
        }
    
        if (!get_option('dlct_log_file_copied')) {
            $currentLogPath = get_option('dlct_debug_file_path');
            $defaultLogPath = apply_filters('wp_dlct_default_log_file_path', WP_CONTENT_DIR . '/debug.log');
        
            $content = file_get_contents($defaultLogPath);
            file_put_contents($currentLogPath, $content);
            file_put_contents($defaultLogPath, 'Content Moved');
        
            update_option('dlct_log_file_copied', true);
        }
    }
}
