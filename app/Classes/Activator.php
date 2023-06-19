<?php

namespace DebugLogConfigTool\Classes;

use DebugLogConfigTool\Controllers\ConfigController;
use DebugLogConfigTool\Controllers\NotificationController;
use DebugLogConfigTool\Controllers\SettingsController;

class Activator
{
    public function run()
    {
        $this->saveInitialConstants();
        $this->updateDebugConstants();
    }
    
    private function saveInitialConstants()
    {
        (new ConfigController())->storeInitialValues();
    }
    
    /**
     * Add new if not existent constants
     * @return void
     */
    private function updateDebugConstants()
    {
        $constantManager = new ConfigController();
        $updatedConstants = [];
        $debugConstants = (new \DebugLogConfigTool\Controllers\SettingsController())->getConstants();
        foreach ($debugConstants as $constantKey=>$constant) {
            $value = $constant['value'];
            // Set all debug constants to true or get the existing value if already true
            $success = $constantManager->update($constantKey, $value, '');
            if (!$success) {
                $value = $constantManager->getValue($constantKey);
            }
            
            $updatedConstants[] = [
                'name'  => strtoupper($constantKey),
                'value' => $value,
                'type'  => 'raw'
            ];
        }
        (new SettingsController())->store($updatedConstants);
        
        if (is_array($updatedConstants)) {
            add_action('admin_notices', function () {
                $class = 'notice notice-success is-dismissible';
                $message = 'Debug Constants is automatically enabled, you can disable/enable these from here ';
                $link = '<a href="' . site_url('wp-admin/tools.php?page=wpdd_log') . '"> Plugin settings . </a>';
                
                printf('<div class="%1$s"><p>%2$s , %3$s</p></div>', esc_attr($class), $message, $link);
            });
        }
    }
}
