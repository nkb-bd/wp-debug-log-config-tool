<?php

namespace DebugLogConfigTool\Classes;

use DebugLogConfigTool\Controllers\NotificationController;

use DebugLogConfigTool\Classes\DashboardWidget;


final class DLCT_Bootstrap
{
    const DLCT_LOG = 'dlct_logs';

    /**
     * All registered keys.
     *
     * @var array
     */
    protected static $registry = [];
    /**
     * @var DLCT_Bootstrap
     */
    protected static $instance;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Bind a new key/value into the container.
     * @param string $key
     * @param mixed $value
     */
    public static function bind($key, $value)
    {
        static::$registry[$key] = $value;
    }

    /**
     * Retrieve a value from the registry.
     * @param string $key
     */
    public static function get($key)
    {
        if (!array_key_exists($key, static::$registry)) {
            throw new Exception("No {$key} is bound in the container.");
        }
        return static::$registry[$key];
    }

    public static function activate()
    {
        (new Activator())->run();
    }

    public static function deactivate()
    {
        (new DeActivator())->run();
    }

    public function init()
    {
        if (!is_admin()) {
            return;
        }
    
        $this->loadTextDomain();

        add_action('admin_menu', [$this, 'adminMenu']);
        add_action('wp_before_admin_bar_render', [$this, 'adminTopMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_script']);
        add_action('wpdd_admin_page_render', [$this, 'showMsg']);
        add_action('admin_init', [$this, 'msgDismissed']);

        add_action('wp_dashboard_setup', [$this, 'dashboardWidget']);
        (new NotificationController())->boot();
        (new NotificationController())->scheduleCron();
        (new AjaxHandler())->boot();
        $this->setRandomLogPath();
    }

    public function loadTextDomain()
    {
        //        todo
        //        load_plugin_textdomain(self::DLCT_LOG . '-domain', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }



    public function enqueue_admin_script()
    {
        if (isset($_GET['page']) && $_GET['page'] == '' . self::DLCT_LOG . '') {
            wp_enqueue_style('dlct_style', DLCT_PLUGIN_URL . 'dist/wpdebuglog-admin-css.css', array(), DLCT_PLUGIN_VERSION);
            wp_enqueue_script(
                'dlct_main_js',
                DLCT_PLUGIN_URL . 'dist/wpdebuglog-admin.js',
                array('jquery'),
                DLCT_PLUGIN_VERSION
            );
            global $wp;
            $url = home_url($wp->request);

            wp_localize_script(
                'dlct_main_js',
                'dlct_wpdebuglog',
                [
                    'ajax_url'      => admin_url('admin-ajax.php'),
                    'base_url'      => $url,
                    'action'        => 'dlct_logs_admin',
                    'nonce'         => wp_create_nonce('dlct-nonce'),
                    'current_color' => get_user_option('admin_color', get_current_user_id())
                ]
            );
        }
    }

    public function adminMenu()
    {
        add_submenu_page(
            'tools.php',
            __('Debug Logs', self::DLCT_LOG),
            __('Debug Logs', self::DLCT_LOG),
            $this->getAccessRole(),
            self::DLCT_LOG,
            array($this, 'adminPage')
        );
    }

    public function adminTopMenu()
    {
        global $wp_admin_bar;
        $wp_admin_bar->add_menu(array(
            'id'     => self::DLCT_LOG . '_id',
            // an unique id (required)
            'parent' => false,
            // false for a top level menu
            'title'  => 'Debug Logs',
            // title/menu text to display
            'href'   => site_url('wp-admin/tools.php?page=') . self::DLCT_LOG . '#/',
            // target url of this menu item

        ));
    }

    public function adminPage()
    {
        do_action('wpdd_admin_page_render');
        echo "<div id='main-app'></div>";
        do_action('wpdd_admin_page_render_after');
    }

    public function showMsg()
    {
        static  $messageShown = false;
        if (!get_option('DLCT_LOGconfig_notice_dismissed_20') && !$messageShown) {
            $class = 'notice notice-success is-dismissible';
            $message = 'If you like this plugin a nice review will be appriciated :)';
            $reviewBtn = '<a class="button" target="_blank" href="https://wordpress.org/plugins/debug-log-config-tool"> Give Review </a>';
            $closeBtn = '<a class="button" href="' . site_url('wp-admin/tools.php?page=' . self::DLCT_LOG . '&dimiss_msg=true') . '"> Dismiss </a>';
            printf('<div class="%1$s"><p>%2$s  %3$s %4$s</p></div>', esc_attr($class), $message, $reviewBtn, $closeBtn);
            $messageShown = true;
        }
    }

    public function msgDismissed()
    {
        if (isset($_GET['page']) && $_GET['page'] == '' . self::DLCT_LOG . '' && isset($_GET['dimiss_msg'])) {
            update_option('DLCT_LOGconfig_notice_dismissed_20', true);
        }
    }

    public function dashboardWidget()
    {
        if (!current_user_can($this->getAccessRole())) {
            return;
        }

        wp_add_dashboard_widget('dlct_widget', __('Recent Debug Logs', 'dlct_logs'), function () {
            (new DashboardWidget())->init();
        }, 10, 1);
    }

    public function getAccessRole()
    {
        return apply_filters('DLCT_LOG_admin_access_role', 'manage_options');
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
            (new \DebugLogConfigTool\Controllers\ConfigController())->update('WP_DEBUG_LOG', "'" . $debugPath . "'");
            
            \DebugLogConfigTool\Controllers\LogController::maybeCopyLogFromDefaultLogFile();
        }
        return $debugPath;
    }
}
