<?php

namespace DebugLogConfigTool;

final class DLCT_Bootstrap
{
    const DLCT_LOG = 'dlct_logs';

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
        $this->loadComponents();
        $this->loadTextDomain();

        add_action('admin_menu', [$this, 'adminMenu']);
        add_action('wp_before_admin_bar_render', [$this, 'adminTopMenu']);
        add_action('wp_ajax_dlct_logs_admin', [$this, 'ajaxHandler']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_script']);
        add_action('wpdd_admin_page_render_after', [$this, 'showMsg']);
        add_action('admin_init', [$this, 'msgDismissed']);

    }

    private function loadComponents()
    {
        new DebugConstantManager();
        new EmailManager();
    }

    public function loadTextDomain()
    {
//        todo
//        load_plugin_textdomain(self::DLCT_LOG . '-domain', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function ajaxHandler()
    {
        $this->verifyNonce($_REQUEST);
        if (current_user_can($this->getAccessRole())) {
            return (new AjaxHandler($_REQUEST));
        }
    }

    public function verifyNonce($request)
    {
        if (!wp_doing_ajax()) {
            return;
        }

        if (!wp_verify_nonce($request['nonce'], 'ajax-nonce')) {
            wp_send_json_error(['message' => 'Error: Nonce error!']);
        }
    }

    public function getAccessRole()
    {
        return apply_filters('DLCT_LOG_admin_access_role', 'manage_options');
    }

    public function enqueue_admin_script()
    {
        if (isset($_GET['page']) && $_GET['page'] == '' . self::DLCT_LOG . '') {
            wp_enqueue_style('dlct_style', DLCT_PLUGIN_URL . 'assets/css/wpdebuglog-admin.css', array(), '1.0');
            wp_enqueue_script('dlct_main_js', DLCT_PLUGIN_URL . 'assets/js/wpdebuglog-admin.js', array('jquery'),
                '1.0');
            wp_localize_script('dlct_main_js', 'dlct_wpdebuglog',
                [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'action' => 'dlct_logs_admin',
                    'nonce' => wp_create_nonce('ajax-nonce'),
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
            'id' => self::DLCT_LOG . '_id', // an unique id (required)
            'parent' => false, // false for a top level menu
            'title' => 'Debug Logs', // title/menu text to display
            'href' => site_url('wp-admin/tools.php?page=') . self::DLCT_LOG . '#/logs', // target url of this menu item

        ));
    }

    public function adminPage()
    {
        do_action('wpdd_admin_page_render');
        echo "<div id ='wpdebugapp'></div>";
        do_action('wpdd_admin_page_render_after');


    }

    public function showMsg()
    {
        if (!get_option('DLCT_LOGconfig_notice_dismissed_20')) {
            $class = 'notice notice-success is-dismissible';
            $message = 'If you like this plugin a nice review will be appriciated :)';
            $reviewBtn = '<a class="button" target="_blank" href="https://wordpress.org/"> Give Review </a>';
            $closeBtn = '<a class="button" href="' . site_url('wp-admin/tools.php?page=DLCT_LOG&dimiss_msg=true') . '"> Dismiss </a>';
            printf('<div class="%1$s"><p>%2$s  %3$s %4$s</p></div>', esc_attr($class), $message, $reviewBtn, $closeBtn);
        }

    }


    function msgDismissed()
    {

        if (isset($_GET['page']) && $_GET['page'] == '' . self::DLCT_LOG . '' && isset($_GET['dimiss_msg'])) {
            update_option('DLCT_LOGconfig_notice_dismissed_20', true);
        }
    }

}


register_deactivation_hook(DLCT_PLUGIN_MAIN_FILE, function () {
    DLCT_Bootstrap::deactivate();
});



