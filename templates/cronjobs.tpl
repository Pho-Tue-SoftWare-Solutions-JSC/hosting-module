<div class="hb-box">
    <h3>Cron Jobs</h3>
    <pre>{if isset($cronjobs)}{print_r($cronjobs)}{elseif isset($result)}{print_r($result)}{else}No cron job data available.{/if}</pre>
</div>