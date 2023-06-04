<?php

namespace DebugLogConfigTool\Controllers;

class SettingsController
{
    protected $optionKey = 'debuglogconfigtool_updated_constant';
    
    public function get()
    {
        $formattedSettings = $this->getSettings();
        $mismatch = [];
        foreach ($formattedSettings as $setting){
            $databaseValue = (new \DebugLogConfigTool\Controllers\ConfigController())->getValue($setting->name);
            if($databaseValue != $setting->value){
                $mismatch[] = $setting->name;
            }
        }
        wp_send_json_success([
            'has_mismatch' => !empty($mismatch),
            'settings'     => $formattedSettings,
            'success'      => true
        ]);
    }
    
    public function store($settings)
    {
        update_option($this->optionKey,json_encode($settings));
    }
    
    public function update()
    {
        $updateValue = filter_var($_REQUEST['setting_value'], FILTER_VALIDATE_BOOLEAN);
        $updateKey = sanitize_text_field($_REQUEST['setting_key']);
        $settings = $this->getSettings();
        $updatedSettings = [];
        foreach ($settings as $setting){
            if($setting->name == $updateKey){
                $setting->value = $updateValue;
                #Write to wp-config.php file
                (new \DebugLogConfigTool\Controllers\ConfigController())->update($updateKey, $updateValue);
            }
            $updatedSettings[] = $setting;
        }
        #Write to database
        $this->store($updatedSettings);
        wp_send_json_success([
            'updated_settings' => $updatedSettings,
            'message' => 'Debug Constant updated successfully',
            'success' => true
        ]);
    }
    
    private function getSettings()
    {
        $settings = get_option($this->optionKey, false);
        if($settings && !$this->is_json($settings)){
            return  [];
        }
        $settings = json_decode($settings);
        $formattedSettings = [];
        foreach ($settings as $setting) {
            $setting->value = $setting->value == 'true' || $setting->value == true;
            $formattedSettings[] = $setting;
        }
        return $formattedSettings;
    }
    public function is_json($string,$return_data = false) {
        $data = json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
    }
}
