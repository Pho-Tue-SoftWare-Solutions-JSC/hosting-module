<?php

use Hosting\Hitechcloud\lib\Constants;

/**
 * Hitechcloud User Controller
 *
 * Handles client-side operations for the Hitechcloud hosting module.
 */
class Hitechcloud_controller extends HBController {

    /**
     * @var Hitechcloud
     */
    var $module;

    protected $tplDir;
    protected $tplDirUrl;
    protected $service;

    /**
     * Feature privileges - toggleable via widgets.
     *
     * @var array
     */
    protected $privileges = [
        'o_domains'  => true,
        'o_databases'=> true,
        'o_ssl'      => true,
        'o_php'      => true,
        'o_sftp'     => true,
        'o_cronjob'  => true,
        'o_backup'   => true,
        'o_stats'    => true,
        'o_logs'     => true,
    ];

    /**
     * Called before each controller action.
     *
     * @param array $params
     * @return mixed
     */
    public function beforeCall($params) {
        HBRegistry::language()->addTranslation('hitechcloud');

        $this->tplDir = APPDIR_MODULES . 'Hosting'
            . DS . strtolower($this->module->getModuleName())
            . DS . 'templates' . DS;

        $this->tplDirUrl = Utilities::checkSecureUrl(HBConfig::getConfig('InstallURL'))
            . str_replace([MAINDIR, DS], ['', '/'], $this->tplDir);

        return parent::beforeCall($params);
    }

    /**
     * Check if a feature privilege is enabled.
     * If array passed, returns true if at least one is enabled.
     *
     * @param array|string $feature
     * @return bool
     */
    protected function _checkPrivilege($feature) {
        if (is_array($feature)) {
            foreach ($feature as $check) {
                if ($this->_checkPrivilege($check)) {
                    return true;
                }
            }
            return false;
        }
        if (!isset($this->privileges[$feature]) || $this->privileges[$feature]) {
            return true;
        }
        return false;
    }

    /**
     * Get the username for the current account.
     *
     * @return string
     */
    protected function getUsername() {
        return $this->module->getUsername();
    }

    // -------------------------------------------------------------------------
    // Feature methods
    // -------------------------------------------------------------------------

    /**
     * Manage domains (addon domains, subdomains).
     *
     * @param array $params
     * @return void
     */
    public function domains($params) {
        if (!$this->_checkPrivilege('o_domains')) {
            Engine::addError('Access denied');
            return;
        }

        $username = $this->getUsername();

        try {
            $api = $this->module->getApi();

            switch ($params['do'] ?? 'list') {
                case 'add':
                    $result = $api->addDomain($username, $params['domain'], $params['type'] ?? 'addon');
                    $this->template->assign('result', $result);
                    break;

                case 'delete':
                    $result = $api->deleteDomain($username, $params['domain']);
                    $this->template->assign('result', $result);
                    break;

                default:
                    $domains = $api->listDomains($username);
                    $this->template->assign('domains', $domains);
                    break;
            }
        } catch (\Exception $e) {
            Engine::addError('Domains error: ' . $e->getMessage());
        }
    }

    /**
     * Manage databases (MySQL/MariaDB).
     *
     * @param array $params
     * @return void
     */
    public function databases($params) {
        if (!$this->_checkPrivilege('o_databases')) {
            Engine::addError('Access denied');
            return;
        }

        $username = $this->getUsername();

        try {
            $api = $this->module->getApi();

            switch ($params['do'] ?? 'list') {
                case 'create':
                    $result = $api->createDatabase($username, $params['db_name'], $params['db_user'] ?? '', $params['db_password'] ?? '');
                    $this->template->assign('result', $result);
                    break;

                case 'delete':
                    $result = $api->deleteDatabase($username, $params['db_name']);
                    $this->template->assign('result', $result);
                    break;

                default:
                    $databases = $api->listDatabases($username);
                    $this->template->assign('databases', $databases);
                    break;
            }
        } catch (\Exception $e) {
            Engine::addError('Databases error: ' . $e->getMessage());
        }
    }

    /**
     * Manage SSL certificates.
     *
     * @param array $params
     * @return void
     */
    public function ssl($params) {
        if (!$this->_checkPrivilege('o_ssl')) {
            Engine::addError('Access denied');
            return;
        }

        $username = $this->getUsername();

        try {
            $api = $this->module->getApi();

            switch ($params['do'] ?? 'list') {
                case 'issue':
                    $result = $api->issueSSL($username, $params['domain']);
                    $this->template->assign('result', $result);
                    break;

                case 'install':
                    $result = $api->installCustomSSL($username, $params['domain'], $params['certificate'], $params['private_key'], $params['ca_bundle'] ?? '');
                    $this->template->assign('result', $result);
                    break;

                default:
                    $certificates = $api->listSSL($username);
                    $this->template->assign('certificates', $certificates);
                    break;
            }
        } catch (\Exception $e) {
            Engine::addError('SSL error: ' . $e->getMessage());
        }
    }

