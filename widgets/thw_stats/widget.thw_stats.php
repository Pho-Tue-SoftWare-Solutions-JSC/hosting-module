<?php

require_once dirname(__DIR__) . '/class.th_toggle.php';

use Hosting\Hitechcloud\widgets\th_toggle;

class widget_thw_stats extends th_toggle {
    protected $description = 'Resource Usage';
    protected $widgetfullname = 'Statistics';
    protected $display_groups = ['sidemenu'];

    protected $config = [
        'disk'      => 'on',
        'bandwidth' => 'on',
        'cpu'       => 'on',
    ];
}
