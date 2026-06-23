<?php

namespace DebugLogConfigTool\Controllers;

class EmergencyViewerController
{
    private function configDir()
    {
        return WP_CONTENT_DIR . '/dlct-emergency';
    }

    private function configFile()
    {
        return $this->configDir() . '/config.php';
    }

    public function getStatus()
    {
        Helper::verifyRequest();
        $config = $this->readConfig();
        wp_send_json_success([
            'enabled'   => !empty($config['enabled']),
            'auth_user' => isset($config['auth_user']) ? $config['auth_user'] : '',
            'url'       => DLCT_PLUGIN_URL . 'emergency-log-viewer.php',
            'success'   => true,
        ]);
    }

    public function save()
    {
        Helper::verifyRequest();

        $user = isset($_REQUEST['auth_user']) ? sanitize_text_field(wp_unslash($_REQUEST['auth_user'])) : '';
        $pass = isset($_REQUEST['auth_pass']) ? (string) wp_unslash($_REQUEST['auth_pass']) : '';

        if (strlen($user) < 3 || strlen($pass) < 8) {
            wp_send_json_error(['message' => 'Username must be at least 3 characters and password at least 8.']);
        }

        $logPath = (new LogController())->getLogFilePath();
        if (empty($logPath)) {
            $logPath = WP_CONTENT_DIR . '/debug.log';
        }

        $config = [
            'enabled'   => true,
            'log_path'  => $logPath,
            'auth_user' => $user,
            'auth_hash' => password_hash($pass, PASSWORD_DEFAULT),
            'updated'   => time(),
        ];

        if (!$this->writeConfig($config)) {
            wp_send_json_error(['message' => 'Could not write emergency viewer configuration. Check filesystem permissions on wp-content.']);
        }

        wp_send_json_success([
            'enabled'   => true,
            'auth_user' => $user,
            'url'       => DLCT_PLUGIN_URL . 'emergency-log-viewer.php',
            'message'   => 'Emergency log viewer enabled.',
            'success'   => true,
        ]);
    }

    public function disable()
    {
        Helper::verifyRequest();
        if (file_exists($this->configFile())) {
            @unlink($this->configFile());
        }
        wp_send_json_success([
            'enabled' => false,
            'message' => 'Emergency log viewer disabled.',
            'success' => true,
        ]);
    }

    private function readConfig()
    {
        $file = $this->configFile();
        if (!file_exists($file)) {
            return [];
        }
        $config = include $file;
        return is_array($config) ? $config : [];
    }

    private function writeConfig($config)
    {
        $dir = $this->configDir();
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }
        if (!is_dir($dir)) {
            return false;
        }

        if (!file_exists($dir . '/index.php')) {
            @file_put_contents($dir . '/index.php', "<?php // Silence is golden.\n");
        }
        if (!file_exists($dir . '/.htaccess')) {
            @file_put_contents($dir . '/.htaccess', "Order allow,deny\nDeny from all\nRequire all denied\n");
        }

        $contents = "<?php\n// Debug Log Manager Tool emergency viewer config. Do not edit by hand.\nreturn " . var_export($config, true) . ";\n";

        return (bool) file_put_contents($this->configFile(), $contents, LOCK_EX);
    }
}
