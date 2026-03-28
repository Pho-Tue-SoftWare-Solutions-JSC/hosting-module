<?php

require_once dirname(__DIR__) . '/class.th_toggle.php';

use Hosting\Hitechcloud\widgets\th_toggle;

class widget_thw_domains extends th_toggle {
    protected $description = 'Manage Domains';
    protected $widgetfullname = 'Domains';
    protected $display_groups = ['sidemenu'];

    protected $config = [
        'addon_domains' => 'on',
        'subdomains'    => 'on',
    ];
}
