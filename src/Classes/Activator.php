<?php

namespace DebugLogConfigTool;

class Activator
{
    public $debugConstants = ['WP_DEBUG', 'WP_DEBUG_LOG', 'SCRIPT_DEBUG'];


    public function run()
    {
        $this->saveInitialConstants();
        $this->updateDebugConstants();
    }

    public function saveInitialConstants()
    {

        $constantManager = new DebugConstantManager();
        $initialConstants = [];
        $debugConstants = apply_filters('DebugLogConfigTool_initial_constants', $this->debugConstants);
        foreach ($debugConstants as $constant) {
            $exists = $constantManager->exists($constant);
            if ($exists) {
                $initialConstants[$constant]['value'] = $constantManager->getValue($constant);
            }
        }
        update_option('DebugLogConfigTool_data_initial', $initialConstants);

    }

    public function updateDebugConstants()
    {
        $constantManager = new DebugConstantManager();
        $updatedConstants = [];
        $debugConstants = apply_filters('DebugLogConfigTool_initial_constants', $this->debugConstants);

        foreach ($debugConstants as $constantKey) {

            $value = 'true';
            //setting all debug constant to true
            //if already true then just get the value
            $success = $constantManager->update($constantKey, $value, '');
            if ($success) {
                $value = 'true';
            } else {
                $value = $constantManager->getValue($constantKey);
            }
            $updatedConstants[] = [
                'name' => strtoupper($constantKey),
                'value' => $value,
                'type' => 'raw'
            ];

        }

        update_site_option('DebugLogConfigTool_updated_constant', json_encode($updatedConstants));
        if (is_array($updatedConstants)) {
            add_action('admin_notices', function () {
                $class = 'notice notice-success is-dismissible';
                $message = 'Debug Constants is automacially enabled, you disable/enable these from here ';
                $link = '<a href="' . site_url('wp-admin/tools.php?page=wpdd_log') . '"> Plugin settings . </a>';
                printf('<div class="%1$s"><p>%2$s , %3$s</p></div>', esc_attr($class), $message, $link);
            });
        }
        return true;


    }


}
