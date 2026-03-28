<div class="hb-box">
    <h3>SSL Certificates</h3>
    <pre>{if isset($certificates)}{print_r($certificates)}{elseif isset($result)}{print_r($result)}{else}No SSL data available.{/if}</pre>
</div>