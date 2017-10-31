<div class="ty-control-group">
    <label for="payment_method" class="cm-required">{__("payment_method")}:</label>
    <select name="payment_info[payment_method]" id="payment_method">
        <option value="alipay" {if $cart.payment_info.payment_method=='alipay'}selected="selected"{/if}>{__("alipay")}</option>
        <option value="wechat" {if $cart.payment_info.payment_method=='wechat'}selected="selected"{/if}>{__("wechat")}</option>
        <option value="onlineBank" {if $cart.payment_info.payment_method=='onlineBank'}selected="selected"{/if}>{__("onlineBank")}</option>
    </select>
</div>