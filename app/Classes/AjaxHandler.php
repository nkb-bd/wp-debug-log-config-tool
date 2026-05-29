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

        try {
            $result = Router::load('app/routes.php')->direct(Request::ajaxRoute(), Request::method());
        } catch (\Throwable $e) {
            error_log(sprintf(
                '[debug-log-config-tool] router dispatch failed: %s (route=%s method=%s)',
                $e->getMessage(),
                Request::ajaxRoute(),
                Request::method()
            ));
            wp_send_json_error(['message' => 'Internal error.'], 500);
        }

        if (is_array($result) && isset($result['ok']) && $result['ok'] === false) {
            error_log(sprintf(
                '[debug-log-config-tool] unknown route: reason=%s route=%s method=%s',
                $result['reason'],
                isset($result['route']) ? $result['route'] : '',
                isset($result['method']) ? $result['method'] : ''
            ));
            wp_send_json_error(['message' => 'Unknown action.'], 404);
        }
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
