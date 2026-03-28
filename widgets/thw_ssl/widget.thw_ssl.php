<?php

require_once dirname(__DIR__) . '/class.th_toggle.php';

use Hosting\Hitechcloud\widgets\th_toggle;

class widget_thw_ssl extends th_toggle {
    protected $description = 'SSL Certificates';
    protected $widgetfullname = 'SSL/TLS';
    protected $display_groups = ['sidemenu'];

    protected $config = [
        'auto_ssl'   => 'on',
        'custom_ssl' => 'on',
    ];
}
