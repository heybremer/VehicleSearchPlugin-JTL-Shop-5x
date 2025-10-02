{* Vehicle Search Plugin Header Template for JTL Shop 5.x *}
{* This template includes CSS and JS resources in the header *}

{block name='vehicle-search-header'}
<link rel="stylesheet" href="{$PluginUrl}frontend/css/vehicle-search.css" type="text/css" />
<script>
    // Global plugin configuration
    window.VehicleSearchPlugin = {
        pluginUrl: '{$PluginUrl}',
        csrfToken: '{$smarty.session.csrf_token}',
        shopUrl: '{$ShopURL}',
        sessionId: '{$smarty.session.sessionID}'
    };
</script>
{/block}