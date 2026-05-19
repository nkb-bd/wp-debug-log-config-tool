<?php

namespace DebugLogConfigTool\Classes;

use DebugLogConfigTool\Activator;
use DebugLogConfigTool\Request;
use DebugLogConfigTool\Router;

class AjaxHandler
{
    
    /**
     * AjaxHandler constructor.
     */
    public function boot()
    {
        add_action('wp_ajax_dlct_logs_admin', [$this, 'handleRequest']);
    }
    
    public function handleRequest()
    {
        $this->verify($_REQUEST);
        Router::load('app/routes.php')->direct(Request::ajaxRoute(), Request::method());
    }
    
    public function getAccessRole()
    {
        return apply_filters('DLCT_LOG_admin_access_role', 'manage_options');
    }
    
    public function verify($request)
    {
        if (!wp_doing_ajax()) {
            wp_send_json_error(['message' => 'Invalid request context.'], 400);
        }
        if (!current_user_can($this->getAccessRole())) {
            wp_send_json_error(['message' => 'Permission denied.'], 403);
        }

        $nonce = isset($request['nonce']) ? sanitize_text_field(wp_unslash($request['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'dlct-nonce')) {
            wp_send_json_error(['message' => 'Error: Nonce error!'], 403);
        }
    }
    
}
