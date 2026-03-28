<?php

require_once dirname(__DIR__) . '/class.th_toggle.php';

use Hosting\Hitechcloud\widgets\th_toggle;

class widget_thw_cronjob extends th_toggle {
    protected $description = 'Cron Jobs';
    protected $widgetfullname = 'Cron Jobs';
    protected $display_groups = ['sidemenu'];

    protected $config = [
        'create' => 'on',
        'delete' => 'on',
    ];
}
