<?php

require_once dirname(__DIR__) . '/class.th_toggle.php';

use Hosting\Hitechcloud\widgets\th_toggle;

class widget_thw_logs extends th_toggle {
    protected $description = 'Access & Error Logs';
    protected $widgetfullname = 'Logs';
    protected $display_groups = ['sidemenu'];

    protected $config = [
        'access_log' => 'on',
        'error_log'  => 'on',
    ];
}
