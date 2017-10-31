{* $Id$ *}

<div class="control-group">
    <label for="user_id" class="control-label">{__("user_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][user_id]" id="user_id" value="{$processor_params.user_id}" class="input-text" />
    </div>
</div>
<div class="control-group">
    <label for="wallet_id" class="control-label">{__("wallet_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][wallet_id]" id="wallet_id" value="{$processor_params.wallet_id}" class="input-text" />
    </div>
</div>
<div class="control-group">
    <label for="api_key" class="control-label">{__("api_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][api_key]" id="api_key" value="{$processor_params.api_key}" class="input-text" />
    </div>
</div>
