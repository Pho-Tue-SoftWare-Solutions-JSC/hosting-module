<?php

/**
 * Hitechcloud Event Handler
 *
 * Observer pattern class that handles account lifecycle events
 * for the Hitechcloud hosting module.
 */
class Hitechcloud_handle {

    /**
     * @var Hitechcloud
     */
    protected $module;

    /**
     * Constructor.
     *
     * @param Hitechcloud $module
     */
    public function __construct($module = null) {
        $this->module = $module;
    }

    /**
     * Handle account creation event.
     *
     * @param array $data Event data containing account information
     * @return void
     */
    public function onAccountCreated($data) {
        $accountId = $data['account_id'] ?? 0;
        if (!$accountId) {
            return;
        }

        HBDebug::debug('Hitechcloud: Account created event for #' . $accountId);

        // Post-creation tasks (e.g., default DNS records, welcome email data)
        try {
            if ($this->module) {
                $this->module->loadAccount($accountId);
                $api      = $this->module->getApi();
                $username = $data['username'] ?? '';

                if ($username) {
                    $api->postCreate($username);
                }
            }
        } catch (\Exception $e) {
            HBDebug::debug('Hitechcloud: Post-create event failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle account suspension event.
     *
     * @param array $data Event data containing account information
     * @return void
     */
    public function onAccountSuspended($data) {
        $accountId = $data['account_id'] ?? 0;
        if (!$accountId) {
            return;
        }

        HBDebug::debug('Hitechcloud: Account suspended event for #' . $accountId);
    }

    /**
     * Handle account unsuspension event.
     *
     * @param array $data Event data containing account information
     * @return void
     */
    public function onAccountUnsuspended($data) {
        $accountId = $data['account_id'] ?? 0;
        if (!$accountId) {
            return;
        }

        HBDebug::debug('Hitechcloud: Account unsuspended event for #' . $accountId);
    }

    /**
     * Handle account termination event.
     *
     * @param array $data Event data containing account information
     * @return void
     */
    public function onAccountTerminated($data) {
        $accountId = $data['account_id'] ?? 0;
        if (!$accountId) {
            return;
        }

        HBDebug::debug('Hitechcloud: Account terminated event for #' . $accountId);

        // Cleanup tasks (e.g., remove cached data, DNS records)
        try {
            if ($this->module) {
                $username = $data['username'] ?? '';
                if ($username) {
                    $this->module->getApi()->postTerminate($username);
                }
            }
        } catch (\Exception $e) {
            HBDebug::debug('Hitechcloud: Post-terminate event failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle password change event.
     *
     * @param array $data Event data containing account information
     * @return void
     */
    public function onPasswordChanged($data) {
        $accountId = $data['account_id'] ?? 0;
        HBDebug::debug('Hitechcloud: Password changed event for #' . $accountId);
    }

    /**
     * Handle package change event.
     *
     * @param array $data Event data containing account information
     * @return void
     */
    public function onPackageChanged($data) {
        $accountId = $data['account_id'] ?? 0;
        HBDebug::debug('Hitechcloud: Package changed event for #' . $accountId);
    }
}
