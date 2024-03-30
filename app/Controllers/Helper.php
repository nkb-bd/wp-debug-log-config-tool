<?php

namespace DebugLogConfigTool\Controllers;

class Helper
{
    
    public static function verifyRequest()
    {
        $nonce = sanitize_text_field(isset($_REQUEST['nonce']));
        if (!wp_verify_nonce($nonce, 'dlct-nonce')) {
            wp_send_json_error(['message' => 'Nonce verification Failed!']);
        }
    
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission Denied!']);
        }
        die();
    }
}
