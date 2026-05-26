<?php

namespace DebugLogConfigTool\Classes;

use DebugLogConfigTool\Controllers\NotificationController;

class DeActivator
{

    public function run()
    {
        (new NotificationController())->deactivate();
    }

}
