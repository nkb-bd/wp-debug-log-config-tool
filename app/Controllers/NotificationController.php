<?php

namespace DebugLogConfigTool\Controllers;

class NotificationController{
    public $notificationEmail = 'dlct_log_notification_email';
    public $notificationStatus = 'dlct_log_notification_email_schedule';
    public function boot()
    {
        if ( ! wp_next_scheduled( 'dlct_notification_check_daily' ) ) { // This will always be false
            wp_schedule_event( time(), 'daily', 'dlct_notification_check_daily', '' );
        }
        add_action('dlct_notification_check_daily',[$this,'maybeSendEmail']);
    }
    public function maybeSendEmail()
    {
        $logData = (new LogController())->get();
        $logData = json_decode($logData);

        if(!empty($logData->logs)){
            $lastLog = array_pop($logData->logs);
            $this->sendEmail($lastLog);
        }
    }
    public function sendEmail($lastLog)
    {
        $to = 'emailsendto@example.com';
        $subject = 'Notification from Debug Log Config Plugin';
        $body = 'A new debug log has been recorded in your site';
        $table = '<table>';
            foreach ($lastLog as $row) {
                $table .= '<tr>';
                foreach ($row as $cell) {
                    $table .= '<td>' . $cell . '</td>';
                }
                $table .= '</tr>';
            }
        $table .= '</table>';
        $body .= $table;
        $headers = array('Content-Type: text/html; charset=UTF-8','From: My Site Name <support@example.com>');

        wp_mail( $to, $subject, $body, $headers );
    }
    public function getNotificationEmail()
    {   
        $notification_email = get_option($this->notificationEmail);
        if(!$notification_email){
            $notification_email = get_option('admin_email'); 
        }
        $notification_status = get_option($this->notificationStatus) == 'true' || get_option($this->notificationStatus) == 'yes';
        wp_send_json_success([
            'email' => $notification_email,
            'status' =>$notification_status,
        ]);
    }

    public function updateNotificationEmail()
    {   
        $notification_email = sanitize_text_field($_REQUEST['email']);
        $notification_status = sanitize_text_field($_REQUEST['status']);
        if($notification_status)

        if(!$notification_email){
            $notification_email = get_option('admin_email'); 
        }
        update_option($this->notificationEmail, $notification_email, false);
        update_option($this->notificationStatus, $notification_status, false);
        wp_send_json_success([
            'message' => 'Notification Settings Updated!',
            'success' => true
        ]);
    }
}