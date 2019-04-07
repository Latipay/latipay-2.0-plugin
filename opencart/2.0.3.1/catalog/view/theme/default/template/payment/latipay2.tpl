<?php if ($latipay_error): ?>
<div class="well well-sm">
    <?php echo $latipay_error; ?>
</div>
<?php else : ?>
<div class="well well-sm">
    <p><?php echo $text_payment_method; ?></p>
    <?php
        foreach($select_array as $one){
    ?>
    <p style="margin-left:10px;">
        <input type="radio" name="payment_method2" value="<?php echo $one['value'];?>" style="margin-right:10px;">
        <?php echo $one['name'];?>
    </p>
    <?php } ?>
</div>
<div class="buttons">
  <div class="pull-right">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="btn btn-primary" data-loading-text="<?php echo $text_loading; ?>" />
  </div>
</div>
<script type="text/javascript"><!--
$('#button-confirm').on('click', function() {
    var payment_method2 = $('input[name="payment_method2"]:checked').val();
    if (payment_method2 == null || payment_method2 == undefined) {
        alert('Please select the payment method !');
        return;
    }

	$.ajax({
		type: 'post',
		dataType: 'json',
		url: 'index.php?route=payment/latipay2/confirm',
		cache: false,
		data: 'payment_method=' + payment_method2,
		beforeSend: function() {
			$('#button-confirm').button('loading');
		},
		complete: function() {
			$('#button-confirm').button('reset');
		},
		success: function(json) {
			//console.log(json);
			if(json['success'] == 'ok'){
				location = json['redirect_url'];return false;
			}
			if(json['error']){
				alert(json['error']);
			}
		}
	});
});
//--></script>
<?php endif; ?>