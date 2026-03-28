<div class="hb-box">
    <h3>Hitechcloud Account Details</h3>

    <table class="table table-bordered table-striped">
        <tr>
            <th style="width:220px;">Account ID</th>
            <td>{$account.id|default:'-'}</td>
        </tr>
        <tr>
            <th>Username</th>
            <td>{$account.username|default:'-'}</td>
        </tr>
        <tr>
            <th>Domain</th>
            <td>{$account.domain|default:'-'}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{$account.status|default:'-'}</td>
        </tr>
    </table>

    <h4>Remote Details</h4>
    <pre>{if $details}{print_r($details)}{else}No remote details available.{/if}</pre>
</div>