<?php

namespace DebugLogConfigTool\Controllers;

class SettingsController
{
    protected $optionKey = 'debuglogconfigtool_updated_constant';
    
    public function get()
    {
        $configs = $this->getConstants();
        $formattedSettings = [];
        foreach ($configs as $setting) {
            $databaseValue = (new \DebugLogConfigTool\Controllers\ConfigController())->getValue($setting['name']);
            if ($databaseValue != null) {
                # value exists in config so do nothing
            } else {
                # value does not exist in config so update
                $databaseValue = $setting['value'];
                (new \DebugLogConfigTool\Controllers\ConfigController())->update($setting['name'], $setting['value']);
            }
            $formattedSettings[] = [
                'name'  => $setting['name'],
                'value' => $databaseValue === true || $databaseValue === 'true',
                'info'  => $setting['info'],
            ];
        }
        wp_send_json_success([
            'settings' => $formattedSettings,
            'success'  => true
        ]);
    }
    
    public function store($settings)
    {
        update_option($this->optionKey, json_encode($settings));
    }
    
    public function update()
    {
        $updateValue = filter_var($_REQUEST['setting_value'], FILTER_VALIDATE_BOOLEAN);
        $updateKey = sanitize_text_field($_REQUEST['setting_key']);
        $settings = $this->getConstants();
        $updatedSettings = [];
        foreach ($settings as $setting) {
            if ($setting['name'] == $updateKey) {
                $setting['value'] = $updateValue;
                #Write to wp-config.php file
                (new \DebugLogConfigTool\Controllers\ConfigController())->update($updateKey, $updateValue);
            }
            $updatedSettings[] = $setting;
        }
        #Write to database
        $this->store($updatedSettings);
        wp_send_json_success([
            'updated_settings' => $updatedSettings,
            'message'          => 'Debug Constant updated successfully',
            'success'          => true
        ]);
    }
    
    public function getConstants()
    {
        $constants = [
            'WP_DEBUG'         => [
                'name'  => 'WP_DEBUG',
                'value' => true,
                'info'  => 'Enable WP_DEBUG mode',
            ],
            'WP_DEBUG_LOG'     => [
                'name'  => 'WP_DEBUG_LOG',
                'value' => true,
                'info'  => 'Enable Debug logging to the /wp-content/debug.log file',
            
            ],
            'SCRIPT_DEBUG'     => [
                'name'  => 'SCRIPT_DEBUG',
                'value' => true,
                'info'  => 'Use the “dev” versions of core CSS and JavaScript files'
            ],
            'WP_DEBUG_DISPLAY' => [
                'name'  => 'WP_DEBUG_DISPLAY',
                'value' => false,
                'info'  => 'Disable or hide display of errors and warnings in html pages'
            ],
            'SAVEQUERIES'      => [
                'name'  => 'SAVEQUERIES',
                'value' => false,
                'info'  => 'Enable database query logging, turn it off when not debuging cause it will effect site performace. The array is stored in the global $wpdb->queries.'
            ],
        ];
        $settings = apply_filters('dlct_constants', $constants);
        
        if (empty($settings)) {
            return [];
        }
        
        return $settings;
    }
    
}
