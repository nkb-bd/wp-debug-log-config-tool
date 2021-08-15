<?php

namespace DebugLogConfigTool;

use DebugLogConfigTool\vendor\WPConfigTransformer;

class DebugConstantManager
{
    private static $configArgs;
    private static $configPath;
    protected $optionKey = 'debuglogconfigtool_updated_constant';
    public $debugConstants = ['WP_DEBUG', 'WP_DEBUG_LOG', 'SCRIPT_DEBUG'];
    protected $configWritter;

    public function __construct()
    {
        $this->setConfig();

    }


    private function setConfig()
    {
        self::$configArgs = [
            'normalize' => true,
            'raw'       => true,
            'add'       => true
        ];

        self::$configPath = $this->getConfigPath();
        //set anchor for the constants to write
        if (false === strpos(file_get_contents(self::$configPath), "/* That's all, stop editing!")) {
            preg_match('@\$table_prefix = (.*);@', file_get_contents(self::$configPath), $matches);
            self::$configArgs = array_merge(
                self::$configArgs,
                [
                    'anchor'    => "$matches[0]",
                    'placement' => 'after',
                ]
            );
        }

        if (!is_writable(self::$configPath)) {
            add_action('admin_notices', function () {
                $class = 'notice notice-error is-dismissible';
                $message = 'Config file not writable';
                printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
            });
            return;
        }

        $this->configWritter = new WPConfigTransformer(self::$configPath);
    }

    public function getConfigPath()
    {
        $file = ABSPATH . 'wp-config.php';
        if (!file_exists($file)) {
            if (@file_exists(dirname(ABSPATH) . '/wp-config.php')) {
                $file = dirname(ABSPATH) . '/wp-config.php';
            }
        }
        return apply_filters('wp_dlct_config_file_path', $file);

    }

    public function get()
    {
        $constants = get_option($this->optionKey, false);
        wp_send_json_success([
            'data'    =>  json_decode($constants, true),
            'success' => true
        ]);

    }

    public function save($constants)
    {
        $updatedConstant = [];
        $constants = json_decode($constants, true);
        $this->removeDeletedItem($constants);



        foreach ($constants as $value) {
            if( empty($value['name'])){
                continue;
            }
            //set constant raw type boolean or string
            $defaults = self::$configArgs;
            $defaults['raw'] = ($value['type'] === 'raw') ? true : false;
            $value['name'] = strtoupper(sanitize_title($value['name']));
            $constantValue = stripslashes(sanitize_text_field($value['value']));
            $value['value'] = str_replace("'", '', $constantValue);
            $value['value'] = $value['value'] ? 'true' : 'false';
            $this->update($value['name'], $value['value'], $defaults);
            $updatedConstant[] = $value;

        }


        

        $success = update_option($this->optionKey, json_encode($updatedConstant));
        wp_send_json_success([
            'message' => 'Constant Updated!',
            'success' => true
        ]);
    }

    /**
     * remove deleted constant from config
     * @param $constants
     */
    protected function removeDeletedItem($constants)
    {
        $previousSavedData = json_decode(get_option($this->optionKey), true);
        $deletedConstant = array_diff(array_column($previousSavedData, 'name'), array_column($constants, 'name'));
        if (is_array($deletedConstant)) {

            foreach ($deletedConstant as $item) {
                $this->delete($item);
            }
        }
    }

    public function delete($key)
    {
        return $this->configWritter->remove('constant', strtoupper($key));
    }

    public function update($key, $value, $option)
    {
        //By default, when attempting to update a config that doesn't exist, one will be added.
        if (!$option) {
            $option = self::$configArgs;
        }
        return $this->configWritter->update('constant', strtoupper($key), $value, $option);

    }

    function gmt_to_local_timestamp($gmt_timestamp)
    {
        $iso_date = strtotime('Y-m-d H:i:s', $gmt_timestamp);
        return get_date_from_gmt($iso_date, 'Y-m-d h:i a');
    }

    public function activate()
    {

        if (!is_writable(self::$configPath)) {
            return;
        }
        $predefinedConstants = [];
        foreach ($this->debugConstants as $constant) {
            if ($this->exists(strtoupper($constant))) {
                $value = $this->getValue(strtoupper($constant));
                $predefinedConstants[$constant] = $value;
            }
        }
        update_site_option('wpdd_debugging_predefined_constants', $predefinedConstants);
        return;
    }

    public function exists($constant)
    {
        return $this->configWritter->exists('constant', strtoupper($constant));

    }

    public function getValue($constant)
    {
        if ($this->configWritter->exists('constant', strtoupper($constant))) {
            return $this->configWritter->get_value('constant', strtoupper($constant));
        }
        return 0;
    }

    public function deactivate()
    {
        //todo restore initial constant as it was and remove all current config items
    }

    public function check()
    {
        list($savedConstant, $updatedConst) = $this->checkWithDatabase();

        list($debugConstantExist, $updatedConst) = $this->checkWithConfigFile($savedConstant, $updatedConst);

        //update database after adjusting constant with database and config file
        update_option($this->optionKey, json_encode($updatedConst));

        wp_send_json_success(['exists' => $debugConstantExist]);
    }

    /**
     * check if all database const item exits in config file
     * otherwise remove that
     * @return array
     */
    protected function checkWithDatabase()
    {
        $savedConstant = json_decode(get_option($this->optionKey), true);
        $updatedConst = [];
        foreach ($savedConstant as $item) {
            if ($value = $this->getValue($item['name'])) {
                $item['value'] = str_replace("'", '', $value);
                $updatedConst[] = $item;
            }
        }
        return [$savedConstant, $updatedConst];
    }

    /**
     * adjust debug constant with database and config file ,check if any missing
     * @param $savedConstant
     * @param array $updatedConst
     * @return array
     */
    protected function checkWithConfigFile($savedConstant, array $updatedConst)
    {
        $debugConstants = apply_filters('DebugLogConfigTool_initial_constants', $this->debugConstants);
        $savedConstantName = array_column($savedConstant, 'name');
        $missingDebugConst = array_udiff($debugConstants, $savedConstantName, 'strcasecmp'); //non case sensitive compare

        $debugConstantExist = true;
        // if debug constant exist in config update database
        if (is_array($missingDebugConst) && count($missingDebugConst) > 0) {
            $debugConstantExist = false;
            foreach ($missingDebugConst as $name) {
                if ($value = $this->getValue($name)) {
                    $updatedConst[] = [
                        'name'  => strtoupper($name),
                        'value' => str_replace("'", '', $value),
                        'type'  => 'raw'
                    ];
                }
            }
        }
        return [$debugConstantExist, $updatedConst];
    }

    private function getDate($date)
    {
        return date('Y-m-d', strtotime($date));
    }


}




