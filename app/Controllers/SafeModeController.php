<?php

namespace DebugLogConfigTool\Controllers;

class SafeModeController
{
    protected static $isInSafeMode = false;
    protected $optionKey = 'dlct_safe_mode';
    
    public function get()
    {
        $allPlugins = $this->getPluginListGroup();
        $res = [
            'all_plugins'                    => $allPlugins,
            'safe_mode_status'               => get_option('safe_mode_status') == 'on',
            'selected_active_plugins_list'   => get_option('selected_active_plugins_list'),
        ];
        wp_send_json_success($res);
    }
    
    public function update()
    {
        $safeMode = $_POST['safe_mode'] === 'true' || $_POST['safe_mode'] === true;
        
        
        $selectedPlugins = stripslashes($_POST['selected_plugins']);
        update_option('selected_active_plugins_list', $selectedPlugins);
        
        
        if ($safeMode === true) {
            $this->activateSafeMode();
        } else {
            $this->deActivateSafeMode();
        }
    }
    
    public function activateSafeMode()
    {
        if (get_option('safe_mode_status') == 'on') {
            wp_send_json_success([
                'message' => 'SafeMode is already activated',
                'success' => true
            ]);
        } else {
            update_option('safe_mode_status', 'on');
        }
        
        $activePlugins = get_option('active_plugins');
        update_option('before_safe_mode_active_plugins_list', $activePlugins);
        
        $selectedPlugins = get_option('selected_active_plugins_list');
        
        $selectedPlugins = json_decode($selectedPlugins, true);
        if (!is_array($selectedPlugins)) {
            $selectedPlugins = [];
        }
        
        $allPlugins = get_plugins();
        
        // Perform the activation/deactivation based on the operation type and selected plugins
        foreach ($allPlugins as $plugin => $pluginDetails) {
            if (in_array($plugin, $selectedPlugins)) {
                if (in_array($plugin, $activePlugins)) {
                    continue;
                }
                activate_plugins($plugin);
            } else {
                deactivate_plugins($plugin);
            }
        }
        wp_send_json_success([
            'message' => 'SafeMode Activated',
            'success' => true
        ]);
    }
    
    public function deActivateSafeMode()
    {
        // Reset safe mode flag to OFF
        if (get_option('safe_mode_status') == 'off') {
            wp_send_json_success([
                'message' => 'SafeMode is already deactivated',
                'success' => true
            ]);
        } else {
            update_option('safe_mode_status', 'off');
        }
        
        $activePlugins = get_option('active_plugins');
        $beforeSafeModePlugins = get_option('before_safe_mode_active_plugins_list');
        
        // If no plugins were active before safe mode, deactivate all currently active plugins
        if (!is_array($beforeSafeModePlugins)) {
            foreach ($activePlugins as $plugin) {
                deactivate_plugins($plugin);
            }
            return;
        }
        
        
        // Deactivate plugins not in the list of plugins before safe mode
        foreach ($activePlugins as $plugin) {
            if (!in_array($plugin, $beforeSafeModePlugins)) {
                deactivate_plugins($plugin);
            }
        }
        
        // Activate plugins that were in the list before safe mode but are currently inactive
        foreach ($beforeSafeModePlugins as $plugin) {
            if (!in_array($plugin, $activePlugins)) {
                activate_plugin($plugin);
            }
        }
        wp_send_json_success([
            'message' => 'SafeMode Deactivated',
            'success' => true
        ]);
    }
    
    private function getPluginListGroup()
    {
        $active_plugins = get_option('active_plugins');
        $all_plugins = get_plugins();
        
        $formatted_active_plugins = [];
        foreach ($active_plugins as $plugin_file) {
            if (isset($all_plugins[$plugin_file])) {
                $formatted_active_plugins[] = [
                    'label' => $all_plugins[$plugin_file]['Name'],
                    'value' => $plugin_file,
                ];
            }
        }
        
        $inactive_plugins = array_diff($all_plugins, $active_plugins);
        
        $formatted_inactive_plugins = [];
        foreach ($inactive_plugins as $key => $plugin_file) {
            if (isset($all_plugins[$key])) {
                $formatted_inactive_plugins[] = [
                    'label' => $plugin_file['Name'],
                    'value' => $key,
                ];
            }
        }
        
        return [
            [
                'label' => 'Active',
                'items' => $formatted_active_plugins,
            ],
            [
                'label' => 'Inactive',
                'items' => $formatted_inactive_plugins,
            ],
        ];
    }
}
