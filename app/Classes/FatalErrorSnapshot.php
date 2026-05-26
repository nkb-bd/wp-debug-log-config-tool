<?php

namespace DebugLogConfigTool\Classes;

class FatalErrorSnapshot
{
    const OPTION_KEY = 'dlct_last_fatal_error_snapshot';

    public function boot()
    {
        register_shutdown_function([$this, 'capture']);
    }

    public function capture()
    {
        $error = error_get_last();

        if (!$error || empty($error['type']) || !$this->isFatalType((int) $error['type'])) {
            return;
        }

        update_option(self::OPTION_KEY, [
            'type' => (int) $error['type'],
            'message' => isset($error['message']) ? wp_strip_all_tags((string) $error['message']) : '',
            'file' => isset($error['file']) ? wp_normalize_path((string) $error['file']) : '',
            'line' => isset($error['line']) ? (int) $error['line'] : 0,
            'captured_at' => current_time('mysql'),
            'timestamp' => time(),
        ], false);
    }

    public static function get()
    {
        $snapshot = get_option(self::OPTION_KEY, []);

        return is_array($snapshot) ? $snapshot : [];
    }

    public static function clear()
    {
        delete_option(self::OPTION_KEY);
    }

    private function isFatalType($type)
    {
        return in_array($type, [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_USER_ERROR,
            E_RECOVERABLE_ERROR,
        ], true);
    }
}
