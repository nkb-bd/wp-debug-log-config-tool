<?php

namespace DebugLogConfigTool\Controllers;

use DebugLogConfigTool\vendor\WPConfigTransformer;

class ConfigController
{
    const WPDD_DEBUGGING_PREDEFINED_CONSTANTS_STATE = 'DebugLogConfigTool_data_initial';
    private static $configfilePath;
    
    protected $optionKey = 'debuglogconfigtool_updated_constant';
    public $debugConstants = ['WP_DEBUG', 'WP_DEBUG_LOG', 'SCRIPT_DEBUG'];
    protected $config_file_manager;
    private static $configArgs = [
        'normalize' => true,
        'raw'       => true,
        'add'       => true,
    ];
    
    public function __construct()
    {
        $this->initialize();
    }
    
    private function initialize()
    {
        self::$configfilePath = $this->getConfigFilePath();
        //set anchor for the constants to write
        $configContents = file_get_contents(self::$configfilePath);
        if (false === strpos($configContents, "/* That's all, stop editing!")) {
            preg_match('@\$table_prefix = (.*);@', $configContents, $matches);
            self::$configArgs['anchor'] = $matches[0] ?? '';
            self::$configArgs['placement'] = 'after';
        }
        
        if (!is_writable(self::$configfilePath)) {
            add_action('admin_notices', function () {
                $class = 'notice notice-error is-dismissible';
                $message = 'Config file not writable';
                printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
            });
            return;
        }
        
        $this->config_file_manager = new WPConfigTransformer(self::$configfilePath);
    }
    
    
    public function store($constants)
    {
        try {
            $updatedConstants = [];
            $constants = json_decode($constants, true);
            $this->maybeRemoveDeletedConstants($constants);
            
            foreach ($constants as $constant) {
//          $defaults['raw'] = ($constant['type'] === 'raw') ? true : false;
                $key = sanitize_title($constant['name']);
                $value = sanitize_text_field($constant['value']);
                if (empty($key)) {
                    continue;
                }
                $key = strtoupper($key);
                $value = str_replace("'", '', stripslashes($value));
                $value = $value ? 'true' : 'false';
                $this->config_file_manager->update('constant', $key, $value, self::$configArgs);
                $updatedConstants[] = $constant;
            }
            update_option($this->optionKey, json_encode($updatedConstants));
            wp_send_json_success([
                'message' => 'Constant Updated!',
                'success' => true
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'success' => false
            ]);
        }
    }
    
    public function exists($constant)
    {
        return $this->config_file_manager->exists('constant', strtoupper($constant));
    }
    
    public function getValue($constant)
    {
        if ($this->exists(strtoupper($constant))) {
            return $this->config_file_manager->get_value('constant', strtoupper($constant));
        }
        return null;
    }
    
    
    public function update($key, $value)
    {
        //By default, when attempting to update a config that doesn't exist, one will be added.
        $option = self::$configArgs;
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
        return $this->config_file_manager->update('constant', strtoupper($key), $value, $option);
    }
    
    public function getConfigFilePath()
    {
        $file = ABSPATH . 'wp-config.php';
        if (!file_exists($file)) {
            if (@file_exists(dirname(ABSPATH) . '/wp-config.php')) {
                $file = dirname(ABSPATH) . '/wp-config.php';
            }
        }
        return apply_filters('wp_dlct_config_file_manager_path', $file);
    }
    
    /**
     * remove deleted constant from config
     * @param $constants
     */
    protected function maybeRemoveDeletedConstants($constants)
    {
        $previousSavedData = json_decode(get_option($this->optionKey), true);
        $deletedConstant = array_diff(array_column($previousSavedData, 'name'), array_column($constants, 'name'));
        foreach ($deletedConstant as $item) {
            $this->config_file_manager->remove('constant', strtoupper($item));
        }
    }
    
    
    public function storeInitialValues()
    {
        if (!is_writable(self::$configfilePath)) {
            return;
        }
        $predefinedConstants = [];
        foreach ($this->debugConstants as $constant) {
            if ($this->exists(strtoupper($constant))) {
                $value = $this->getValue(strtoupper($constant));
                $predefinedConstants[$constant] = $value;
            }
        }
        
        update_option(self::WPDD_DEBUGGING_PREDEFINED_CONSTANTS_STATE, $predefinedConstants);
    }
    
    
    public function restoreInitialState()
    {
        $settings = get_option(self::WPDD_DEBUGGING_PREDEFINED_CONSTANTS_STATE);
        if (!is_writable(self::$configfilePath)) {
            return;
        }
        foreach ($settings as $key => $value) {
            (new ConfigController())->update($key, $value);
        }
    }
    
    
}