    /**
     * Manage PHP settings and version.
     *
     * @param array $params
     * @return void
     */
    public function php($params) {
        if (!$this->_checkPrivilege('o_php')) {
            Engine::addError('Access denied');
            return;
        }

        $username = $this->getUsername();

        try {
            $api = $this->module->getApi();

            switch ($params['do'] ?? 'info') {
                case 'switch':
                    $result = $api->switchPHPVersion($username, $params['domain'] ?? '', $params['version']);
                    $this->template->assign('result', $result);
                    break;

                case 'settings':
                    $result = $api->updatePHPSettings($username, $params['domain'] ?? '', $params['settings'] ?? []);
                    $this->template->assign('result', $result);
                    break;

                default:
                    $info = $api->getPHPInfo($username);
                    $this->template->assign('php_info', $info);
                    break;
            }
        } catch (\Exception $e) {
            Engine::addError('PHP error: ' . $e->getMessage());
        }
    }

    /**
     * Manage SFTP/FTP accounts.
     *
     * @param array $params
     * @return void
     */
    public function sftp($params) {
        if (!$this->_checkPrivilege('o_sftp')) {
            Engine::addError('Access denied');
            return;
        }

        $username = $this->getUsername();

        try {
            $api = $this->module->getApi();

            switch ($params['do'] ?? 'list') {
                case 'create':
                    $result = $api->createSFTPAccount($username, $params['ftp_user'], $params['ftp_password'], $params['directory'] ?? '/');
                    $this->template->assign('result', $result);
                    break;

                case 'delete':
                    $result = $api->deleteSFTPAccount($username, $params['ftp_user']);
                    $this->template->assign('result', $result);
                    break;

                default:
                    $accounts = $api->listSFTPAccounts($username);
                    $this->template->assign('sftp_accounts', $accounts);
                    break;
            }
        } catch (\Exception $e) {
            Engine::addError('SFTP error: ' . $e->getMessage());
        }
    }

    /**
     * Manage cron jobs.
     *
     * @param array $params
     * @return void
     */
    public function cronjobs($params) {
        if (!$this->_checkPrivilege('o_cronjob')) {
            Engine::addError('Access denied');
            return;
        }

        $username = $this->getUsername();

        try {
            $api = $this->module->getApi();

            switch ($params['do'] ?? 'list') {
                case 'create':
                    $result = $api->createCronJob($username, $params['schedule'], $params['command']);
                    $this->template->assign('result', $result);
                    break;

                case 'delete':
                    $result = $api->deleteCronJob($username, $params['cron_id']);
                    $this->template->assign('result', $result);
                    break;

                default:
                    $cronjobs = $api->listCronJobs($username);
                    $this->template->assign('cronjobs', $cronjobs);
                    break;
            }
        } catch (\Exception $e) {
            Engine::addError('Cron jobs error: ' . $e->getMessage());
        }
    }

    /**
     * Manage backups.
     *
     * @param array $params
     * @return void
     */
    public function backups($params) {
        if (!$this->_checkPrivilege('o_backup')) {
            Engine::addError('Access denied');
            return;
        }

        $username = $this->getUsername();

        try {
            $api = $this->module->getApi();

            switch ($params['do'] ?? 'list') {
                case 'create':
                    $result = $api->createBackup($username);
                    $this->template->assign('result', $result);
                    break;

                case 'restore':
                    $result = $api->restoreBackup($username, $params['backup_id']);
                    $this->template->assign('result', $result);
                    break;

                case 'delete':
                    $result = $api->deleteBackup($username, $params['backup_id']);
                    $this->template->assign('result', $result);
                    break;

                default:
                    $backups = $api->listBackups($username);
                    $this->template->assign('backups', $backups);
                    break;
            }
        } catch (\Exception $e) {
            Engine::addError('Backups error: ' . $e->getMessage());
        }
    }

    /**
     * View resource usage statistics.
     *
     * @param array $params
     * @return void
     */
    public function stats($params) {
        if (!$this->_checkPrivilege('o_stats')) {
            Engine::addError('Access denied');
            return;
        }

        $username = $this->getUsername();

        try {
            $api   = $this->module->getApi();
            $stats = $api->getUsageStats($username);

            $this->template->assign('stats', $stats);
        } catch (\Exception $e) {
            Engine::addError('Statistics error: ' . $e->getMessage());
        }
    }

    /**
     * View access and error logs.
     *
     * @param array $params
     * @return void
     */
    public function logs($params) {
        if (!$this->_checkPrivilege('o_logs')) {
            Engine::addError('Access denied');
            return;
        }

        $username = $this->getUsername();

        try {
            $api  = $this->module->getApi();
            $type = $params['type'] ?? 'access';
            $domain = $params['domain'] ?? '';
            $lines = (int) ($params['lines'] ?? 100);

            $logs = $api->getLogs($username, $type, $domain, $lines);
            $this->template->assign('logs', $logs);
            $this->template->assign('log_type', $type);
        } catch (\Exception $e) {
            Engine::addError('Logs error: ' . $e->getMessage());
        }
    }
}
