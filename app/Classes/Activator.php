<?php

namespace DebugLogConfigTool\Classes;

class Activator
{
    public function run()
    {
        // Activation must be side-effect safe for wp-config.php.
        // Debug constants are changed only after an explicit admin action.
    }
}
