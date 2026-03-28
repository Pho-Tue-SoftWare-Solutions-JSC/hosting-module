<?php

require_once dirname(__DIR__) . '/class.th_toggle.php';

use Hosting\Hitechcloud\widgets\th_toggle;

class widget_thw_sftp extends th_toggle {
    protected $description = 'SFTP Accounts';
    protected $widgetfullname = 'SFTP/FTP';
    protected $display_groups = ['sidemenu'];

    protected $config = [
        'create' => 'on',
        'delete' => 'on',
    ];
}
