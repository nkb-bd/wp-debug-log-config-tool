<?php

/** @var \DebugLogConfigTool\Router $router */
$router->get('get_log', 'LogController@get');
$router->post('clear_logs', 'LogController@clear');
$router->get('get_settings', 'SettingsController@get');
$router->post('update_settings', 'SettingsController@update');
$router->get('get_notification_email', 'NotificationController@getNotificationEmail');
$router->post('update_notification_email', 'NotificationController@updateNotificationEmail');
$router->post('update_safe_mode', 'SafeModeController@update');
$router->get('get_safe_mode', 'SafeModeController@get');


