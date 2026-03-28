<?php

require_once dirname(__DIR__) . '/class.th_toggle.php';

use Hosting\Hitechcloud\widgets\th_toggle;

class widget_thw_php extends th_toggle {
    protected $description = 'PHP Settings';
    protected $widgetfullname = 'PHP';
    protected $display_groups = ['sidemenu'];

    protected $config = [
        'version_switch' => 'on',
        'settings'       => 'on',
    ];
}
