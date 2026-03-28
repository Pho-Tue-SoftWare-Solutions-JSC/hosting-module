<?php

/**
 * Hitechcloud Cron Controller
 *
 * Handles scheduled tasks for the Hitechcloud hosting module.
 */
class Hitechcloud_controller extends HBController {

    /**
     * @var Hitechcloud
     */
    var $module;

    /**
     * Hourly cron task: sync bandwidth usage for all active accounts.
     *
     * @return void
     */
    public function call_Hourly() {
        $accounts = HBLoader::LoadModel('Accounts')->getActiveAccountsByModule('hitechcloud');

        if (empty($accounts)) {
            return;
        }

        foreach ($accounts as $account) {
            try {
                $this->module->loadAccount($account['id']);
                $api      = $this->module->getApi();
                $username = $account['username'] ?? '';

                if (empty($username)) {
                    continue;
                }

                $usage = $api->getBandwidthUsage($username);

                if (!empty($usage)) {
                    $this->module->updateBandwidthUsage($account['id'], [
                        'bandwidth_used' => $usage['used'] ?? 0,
                        'bandwidth_limit'=> $usage['limit'] ?? 0,
                    ]);
                }
            } catch (\Exception $e) {
                HBDebug::debug('Hitechcloud cron hourly error for account ' . $account['id'] . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Daily cron task: sync disk usage and check SSL certificate renewals.
     *
     * @return void
     */
    public function call_Daily() {
        $accounts = HBLoader::LoadModel('Accounts')->getActiveAccountsByModule('hitechcloud');

        if (empty($accounts)) {
            return;
        }

        foreach ($accounts as $account) {
            try {
                $this->module->loadAccount($account['id']);
                $api      = $this->module->getApi();
                $username = $account['username'] ?? '';

                if (empty($username)) {
                    continue;
                }

                // Sync disk usage
                $diskUsage = $api->getDiskUsage($username);
                if (!empty($diskUsage)) {
                    $this->module->updateDiskUsage($account['id'], [
                        'disk_used'  => $diskUsage['used'] ?? 0,
                        'disk_limit' => $diskUsage['limit'] ?? 0,
                    ]);
                }

                // Check SSL certificate renewals
                $sslCerts = $api->listSSL($username);
                if (!empty($sslCerts)) {
                    foreach ($sslCerts as $cert) {
                        $expiry = strtotime($cert['expires'] ?? '');
                        // Renew if expiring within 7 days
                        if ($expiry && ($expiry - time()) < (7 * 86400)) {
                            try {
                                $api->renewSSL($username, $cert['domain']);
                                HBDebug::debug('Hitechcloud: renewed SSL for ' . $cert['domain'] . ' (account ' . $account['id'] . ')');
                            } catch (\Exception $e) {
                                HBDebug::debug('Hitechcloud: SSL renewal failed for ' . $cert['domain'] . ': ' . $e->getMessage());
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                HBDebug::debug('Hitechcloud cron daily error for account ' . $account['id'] . ': ' . $e->getMessage());
            }
        }
    }
}
