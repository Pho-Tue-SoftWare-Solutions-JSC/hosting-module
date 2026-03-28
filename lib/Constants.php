<?php

namespace Hosting\Hitechcloud\lib;

interface Constants
{
    // Account statuses
    const STATUS_ACTIVE    = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_TERMINATED = 'terminated';
    const STATUS_PENDING   = 'pending';

    // Detail fields (option keys used by HostBill)
    const D_USERNAME = 'option1';
    const D_PASSWORD = 'option2';
    const D_DOMAIN   = 'option3';

    // Product option keys
    const O_PLAN_NAME     = 'Plan Name';
    const O_DISK_QUOTA    = 'Disk Quota (MB)';
    const O_BANDWIDTH     = 'Bandwidth (MB)';
    const O_MAX_DOMAINS   = 'Max Addon Domains';
    const O_MAX_DATABASES = 'Max Databases';
    const O_MAX_FTP       = 'Max FTP Accounts';
    const O_MAX_CRONJOBS  = 'Max Cron Jobs';
    const O_PHP_VERSION   = 'PHP Version';
    const O_SHELL_ACCESS  = 'Shell Access';
    const O_SSL_ENABLED   = 'SSL Enabled';
    const O_BACKUP_ENABLED = 'Backup Enabled';
}
