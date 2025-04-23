<?php

namespace DebugLogConfigTool\Controllers;

use DebugLogConfigTool\Classes\DLCT_Bootstrap;

class LogController
{
    private $logFilePath;
    private $originalLogFilePath;

    public function __construct()
    {
        $debugPath =  $this->setRandomLogPath();
        $this->logFilePath = apply_filters('wp_dlct_log_file_path', $debugPath);
    }

    public function get()
    {
        Helper::verifyRequest();

        if(empty( $this->logFilePath )){
            $this->logFilePath = $this->setRandomLogPath();
        }
        try {
            if (!file_exists($this->logFilePath)) {
                wp_send_json_error(['message' => 'Debug log file not found']);
            }

            // Check if we should only get new logs
            $lastModified = isset($_GET['last_modified']) ? intval($_GET['last_modified']) : 0;
            $lastSize = isset($_GET['last_size']) ? intval($_GET['last_size']) : 0;
            $currentSize = $this->getFilesize();

            // If file hasn't changed, return empty logs with current size
            if ($lastSize > 0 && $lastSize === $currentSize) {
                wp_send_json_success([
                    'success' => true,
                    'log_path' => $this->logFilePath,
                    'logs' => [],
                    'error_types' => [],
                    'file_size' => $currentSize,
                    'last_modified' => time(),
                    'no_changes' => true
                ]);
                return;
            }

            // Load logs, potentially only new ones
            $logData = $this->loadLogs(false, $lastModified);
            $isSaveQueryOn = (new \DebugLogConfigTool\Controllers\ConfigController())->getValue('SAVEQUERIES');

            wp_send_json_success([
                'success' => true,
                'log_path'    => $this->logFilePath,
                'logs'        => $logData['logs'] ?? '',
                'error_types' => $logData['unique_error_types'] ?? '',
                'file_size'   => $currentSize,
                'last_modified' => time(),
                'query_logs'    => $this->getQueryLogs($isSaveQueryOn),
                'is_save_query_on' => $isSaveQueryOn === true || $isSaveQueryOn == 'true'
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

    public function loadLogs($limit = false, $lastModified = 0)
    {
        // Check if log file exists

        if (!file_exists($this->logFilePath)) {
            // Log file doesn't exist, return empty array
            return [];
        }

        $fileSize = filesize($this->logFilePath);

        if ($fileSize === 0) {
            // Log file is empty
            return [
                'logs' => [],
                'unique_error_types' => [],
            ];
        }

        self::maybeCopyLogFromDefaultLogFile();

        $fh = fopen($this->logFilePath, 'r');

        if (!$fh) {
            // Failed to open log file
            return '';
        }

        $logs = [];
        $errorTypes = []; // Initialize error types array
        $i = 0;
        $fileModTime = filemtime($this->logFilePath);

        // If we're only looking for new logs and file hasn't been modified, return empty
        if ($lastModified > 0 && $fileModTime <= $lastModified) {
            // File not modified since last check
            fclose($fh);
            return [
                'logs' => [],
                'unique_error_types' => [],
            ];
        }

        // If we're looking for new logs, try to optimize by seeking to the end minus a reasonable buffer
        if ($lastModified > 0) {
            $fileSize = filesize($this->logFilePath);
            $seekPosition = max(0, $fileSize - 50000); // Look at last ~50KB of the file for new logs
            fseek($fh, $seekPosition);

            // If we're not at the beginning, discard the first line as it might be partial
            if ($seekPosition > 0) {
                fgets($fh);
            }
        }

        $lineCount = 0;
        $parsedCount = 0;
        $failedCount = 0;

        while ($line = @fgets($fh)) {
            $lineCount++;

            // Skip empty lines
            if (trim($line) === '') {
                continue;
            }

            // Debug first few lines
            // Debug line processing removed

            $logEntry = $this->parseLogLine($line);

            if ($logEntry !== false) {
                $parsedCount++;

                // If we're only looking for new logs, check the timestamp
                if ($lastModified > 0) {
                    $entryTime = strtotime($logEntry['date'] . ' ' . $logEntry['time']);
                    if ($entryTime && $entryTime <= $lastModified) {
                        continue; // Skip older entries
                    }
                }

                $logs[] = $logEntry;

                // Extract and store error type
                if (is_array($logEntry) && !empty($logEntry['error_type'])) {
                    $errorTypes[$logEntry['error_type']] = true;
                }

                $i++; // Increment the counter

                if ($limit && $i >= $limit) {
                    break;
                }
            } else {
                $failedCount++;

                // Debug first few failed lines
                // Debug failed parsing removed
            }
        }

        fclose($fh);
        $uniqueErrorTypes = array_keys($errorTypes);

        error_log('Debug Log Config Tool: Total lines read: ' . $lineCount);
        error_log('Debug Log Config Tool: Successfully parsed: ' . $parsedCount);
        error_log('Debug Log Config Tool: Failed to parse: ' . $failedCount);
        error_log('Debug Log Config Tool: Logs collected: ' . count($logs));
        error_log('Debug Log Config Tool: Error types found: ' . implode(', ', $uniqueErrorTypes));

        return [
            'logs'               => array_reverse($logs),
            'unique_error_types' => $uniqueErrorTypes,
        ];
    }



    private function parseLogLine($line)
    {
        // Try to handle different log formats
        $sep = '$!$';

        // Standard WordPress log format: [YYYY-MM-DD HH:MM:SS timezone] message
        $standardFormat = preg_replace("/^\[([0-9a-zA-Z-]+) ([0-9:]+) ([a-zA-Z_\/]+)\] (.*)$/i", "$1" . $sep . "$2" . $sep . "$3" . $sep . "$4", $line);

        // Check if the line was matched by the standard format
        if ($standardFormat !== $line) {
            $parts = explode($sep, $standardFormat);
            if (count($parts) >= 4) {
                // Standard format matched
                return $this->parseStandardFormat($parts, $line);
            }
        }

        // Alternative format: [YYYY-MM-DD HH:MM:SS] message (no timezone)
        if (preg_match("/^\[([0-9-]+) ([0-9:]+)\] (.*)$/i", $line, $matches)) {
            $logTime = strtotime($matches[1] . ' ' . $matches[2]);
            if ($logTime === false) {
                // If we can't parse the time, use current time
                $logTime = current_time('U');
            }

            return [
                'date' => date('d/m/y', $logTime),
                'time' => $this->formatTimeAgo($logTime),
                'raw_time' => $logTime,
                'timezone' => '',
                'details' => $matches[3],
                'error_type' => $this->extractErrorType($matches[3]),
                'plugin_name' => $this->extractPluginName($matches[3]),
                'file_location' => '',
                'line_number' => ''
            ];
        }

        // Simple format: just the message without timestamp
        if (strpos($line, 'PHP ') === 0 && (strpos($line, 'Notice') !== false || strpos($line, 'Warning') !== false ||
            strpos($line, 'Fatal error') !== false || strpos($line, 'Parse error') !== false)) {
            $currentTime = current_time('U');

            return [
                'date' => date('d/m/y', $currentTime),
                'time' => $this->formatTimeAgo($currentTime),
                'raw_time' => $currentTime,
                'timezone' => '',
                'details' => $line,
                'error_type' => $this->extractErrorType($line),
                'plugin_name' => $this->extractPluginName($line),
                'file_location' => '',
                'line_number' => ''
            ];
        }

        // If we couldn't parse the line with any known format, return false
        return false;
    }

    private function parseStandardFormat($parts, $line)
    {
        $info = stripslashes($parts[3]);
        $time = strtotime($parts[1]);

        $pluginName = $this->extractPluginName($info);
        $errorType = $this->extractErrorType($info);

        // Check if the line contains a PHP array
        if (preg_match('/\[(.*?)\]/', $info, $matches)) {
            // Extracting the PHP array string
            $arrayString = $matches[1];
            return [
                'date' => date('d/m/y', $time),
                'time' => $this->formatTimeAgo($time),
                'raw_time' => $time,
                'timezone' => $parts[2],
                'details' => $arrayString,
                'error_type' => '',
                'plugin_name' => '',
                'file_location' => '',
                'line_number' => '',
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

            // Extract stack trace if available
            $stackTrace = [];
            if (strpos($info, 'Stack trace:') !== false) {
                preg_match('/Stack trace:[\s\S]*$/i', $info, $stackMatches);
                if (!empty($stackMatches[0])) {
                    $stackLines = explode("\n", $stackMatches[0]);
                    foreach ($stackLines as $stackLine) {
                        $trimmedLine = trim($stackLine);
                        // Match both standard stack trace lines and the final 'thrown in' line
                        if (preg_match('/^#\d+\s+/', $trimmedLine) || strpos($trimmedLine, 'thrown in') === 0) {
                            $stackTrace[] = $trimmedLine;
                        }
                    }
                }
            }

            // If no stack trace was found but the error message contains line numbers and file paths,
            // create a simple stack trace from the error message
            if (empty($stackTrace) && preg_match('/in\s+([^\s]+)\s+on\s+line\s+(\d+)/', $info, $matches)) {
                $errorFile = $matches[1];
                $errorLine = $matches[2];
                $stackTrace[] = "Error occurred in {$errorFile} on line {$errorLine}";
            }

            return [
                'date' => date('d/m/y', $time),
                'time' => $this->formatTimeAgo($time),
                'raw_time' => $time,
                'timezone' => $parts[2],
                'details' => $info,
                'error_type' => $errorType,
                'plugin_name' => ucwords(str_replace('-', ' ', $pluginName)),
                'file_location' => $fileLocation,
                'line_number' => $lineNumber,
                'stack_trace' => $stackTrace
            ];
        }

        return false;
    }

    private function extractErrorType($logLine)
    {
        $pattern = '/PHP (Fatal error|Notice|Warning|Parse error|Deprecated):/';
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

    /**
     * Format a timestamp into a human-readable time ago string
     *
     * @param int $timestamp Unix timestamp
     * @return string Formatted time string
     */
    private function formatTimeAgo($timestamp)
    {
        $current_time = current_time('U');
        $time_diff = $current_time - $timestamp;

        // If the log is from the future (server time issues), show it as 'just now'
        if ($time_diff < 0) {
            return 'Just Now';
        }

        // Use WordPress's human_time_diff function but with some improvements
        if ($time_diff < 60) {
            return 'Just Now';
        } elseif ($time_diff < 3600) {
            $mins = round($time_diff / 60);
            return $mins . ' ' . _n('minute', 'minutes', $mins, 'debug-log-config-tool') . ' ago';
        } elseif ($time_diff < 86400) {
            $hours = round($time_diff / 3600);
            return $hours . ' ' . _n('hour', 'hours', $hours, 'debug-log-config-tool') . ' ago';
        } elseif ($time_diff < 604800) {
            $days = round($time_diff / 86400);
            return $days . ' ' . _n('day', 'days', $days, 'debug-log-config-tool') . ' ago';
        } else {
            // For older logs, show the actual date and time
            return date('M j, Y g:i a', $timestamp);
        }
    }

    public function clearDebugLog()
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

    public function clearQueryLog()
    {
        Helper::verifyRequest();

        update_option('dlct_db_query_log', '',false);

        $msg = 'clear query log';


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
        if (get_option('dlct_debug_file_path_generated') != 'yes') {
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

    public function getQueryLogs($isSaveQueryOn)
    {
        if(!$isSaveQueryOn){
            return  [];
        }
        global $wpdb;
        $allQueries = get_option('dlct_db_query_log');
        if(!is_array($allQueries) || empty($allQueries)){
            return [];
        }
        $queryLogs = [];
        foreach ($allQueries as $query) {
            $callers = [];
            if (isset($query[0], $query[1], $query[2])) {
                $sql = $query[0];
                $executionTime = $query[1];
                $stack = $query[2];
            } else {
                continue;
            }
            $callers = array_reverse(explode(',', $stack));
            $callers = array_map('trim', $callers);
            $caller = reset($callers);
            $sql = trim($sql);

            $row = [
                'caller'         => $caller,
                'sql'            => $sql,
                'execution_time' => $executionTime,
                'stack'          => $callers,
            ];
            $queryLogs[] = $row; // Store the row in the $rows array
        }
        return array_reverse($queryLogs);
    }



    public static function maybeCacheQueries()
    {
        try{
            global $wpdb;

            $currentQueries = $wpdb->queries ?? [];
            if(empty($currentQueries)){
                return;
            }
            $allQueries = get_option('dlct_db_query_log', array());
            if(!is_array($allQueries)){
                $allQueries = array();
                update_option('dlct_db_query_log', array());
            }
            $allQueries = array_merge($allQueries, $currentQueries,[]);

            $allQueries = array_slice($allQueries, -50);

            update_option('dlct_db_query_log', $allQueries);
        } catch (\Exception $e){

        }


    }

    public function setRandomLogPath()
    {
        $debugPath = '';
        $generatedDebugPath = get_option('dlct_debug_file_path');
        if (get_option('dlct_debug_file_path_generated') === 'yes' && file_exists($generatedDebugPath)) {
            $debugPath = get_option('dlct_debug_file_path');
        } else {
            $randomString = uniqid();
            $debugPath = apply_filters('dlct_debug_file_path', ABSPATH . "wp-content/debug-" . $randomString . ".log");
            update_option('dlct_debug_file_path', $debugPath, false);
            update_option('dlct_debug_file_path_generated', 'yes', false);
            update_option('dlct_log_file_copied',false,false);
            if (!is_file($debugPath)) {
                file_put_contents($debugPath, '');
            }
            (new \DebugLogConfigTool\Controllers\ConfigController())->update('WP_DEBUG_LOG', "'" . $debugPath . "'");
            (new \DebugLogConfigTool\Controllers\LogController())->maybeCopyLogFromDefaultLogFile();
        }
        return $debugPath;
    }

    /**
     * Generate test logs of different types for demonstration purposes
     */
    public function generateTestLogs()
    {
        Helper::verifyRequest();

        if(empty($this->logFilePath)){
            $this->logFilePath = $this->setRandomLogPath();
        }

        try {
            // Make sure debug logging is enabled
            $configController = new \DebugLogConfigTool\Controllers\ConfigController();
            $isDebugEnabled = $configController->getValue('WP_DEBUG');
            $isDebugLogEnabled = $configController->getValue('WP_DEBUG_LOG');

            if (!$isDebugEnabled || !$isDebugLogEnabled) {
                // Temporarily enable debug logging
                $configController->update('WP_DEBUG', 'true');
                $configController->update('WP_DEBUG_LOG', 'true');
            }

            // Generate different types of log entries
            $timestamp = current_time('mysql');

            // Simple format test (should be easier to parse)
            $this->writeTestLog($timestamp, 'Simple test log entry for debugging');

            // Standard WordPress format test
            $this->writeTestLog($timestamp, '[' . date('Y-m-d H:i:s') . ' UTC] Simple WordPress format test log');

            // Notice
            $this->writeTestLog($timestamp, 'PHP Notice: Undefined variable: test_var in ' . ABSPATH . 'wp-content/plugins/test-plugin/test-file.php on line 42');

            // Warning
            $this->writeTestLog($timestamp, 'PHP Warning: Invalid argument supplied for foreach() in ' . ABSPATH . 'wp-content/plugins/test-plugin/test-file.php on line 53');

            // Deprecated
            $this->writeTestLog($timestamp, 'PHP Deprecated: Function create_function() is deprecated in ' . ABSPATH . 'wp-content/plugins/legacy-plugin/old-file.php on line 27');

            // Parse error
            $this->writeTestLog($timestamp, 'PHP Parse error: syntax error, unexpected \'}\'  in ' . ABSPATH . 'wp-content/plugins/broken-plugin/broken-file.php on line 65');

            // Fatal error with stack trace
            $fatalError = 'PHP Fatal error: Uncaught Error: Call to undefined function nonexistent_function() in ' . ABSPATH . 'wp-content/plugins/example-plugin/example.php:78' . PHP_EOL;
            $fatalError .= 'Stack trace:' . PHP_EOL;
            $fatalError .= '#0 ' . ABSPATH . 'wp-includes/class-wp-hook.php(324): example_plugin_function()' . PHP_EOL;
            $fatalError .= '#1 ' . ABSPATH . 'wp-includes/class-wp-hook.php(348): WP_Hook->apply_filters()' . PHP_EOL;
            $fatalError .= '#2 ' . ABSPATH . 'wp-includes/plugin.php(517): WP_Hook->do_action()' . PHP_EOL;
            $fatalError .= '#3 ' . ABSPATH . 'wp-settings.php(617): do_action(\'init\')' . PHP_EOL;
            $fatalError .= '#4 ' . ABSPATH . 'wp-config.php(96): require_once(\'' . ABSPATH . 'wp-settings.php\')' . PHP_EOL;
            $fatalError .= '#5 ' . ABSPATH . 'wp-load.php(50): require_once(\'' . ABSPATH . 'wp-config.php\')' . PHP_EOL;
            $fatalError .= '#6 ' . ABSPATH . 'wp-blog-header.php(13): require_once(\'' . ABSPATH . 'wp-load.php\')' . PHP_EOL;
            $fatalError .= '#7 ' . ABSPATH . 'index.php(17): require(\'' . ABSPATH . 'wp-blog-header.php\')' . PHP_EOL;
            $fatalError .= '#8 {main}' . PHP_EOL;
            $fatalError .= '  thrown in ' . ABSPATH . 'wp-content/plugins/example-plugin/example.php on line 78';

            $this->writeTestLog($timestamp, $fatalError);

            // Database error
            $this->writeTestLog($timestamp, 'WordPress database error Table \'wp_options\' doesn\'t exist for query SELECT option_name, option_value FROM wp_options made by require_once(\'wp-load.php\'), require_once(\'wp-config.php\'), require_once(\'wp-settings.php\'), include_once(\'wp-includes/option.php\'), get_option');

            // Custom backtrace example
            $backtraceExample = $this->generateBacktraceExample();
            $this->writeTestLog($timestamp, '[DEBUG] Custom backtrace example: Testing a function with detailed backtrace' . "\n" . $backtraceExample);

            // Log the path to help with debugging
            error_log('Debug Log Config Tool: Test logs written to ' . $this->logFilePath);
            error_log('Debug Log Config Tool: File exists: ' . (file_exists($this->logFilePath) ? 'Yes' : 'No'));
            error_log('Debug Log Config Tool: File size: ' . filesize($this->logFilePath) . ' bytes');

            wp_send_json_success([
                'message' => 'Test logs generated successfully',
                'success' => true,
                'log_path' => $this->logFilePath,
                'file_exists' => file_exists($this->logFilePath),
                'file_size' => filesize($this->logFilePath)
            ]);

        } catch (\Exception $e) {
            error_log('Debug Log Config Tool Error: ' . $e->getMessage());
            wp_send_json_error([
                'message' => $e->getMessage(),
                'success' => false
            ]);
        }
    }

    /**
     * Helper method to write a test log entry
     *
     * @param string $timestamp The timestamp for the log entry
     * @param string $message The log message
     */
    private function writeTestLog($timestamp, $message)
    {
        $logEntry = "[{$timestamp}] {$message}" . PHP_EOL;
        file_put_contents($this->logFilePath, $logEntry, FILE_APPEND);
    }

    /**
     * Generate a sample backtrace for demonstration purposes
     *
     * @return string Formatted backtrace string
     */
    private function generateBacktraceExample()
    {
        // Simulate a function call stack
        $backtrace = "Backtrace:\n";
        $backtrace .= "#0 wp-content/plugins/example-plugin/includes/class-example.php(123): ExamplePlugin\\Core\\API->process_request(Array)\n";
        $backtrace .= "#1 wp-content/plugins/example-plugin/includes/class-api.php(45): ExamplePlugin\\Core->handle_endpoint('users/profile')\n";
        $backtrace .= "#2 wp-includes/class-wp-hook.php(324): ExamplePlugin\\API->register_routes()\n";
        $backtrace .= "#3 wp-includes/class-wp-hook.php(348): WP_Hook->apply_filters(NULL, Array)\n";
        $backtrace .= "#4 wp-includes/plugin.php(517): WP_Hook->do_action(Array)\n";
        $backtrace .= "#5 wp-includes/rest-api.php(458): do_action('rest_api_init')\n";
        $backtrace .= "#6 wp-includes/rest-api.php(368): rest_api_loaded()\n";
        $backtrace .= "#7 wp-includes/class-wp-hook.php(324): rest_api_init('1')\n";
        $backtrace .= "#8 wp-includes/class-wp-hook.php(348): WP_Hook->apply_filters(NULL, Array)\n";
        $backtrace .= "#9 wp-includes/plugin.php(517): WP_Hook->do_action(Array)\n";
        $backtrace .= "#10 wp-includes/load.php(1165): do_action('init')\n";
        $backtrace .= "#11 wp-settings.php(512): wp_loaded()\n";
        $backtrace .= "#12 wp-config.php(96): require_once('/var/www/html/w...')\n";
        $backtrace .= "#13 wp-load.php(50): require_once('/var/www/html/w...')\n";
        $backtrace .= "#14 index.php(17): require('/var/www/html/w...')\n";
        $backtrace .= "#15 {main}\n";

        // Add some variable dump information that might be useful in debugging
        $backtrace .= "\nVariable dump:\n";
        $backtrace .= "\$_REQUEST = " . json_encode(['page_id' => 123, 'action' => 'view', 'user_id' => 456]) . "\n";
        $backtrace .= "\$current_user_id = 456\n";
        $backtrace .= "\$api_response = " . json_encode(['status' => 'error', 'code' => 403, 'message' => 'Permission denied']) . "\n";

        return $backtrace;
    }
}
