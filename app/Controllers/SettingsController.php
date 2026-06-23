<?php

namespace DebugLogConfigTool\Controllers;

use DebugLogConfigTool\Controllers\ConfigController;

class SettingsController
{
    protected $optionKey = 'debuglogconfigtool_updated_constant';

    public function get()
    {
        Helper::verifyRequest();
        $configs = $this->getConstants();
        $formattedSettings = [];
        foreach ($configs as $setting) {
            $configFileValue = ConfigController::getInstance()->getValue($setting['name']);
            if ($setting['name'] == 'WP_DEBUG_LOG') {
                $raw = is_string($configFileValue) ? trim($configFileValue, "'\"") : $configFileValue;
                $value = $raw === true || $raw === 'true' || $raw === '1'
                    || (is_string($raw) && $raw !== '' && $raw !== 'false');
            } else {
                $value = $configFileValue === true || $configFileValue === 'true';
            }
            $formattedSettings[] = [
                'name'  => $setting['name'],
                'value' => $value,
                'info'  => $setting['info'],
                'exists' => $configFileValue !== null,
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
        Helper::verifyRequest();
        $updateValue = filter_var($_REQUEST['setting_value'], FILTER_VALIDATE_BOOLEAN);
        $updateKey = sanitize_text_field($_REQUEST['setting_key']);
        $settings = $this->getConstants();
        $updatedSettings = [];
        foreach ($settings as $setting) {
            if ($setting['name'] == $updateKey) {
                $setting['value'] = $updateValue;
                #Write to wp-config.php file
                if ($updateKey === 'WP_DEBUG_LOG') {
                    $this->updateDebugLogConstant($updateValue);
                } else {
                    ConfigController::getInstance()->update($updateKey, $updateValue);
                }
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

    private function updateDebugLogConstant($enable)
    {
        $config = ConfigController::getInstance();
        if (!$enable) {
            $config->update('WP_DEBUG_LOG', 'false');
            return;
        }
        $managedPath = get_option('dlct_debug_file_path');
        if ($managedPath && get_option('dlct_debug_file_path_generated') === 'yes') {
            $config->update('WP_DEBUG_LOG', "'" . $managedPath . "'");
        } else {
            $config->update('WP_DEBUG_LOG', 'true');
        }
    }

    public function getConstants()
    {
        $WP_DEBUG_LOG = false;
        if(get_option('dlct_debug_file_path')){
            $WP_DEBUG_LOG = "'".get_option('dlct_debug_file_path')."'";
        }
        $constants = [
            'WP_DEBUG'         => [
                'name'  => 'WP_DEBUG',
                'value' => false,
                'info'  => 'Enable WP_DEBUG mode',
            ],
            'WP_DEBUG_LOG'     => [
                'name'  => 'WP_DEBUG_LOG',
                'value' => $WP_DEBUG_LOG,
                'info'  => 'Enable Debug logging to the /wp-content/debug.log file',
            ],
            'SCRIPT_DEBUG'     => [
                'name'  => 'SCRIPT_DEBUG',
                'value' => false,
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
