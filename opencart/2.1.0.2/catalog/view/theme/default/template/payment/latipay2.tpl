<div class="well well-sm">
  <?php echo $text_payment_method; ?>
  <select class="form-control" id="payment_method2" name="payment_method2">
  		<?php
        	foreach($select_array as $one){
        ?>
        <option value="<?php echo $one['value'];?>"><?php echo $one['name'];?></option>
        <?php } ?>
    </select>
</div>
<div class="buttons">
  <div class="pull-right">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="btn btn-primary" data-loading-text="<?php echo $text_loading; ?>" />
  </div>
</div>
<script type="text/javascript"><!--
$('#button-confirm').on('click', function() {
	$.ajax({
		type: 'post',
		dataType: 'json',
		url: 'index.php?route=payment/latipay2/confirm',
		cache: false,
		data: 'payment_method=' + $('#payment_method2').val(),
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
