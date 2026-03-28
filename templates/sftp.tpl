<div class="hb-box">
    <h3>SFTP / FTP Accounts</h3>
    <pre>{if isset($sftp_accounts)}{print_r($sftp_accounts)}{elseif isset($result)}{print_r($result)}{else}No SFTP data available.{/if}</pre>
</div>