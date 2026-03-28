<?php

namespace Hosting\Hitechcloud\widgets;

abstract class th_toggle extends \HostingWidget {
    protected $description = 'Allow client to use hosting feature';
    protected $widgetfullname = 'Toggle feature';
    protected $info = array(
        'appendtpl' => false,
        'replacetpl' => false,
        'options'=> self::OPTION_UNIQUE
    );
    protected $display_groups = [];
}
