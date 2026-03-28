<?php

require_once dirname(__DIR__) . '/class.th_toggle.php';

use Hosting\Hitechcloud\widgets\th_toggle;

class widget_thw_databases extends th_toggle {
    protected $description = 'Manage Databases';
    protected $widgetfullname = 'Databases';
    protected $display_groups = ['sidemenu'];

    protected $config = [
        'create' => 'on',
        'delete' => 'on',
    ];
}
