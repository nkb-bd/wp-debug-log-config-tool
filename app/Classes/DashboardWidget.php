<?php

namespace DebugLogConfigTool\Classes;

use DebugLogConfigTool\Controllers\LogController;

class DashboardWidget
{

    public function init()
    {
        $logs = (new LogController)->loadLogs(5);
        if (!$logs) {
            echo 'You can see your submission stats here';
            return;
        }

        $this->printStats($logs['logs']);
        return;
    }

    private function printStats($stats)
    {
?>
        <div class="">
            <table class=" wp-list-table widefat fixed striped">
                <thead>
                <tr>
                    <th  style="width: 50px;" ><?php _e('Count'); ?></th>
                    <th ><?php _e('Details'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php  $i = 1; foreach ($stats as $stat): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $stat['details']; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
<?php
    }
}
