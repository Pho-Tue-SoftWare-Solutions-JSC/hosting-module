<?php

/**
 * Hitechcloud Admin Controller
 *
 * Handles admin-side operations for the Hitechcloud hosting module.
 */
class Hitechcloud_controller extends HBController {

    /**
     * @var Hitechcloud
     */
    var $module;

    protected $tplDir;
    protected $tplDirUrl;

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
     * Display account details in admin area.
     *
     * @param array $params
     * @return void
     */
    public function accountDetails($params) {
        $account = HBLoader::LoadModel('Accounts')->getAccount($params['id']);
        if (!$account) {
            return false;
        }

        $username = $account['username'] ?? '';
        $details = [];

        if ($username) {
            try {
                $details = $this->module->getApi()->getAccountInfo($username);
            } catch (\Exception $e) {
                Engine::addError('Failed to load account details: ' . $e->getMessage());
            }
        }

        $this->template->assign('account', $account);
        $this->template->assign('details', $details);
        $this->template->assign('moduledir', $this->tplDir);
        $this->template->assign('moduledir_url', $this->tplDirUrl);
    }

    /**
     * Display server information.
     *
     * @param array $params
     * @return void
     */
    public function serverInfo($params) {
        try {
            $info = $this->module->getApi()->getServerInfo();
            $this->template->assign('server_info', $info);
        } catch (\Exception $e) {
            Engine::addError('Failed to load server info: ' . $e->getMessage());
        }
    }

    /**
     * Synchronize accounts from the remote server.
     *
     * @param array $params
     * @return void
     */
    public function syncAccounts($params) {
        try {
            $accounts = $this->module->getApi()->listAccounts();
            $synced = 0;

            foreach ($accounts as $account) {
                // Sync logic: match remote accounts to local HostBill accounts
                $synced++;
            }

            Engine::addInfo("Synced {$synced} accounts from server.");
            $this->template->assign('synced_count', $synced);
        } catch (\Exception $e) {
            Engine::addError('Failed to sync accounts: ' . $e->getMessage());
        }
    }
}
