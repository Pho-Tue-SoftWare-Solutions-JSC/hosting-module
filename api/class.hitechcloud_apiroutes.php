<?php

/**
 * Hitechcloud API Routes
 *
 * Defines REST API routes for external access to the Hitechcloud module.
 *
 * @category Services
 */
class hitechcloud_apiroutes {

    /**
     * Load the Hitechcloud module for a given service.
     *
     * @param int $id Service ID
     * @return Hitechcloud
     * @throws Exception
     */
    protected function module($id) {
        $module = UserApi::module($id, 'hitechcloud');
        return $module;
    }

    /**
     * Get Account Details
     *
     * Get hosting account details for a service
     *
     * @route GET /service/$id/account
     */
    public function account_get($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();

        if (!$username) {
            Engine::addError('Account not found');
            return;
        }

        try {
            $info = $module->getApi()->getAccountInfo($username);
            UserApi::render([
                'account' => $info,
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * List Domains
     *
     * List all domains for a hosting account
     *
     * @route GET /service/$id/domains
     */
    public function domains_list($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();

        try {
            $domains = $module->getApi()->listDomains($username);
            UserApi::render([
                'domains' => $domains ?: [],
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * Add Domain
     *
     * Add a domain to the hosting account
     *
     * @route POST /service/$id/domains
     * @param string $domain Domain name
     * @param string $type Domain type (addon, subdomain)
     */
    public function domains_add($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();
        $params   = UserApi::params();

        try {
            $result = $module->getApi()->addDomain($username, $params['domain'], $params['type'] ?? 'addon');
            UserApi::render([
                'status' => (bool) $result,
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * Delete Domain
     *
     * Remove a domain from the hosting account
     *
     * @route DELETE /service/$id/domains/$domain
     * @param string $domain Domain name to remove
     */
    public function domains_delete($id, $domain) {
        $module   = $this->module($id);
        $username = $module->getUsername();

        try {
            $result = $module->getApi()->deleteDomain($username, $domain);
            UserApi::render([
                'status' => (bool) $result,
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * List Databases
     *
     * List all databases for a hosting account
     *
     * @route GET /service/$id/databases
     */
    public function databases_list($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();

        try {
            $databases = $module->getApi()->listDatabases($username);
            UserApi::render([
                'databases' => $databases ?: [],
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * Create Database
     *
     * Create a new database
     *
     * @route POST /service/$id/databases
     * @param string $db_name Database name
     * @param string $db_user Database user
     * @param string $db_password Database password
     */
    public function databases_create($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();
        $params   = UserApi::params();

        try {
            $result = $module->getApi()->createDatabase($username, $params['db_name'], $params['db_user'] ?? '', $params['db_password'] ?? '');
            UserApi::render([
                'status' => (bool) $result,
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * Delete Database
     *
     * Delete a database
     *
     * @route DELETE /service/$id/databases/$db_name
     * @param string $db_name Database name
     */
    public function databases_delete($id, $db_name) {
        $module   = $this->module($id);
        $username = $module->getUsername();

        try {
            $result = $module->getApi()->deleteDatabase($username, $db_name);
            UserApi::render([
                'status' => (bool) $result,
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * List SSL Certificates
     *
     * List all SSL certificates for a hosting account
     *
     * @route GET /service/$id/ssl
     */
    public function ssl_list($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();

        try {
            $certs = $module->getApi()->listSSL($username);
            UserApi::render([
                'certificates' => $certs ?: [],
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * Issue SSL Certificate
     *
     * Issue a new Let's Encrypt SSL certificate
     *
     * @route POST /service/$id/ssl
     * @param string $domain Domain name
     */
    public function ssl_issue($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();
        $params   = UserApi::params();

        try {
            $result = $module->getApi()->issueSSL($username, $params['domain']);
            UserApi::render([
                'status' => (bool) $result,
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * Get Usage Statistics
     *
     * Get disk, bandwidth, and CPU usage statistics
     *
     * @route GET /service/$id/stats
     */
    public function stats($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();

        try {
            $stats = $module->getApi()->getUsageStats($username);
            UserApi::render([
                'stats' => $stats ?: [],
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * List Backups
     *
     * List all backups for a hosting account
     *
     * @route GET /service/$id/backups
     */
    public function backups_list($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();

        try {
            $backups = $module->getApi()->listBackups($username);
            UserApi::render([
                'backups' => $backups ?: [],
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * Create Backup
     *
     * Create a new backup
     *
     * @route POST /service/$id/backups
     */
    public function backups_create($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();

        try {
            $result = $module->getApi()->createBackup($username);
            UserApi::render([
                'status' => (bool) $result,
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * Restore Backup
     *
     * Restore a backup by ID
     *
     * @route POST /service/$id/backups/$backup_id/restore
     * @param string $backup_id Backup ID
     */
    public function backups_restore($id, $backup_id) {
        $module   = $this->module($id);
        $username = $module->getUsername();

        try {
            $result = $module->getApi()->restoreBackup($username, $backup_id);
            UserApi::render([
                'status' => (bool) $result,
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * Delete Backup
     *
     * Delete a backup by ID
     *
     * @route DELETE /service/$id/backups/$backup_id
     * @param string $backup_id Backup ID
     */
    public function backups_delete($id, $backup_id) {
        $module   = $this->module($id);
        $username = $module->getUsername();

        try {
            $result = $module->getApi()->deleteBackup($username, $backup_id);
            UserApi::render([
                'status' => (bool) $result,
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * List Cron Jobs
     *
     * List all cron jobs for a hosting account
     *
     * @route GET /service/$id/cronjobs
     */
    public function cronjobs_list($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();

        try {
            $cronjobs = $module->getApi()->listCronJobs($username);
            UserApi::render([
                'cronjobs' => $cronjobs ?: [],
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * Create Cron Job
     *
     * Create a new cron job
     *
     * @route POST /service/$id/cronjobs
     * @param string $schedule Cron schedule expression
     * @param string $command Command to execute
     */
    public function cronjobs_create($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();
        $params   = UserApi::params();

        try {
            $result = $module->getApi()->createCronJob($username, $params['schedule'], $params['command']);
            UserApi::render([
                'status' => (bool) $result,
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * Delete Cron Job
     *
     * Delete a cron job by ID
     *
     * @route DELETE /service/$id/cronjobs/$cron_id
     * @param string $cron_id Cron job ID
     */
    public function cronjobs_delete($id, $cron_id) {
        $module   = $this->module($id);
        $username = $module->getUsername();

        try {
            $result = $module->getApi()->deleteCronJob($username, $cron_id);
            UserApi::render([
                'status' => (bool) $result,
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * Get PHP Info
     *
     * Get PHP version and settings
     *
     * @route GET /service/$id/php
     */
    public function php_info($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();

        try {
            $info = $module->getApi()->getPHPInfo($username);
            UserApi::render([
                'php' => $info ?: [],
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * Switch PHP Version
     *
     * Change PHP version for a domain
     *
     * @route POST /service/$id/php/version
     * @param string $domain Domain name
     * @param string $version PHP version
     */
    public function php_switch($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();
        $params   = UserApi::params();

        try {
            $result = $module->getApi()->switchPHPVersion($username, $params['domain'] ?? '', $params['version']);
            UserApi::render([
                'status' => (bool) $result,
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * List SFTP Accounts
     *
     * List SFTP/FTP accounts
     *
     * @route GET /service/$id/sftp
     */
    public function sftp_list($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();

        try {
            $accounts = $module->getApi()->listSFTPAccounts($username);
            UserApi::render([
                'sftp_accounts' => $accounts ?: [],
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * Create SFTP Account
     *
     * Create a new SFTP/FTP account
     *
     * @route POST /service/$id/sftp
     * @param string $ftp_user FTP username
     * @param string $ftp_password FTP password
     * @param string $directory Home directory
     */
    public function sftp_create($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();
        $params   = UserApi::params();

        try {
            $result = $module->getApi()->createSFTPAccount($username, $params['ftp_user'], $params['ftp_password'], $params['directory'] ?? '/');
            UserApi::render([
                'status' => (bool) $result,
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * Delete SFTP Account
     *
     * Delete an SFTP/FTP account
     *
     * @route DELETE /service/$id/sftp/$ftp_user
     * @param string $ftp_user FTP username
     */
    public function sftp_delete($id, $ftp_user) {
        $module   = $this->module($id);
        $username = $module->getUsername();

        try {
            $result = $module->getApi()->deleteSFTPAccount($username, $ftp_user);
            UserApi::render([
                'status' => (bool) $result,
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }

    /**
     * Get Logs
     *
     * Get access or error logs
     *
     * @route GET /service/$id/logs
     * @param string $type Log type (access, error)
     * @param string $domain Domain name
     * @param int $lines Number of lines
     */
    public function logs($id) {
        $module   = $this->module($id);
        $username = $module->getUsername();
        $params   = UserApi::params();

        try {
            $logs = $module->getApi()->getLogs(
                $username,
                $params['type'] ?? 'access',
                $params['domain'] ?? '',
                (int) ($params['lines'] ?? 100)
            );
            UserApi::render([
                'logs' => $logs ?: [],
            ]);
        } catch (\Exception $e) {
            Engine::addError($e->getMessage());
        }
    }
}
