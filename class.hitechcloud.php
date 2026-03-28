<?php

/**
 * Hitechcloud Shared Hosting Module for HostBill
 *
 * Manages shared hosting accounts via Hitechcloud Agent API.
 */

require_once __DIR__ . '/lib/include.php';

use Hosting\Hitechcloud\lib\HitechcloudAPI;
use Hosting\Hitechcloud\lib\Constants;

class Hitechcloud
    extends HostingModule
    implements Constants
{
    protected $modname     = 'Hitechcloud';
    protected $description = 'Hitechcloud Shared Hosting Management';
    protected $version     = '1.0.0';

    /** @var HitechcloudAPI|null */
    protected $api = null;

    // -------------------------------------------------------------------------
    // Server connection fields
    // -------------------------------------------------------------------------

    protected $serverFields = [
        self::CONNECTION_FIELD_CHECKBOX   => false,
        self::CONNECTION_FIELD_MAXACCOUNTS => false,
        self::CONNECTION_FIELD_STATUSURL  => false,
        self::CONNECTION_FIELD_INPUT1     => true,  // API URL
        self::CONNECTION_FIELD_INPUT2     => true,  // API Key
    ];

    protected $serverFieldsDescription = [
        self::CONNECTION_FIELD_INPUT1 => 'API URL <a class="vtip_description">Full URL to Hitechcloud Agent API (e.g. https://server.example.com:8443)</a>',
        self::CONNECTION_FIELD_INPUT2 => 'API Key <a class="vtip_description">API key for authentication with Hitechcloud Agent</a>',
    ];

    // -------------------------------------------------------------------------
    // Product configuration options (shown in HostBill product setup)
    // -------------------------------------------------------------------------

    protected $options = [
        self::O_PLAN_NAME => [
            'type'  => 'input',
            'default' => 'default',
        ],
        self::O_DISK_QUOTA => [
            'type'  => 'input',
            'default' => '10240',
        ],
        self::O_BANDWIDTH => [
            'type'  => 'input',
            'default' => '102400',
        ],
        self::O_MAX_DOMAINS => [
            'type'  => 'input',
            'default' => '5',
        ],
        self::O_MAX_DATABASES => [
            'type'  => 'input',
            'default' => '5',
        ],
        self::O_MAX_FTP => [
            'type'  => 'input',
            'default' => '5',
        ],
        self::O_MAX_CRONJOBS => [
            'type'  => 'input',
            'default' => '3',
        ],
        self::O_PHP_VERSION => [
            'type'    => 'select',
            'default' => ['7.4', '8.0', '8.1', '8.2', '8.3'],
        ],
        self::O_SHELL_ACCESS => [
            'type' => 'check',
        ],
        self::O_SSL_ENABLED => [
            'type' => 'check',
        ],
        self::O_BACKUP_ENABLED => [
            'type' => 'check',
        ],
    ];

    // -------------------------------------------------------------------------
    // Account detail fields (per-account data stored by HostBill)
    // -------------------------------------------------------------------------

    protected $details = [
        self::D_USERNAME => [
            'name'    => 'username',
            'value'   => false,
            'type'    => 'input',
            'default' => false,
        ],
        self::D_PASSWORD => [
            'name'    => 'password',
            'value'   => false,
            'type'    => 'hidden',
            'default' => false,
        ],
        self::D_DOMAIN => [
            'name'    => 'domain',
            'value'   => false,
            'type'    => 'input',
            'default' => false,
        ],
    ];

    // -------------------------------------------------------------------------
    // Connection / API helpers
    // -------------------------------------------------------------------------

    /**
     * Called by HostBill when server connection is established.
     * Stores connection data and initializes the API client.
     *
     * @param array $connect Server connection data from HostBill
     */
    public function connect($connect)
    {
        $server = [];
        $server['host']    = (!$connect['ip'] && $connect['host']) ? $connect['host'] : $connect['ip'];
        $server['api_url'] = !empty($connect['field1']) ? rtrim($connect['field1'], '/') : '';
        $server['api_key'] = !empty($connect['field2']) ? $connect['field2'] : '';

        $server['username'] = $connect['username'];
        $server['password'] = $connect['password'];

        $this->connection = $server;

        if (!empty($server['api_url']) && !empty($server['api_key'])) {
            try {
                $this->api = new HitechcloudAPI($server['api_url'], $server['api_key']);
            } catch (\Exception $ex) {
                $this->addError($ex->getMessage());
            }
        }
    }

    /**
     * Get or create the API client instance.
     *
     * @return HitechcloudAPI
     * @throws \RuntimeException if connection data is missing
     */
    protected function getApi()
    {
        if ($this->api !== null) {
            return $this->api;
        }

        if (empty($this->connection['api_url']) || empty($this->connection['api_key'])) {
            throw new \RuntimeException('Hitechcloud API connection not configured');
        }

        $this->api = new HitechcloudAPI($this->connection['api_url'], $this->connection['api_key']);
        return $this->api;
    }

    /**
     * Test connection to the Hitechcloud server.
     *
     * @param callable|null $log Optional logger callback
     * @return bool
     */
    public function testConnection($log = null)
    {
        $conn = $this->connection;
        if ($log) {
            $log(\Monolog\Logger::INFO, "Connecting to {$conn['api_url']}");
        }

        try {
            $api    = $this->getApi();
            $result = $api->getHealth();

            if ($log && isset($result['version'])) {
                $log(\Monolog\Logger::INFO, "Hitechcloud Agent version: {$result['version']}");
            }

            return true;
        } catch (\Exception $ex) {
            $this->addError('Connection test failed: ' . $ex->getMessage());
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Helper: build account parameters from HostBill options/details
    // -------------------------------------------------------------------------

    /**
     * Build the account creation/package parameters array from HostBill data.
     *
     * @return array
     */
    protected function buildAccountParams()
    {
        return [
            'username'       => $this->details[self::D_USERNAME]['value'],
            'password'       => $this->details[self::D_PASSWORD]['value'],
            'domain'         => $this->details[self::D_DOMAIN]['value'],
            'plan_name'      => $this->getOption(self::O_PLAN_NAME),
            'disk_quota'     => (int) $this->getOption(self::O_DISK_QUOTA),
            'bandwidth'      => (int) $this->getOption(self::O_BANDWIDTH),
            'max_domains'    => (int) $this->getOption(self::O_MAX_DOMAINS),
            'max_databases'  => (int) $this->getOption(self::O_MAX_DATABASES),
            'max_ftp'        => (int) $this->getOption(self::O_MAX_FTP),
            'max_cronjobs'   => (int) $this->getOption(self::O_MAX_CRONJOBS),
            'php_version'    => $this->getOption(self::O_PHP_VERSION),
            'shell_access'   => (bool) $this->getOption(self::O_SHELL_ACCESS),
            'ssl_enabled'    => (bool) $this->getOption(self::O_SSL_ENABLED),
            'backup_enabled' => (bool) $this->getOption(self::O_BACKUP_ENABLED),
        ];
    }

    /**
     * Safely read an option value.
     *
     * @param string $key
     * @return mixed
     */
    protected function getOption($key)
    {
        if (isset($this->options[$key]['value'])) {
            return $this->options[$key]['value'];
        }
        if (isset($this->options[$key]['default'])) {
            $def = $this->options[$key]['default'];
            return is_array($def) ? reset($def) : $def;
        }
        return '';
    }

    /**
     * Get the username for the current account.
     *
     * @return string
     */
    protected function getUsername()
    {
        if (!empty($this->details[self::D_USERNAME]['value'])) {
            return $this->details[self::D_USERNAME]['value'];
        }
        if (!empty($this->account_details['username'])) {
            return $this->account_details['username'];
        }
        return '';
    }

    // -------------------------------------------------------------------------
    // Provisioning methods
    // -------------------------------------------------------------------------

    /**
     * Create hosting account.
     *
     * @return bool
     */
    public function Create()
    {
        if ($this->account_details['status'] === 'Active' || $this->account_details['status'] === 'Suspended') {
            $this->addError('Service already provisioned, terminate it before re-provisioning');
            return false;
        }

        try {
            $api    = $this->getApi();
            $params = $this->buildAccountParams();

            if (empty($params['username'])) {
                $this->addError('Username is required to create account');
                return false;
            }
            if (empty($params['domain'])) {
                $this->addError('Domain is required to create account');
                return false;
            }

            $result = $api->createAccount($params);

            // Store the username returned by the API (if any)
            if (!empty($result['username'])) {
                $this->details[self::D_USERNAME]['value'] = $result['username'];
                $this->account_details['username'] = $result['username'];
            }

            return true;
        } catch (\Exception $ex) {
            $this->addError('Failed to create account: ' . $ex->getMessage());
            return false;
        }
    }

    /**
     * Suspend hosting account.
     *
     * @return bool
     */
    public function Suspend()
    {
        try {
            $api      = $this->getApi();
            $username = $this->getUsername();

            if (empty($username)) {
                $this->addError('Cannot suspend: username not found');
                return false;
            }

            $api->suspendAccount($username, 'Suspended by HostBill');
            return true;
        } catch (\Exception $ex) {
            $this->addError('Failed to suspend account: ' . $ex->getMessage());
            return false;
        }
    }

    /**
     * Unsuspend hosting account.
     *
     * @return bool
     */
    public function Unsuspend()
    {
        try {
            $api      = $this->getApi();
            $username = $this->getUsername();

            if (empty($username)) {
                $this->addError('Cannot unsuspend: username not found');
                return false;
            }

            $api->unsuspendAccount($username);
            return true;
        } catch (\Exception $ex) {
            $this->addError('Failed to unsuspend account: ' . $ex->getMessage());
            return false;
        }
    }

    /**
     * Terminate (delete) hosting account.
     *
     * @return bool
     */
    public function Terminate()
    {
        try {
            $api      = $this->getApi();
            $username = $this->getUsername();

            if (empty($username)) {
                $this->addError('Cannot terminate: username not found');
                return false;
            }

            $api->terminateAccount($username);
            return true;
        } catch (\Exception $ex) {
            $this->addError('Failed to terminate account: ' . $ex->getMessage());
            return false;
        }
    }

    /**
     * Change account password.
     *
     * @param string $newpassword
     * @return bool
     */
    public function ChangePassword($newpassword)
    {
        try {
            $api      = $this->getApi();
            $username = $this->getUsername();

            if (empty($username)) {
                $this->addError('Cannot change password: username not found');
                return false;
            }

            $api->changePassword($username, $newpassword);

            // Update stored password
            $this->details[self::D_PASSWORD]['value'] = $newpassword;
            return true;
        } catch (\Exception $ex) {
            $this->addError('Failed to change password: ' . $ex->getMessage());
            return false;
        }
    }

    /**
     * Change account package (upgrade/downgrade).
     *
     * @return bool
     */
    public function ChangePackage()
    {
        try {
            $api      = $this->getApi();
            $username = $this->getUsername();

            if (empty($username)) {
                $this->addError('Cannot change package: username not found');
                return false;
            }

            $package = [
                'plan_name'      => $this->getOption(self::O_PLAN_NAME),
                'disk_quota'     => (int) $this->getOption(self::O_DISK_QUOTA),
                'bandwidth'      => (int) $this->getOption(self::O_BANDWIDTH),
                'max_domains'    => (int) $this->getOption(self::O_MAX_DOMAINS),
                'max_databases'  => (int) $this->getOption(self::O_MAX_DATABASES),
                'max_ftp'        => (int) $this->getOption(self::O_MAX_FTP),
                'max_cronjobs'   => (int) $this->getOption(self::O_MAX_CRONJOBS),
                'php_version'    => $this->getOption(self::O_PHP_VERSION),
                'shell_access'   => (bool) $this->getOption(self::O_SHELL_ACCESS),
                'ssl_enabled'    => (bool) $this->getOption(self::O_SSL_ENABLED),
                'backup_enabled' => (bool) $this->getOption(self::O_BACKUP_ENABLED),
            ];

            $api->changePackage($username, $package);
            return true;
        } catch (\Exception $ex) {
            $this->addError('Failed to change package: ' . $ex->getMessage());
            return false;
        }
    }
}
