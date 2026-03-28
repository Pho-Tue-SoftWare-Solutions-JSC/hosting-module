<?php

require_once dirname(__DIR__) . '/class.th_toggle.php';

use Hosting\Hitechcloud\widgets\th_toggle;

class widget_thw_backup extends th_toggle {
    protected $description = 'Backups';
    protected $widgetfullname = 'Backups';
    protected $display_groups = ['sidemenu'];

    protected $config = [
        'create'  => 'on',
        'restore' => 'on',
        'delete'  => 'on',
    ];
}
