<?php

namespace DebugLogConfigTool\Classes;
use DebugLogConfigTool\Controllers\ConfigController;
use DebugLogConfigTool\Controllers\NotificationController;

class DeActivator
{

    public function run()
    {
        (new ConfigController())->restoreInitialState();
        (new NotificationController())->deactivate();
    }
    
}
