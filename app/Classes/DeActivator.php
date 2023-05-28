<?php

namespace DebugLogConfigTool;
use DebugLogConfigTool\DebugConstantManager;
class DeActivator
{

    public function run()
    {
        $debugConstants = get_option('DebugLogConfigTool_data_initial');
        $constantManager = new DebugConstantManager();
        if (!is_writable($constantManager->getConfigPath())) {
            return;
        }
        foreach ($debugConstants as $index=>$constantKey) {
           $constantManager->update( $index, $constantKey['value'], '');
        }
    }
}
