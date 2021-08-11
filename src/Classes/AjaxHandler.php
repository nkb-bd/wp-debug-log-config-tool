<?php

namespace DebugLogConfigTool;

use DebugLogConfigTool\Activator;
use DebugLogConfigTool\DebugConstantManager;

class AjaxHandler
{


    /**
     * AjaxHandler constructor.
     */
    public function __construct($request)
    {
        $validRoutes = [
            'clearlog'          => 'clearlog',
            'fetch_data'        => 'fetchData',
            'save_constant'     => 'saveConstant',
            'get_constant'      => 'getConstant',
            'enableAllDebuging' => 'updatedDebugConstant',
            'check_constant'    => 'checkConstant'

        ];

        $route = sanitize_text_field($request['route']);

        if( !is_writable( (new DebugConstantManager())->getConfigPath() )){
            wp_send_json_error(['message' => 'Config file not writtable']);
        }
        if (isset($validRoutes[$route])) {
            return $this->{$validRoutes[$route]}($request);
        }

    }

    public static function sanitize($data)
    {
        if (is_array($data)) {
            array_walk_recursive($data, 'sanitize_text_field');  // replace your loop with this line
        } else {
            sanitize_text_field($data);
        }
        return $data;
    }

    public function updatedDebugConstant()
    {
        (new Activator())->updateDebugConstants();
    }

    public function clearLog()
    {
        $file = WP_CONTENT_DIR . '/debug.log';
        $open = fopen($file, "r+");
        if ($open != true) {
            $msg = 'Could not open file!';
        } else {
            file_put_contents($file, "");
            $msg = 'Log cleared';
        }
        wp_send_json_success($msg);

    }

    public function fetchData()
    {
        $file = WP_CONTENT_DIR . '/debug.log';
        $file =  apply_filters('wp_debuglog_log_file_path', $file);
        if( !file_exists( $file )){
            wp_send_json_error(['message' => 'Debug file not found']);
        }

        $returnData = array(
            'data'    => $this->loadDebugLog(),
            'logsize' => $this->getFilesize(),
        );
        wp_send_json_success($returnData);
    }

    public function loadDebugLog()
    {

        $file = WP_CONTENT_DIR . '/debug.log';
        $file =  apply_filters('wp_debuglog_log_file_path', $file);
        $fh = fopen($file, 'r');
        if (!$fh) {
            return 'fail_open';
        }
        $errors = [];
        $i = 0;
        while ($line = @fgets($fh)) {
            $sep = '$!$';
            $line = preg_replace("/^\[([0-9a-zA-Z-]+) ([0-9:]+) ([a-zA-Z_\/]+)\] (.*)$/i", "$1" . $sep . "$2" . $sep . "$3" . $sep . "$4", $line);
            $parts = explode($sep, $line);

            if (count($parts) >= 4) {
                array_push($errors, array(
                    'line'     => $i++,
                    'date'     => $this->getDate($parts[0]),
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
        return $errors;
    }

    private function getDate($date)
    {

        return date('Y-m-d', strtotime($date));
    }

    public function getFilesize()
    {

        $file = WP_CONTENT_DIR . '/debug.log';
        if (file_exists($file)) {
            return filesize($file);
        }
        return false;
    }

    public function saveConstant($req)
    {

        $constants = $req['data'];

        $constants = stripslashes($constants);
        (new DebugConstantManager())->save($constants);
    }

    public function getConstant($req)
    {
        $constants = (new DebugConstantManager())->get();
    }

    public function checkConstant()
    {
        (new DebugConstantManager())->check();
    }

}
