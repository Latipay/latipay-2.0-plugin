<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-latipay2" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-latipay2" class="form-horizontal">
          
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-user_id"><span data-toggle="tooltip" title="<?php echo $entry_user_id; ?>"><?php echo $entry_user_id; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="latipay2_user_id" value="<?php echo $latipay2_user_id; ?>" placeholder="<?php echo $entry_user_id; ?>" id="input-user_id" class="form-control" />
              <?php if ($error_user_id) { ?>
              <div class="text-danger"><?php echo $error_user_id; ?></div>
              <?php } ?>
            </div>
          </div>
          
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-api_key"><span data-toggle="tooltip" title="<?php echo $entry_api_key; ?>"><?php echo $entry_api_key; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="latipay2_api_key" value="<?php echo $latipay2_api_key; ?>" placeholder="<?php echo $entry_api_key; ?>" id="input-api_key" class="form-control" />
              <?php if ($error_api_key) { ?>
              <div class="text-danger"><?php echo $error_api_key; ?></div>
              <?php } ?>
            </div>
          </div>
          
          <!-- NZD -->
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-wallet_id_nzd"><span data-toggle="tooltip" title="<?php echo $entry_wallet_id_nzd; ?>"><?php echo $entry_wallet_id_nzd; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="latipay2_wallet_id_nzd" value="<?php echo $latipay2_wallet_id_nzd; ?>" placeholder="<?php echo $entry_wallet_id_nzd; ?>" id="input-wallet_id_nzd" class="form-control" />
              <?php if ($error_wallet_id_nzd) { ?>
              <div class="text-danger"><?php echo $error_wallet_id_nzd; ?></div>
              <?php } ?>
            </div>
          </div>
          
          <!-- AUD -->
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-wallet_id_aud"><span data-toggle="tooltip" title="<?php echo $entry_wallet_id_aud; ?>"><?php echo $entry_wallet_id_aud; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="latipay2_wallet_id_aud" value="<?php echo $latipay2_wallet_id_aud; ?>" placeholder="<?php echo $entry_wallet_id_aud; ?>" id="input-wallet_id_aud" class="form-control" />
              <?php if ($error_wallet_id_aud) { ?>
              <div class="text-danger"><?php echo $error_wallet_id_aud; ?></div>
              <?php } ?>
            </div>
          </div>
          
          <!-- CNY -->
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-wallet_id_cny"><span data-toggle="tooltip" title="<?php echo $entry_wallet_id_cny; ?>"><?php echo $entry_wallet_id_cny; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="latipay2_wallet_id_cny" value="<?php echo $latipay2_wallet_id_cny; ?>" placeholder="<?php echo $entry_wallet_id_cny; ?>" id="input-wallet_id_cny" class="form-control" />
              <?php if ($error_wallet_id_cny) { ?>
              <div class="text-danger"><?php echo $error_wallet_id_cny; ?></div>
              <?php } ?>
            </div>
          </div>
			
          
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-huan_jing"><?php echo $entry_huan_jing; ?></label>
            <div class="col-sm-10">
              <select name="latipay2_huan_jing" id="input-huan_jing" class="form-control">
                
                <option value="0" <?php if($latipay2_huan_jing == '0'){ echo 'selected="selected"'; }?>><?php echo $entry_huan_jing_ce_shi; ?></option>
                <option value="1" <?php if($latipay2_huan_jing == '1'){ echo 'selected="selected"'; }?>><?php echo $entry_huan_jing_zheng_shi; ?></option>

              </select>
            </div>
          </div>
          
          
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-payment_method_url"><span data-toggle="tooltip" title="<?php echo $entry_payment_method_url; ?>"><?php echo $entry_payment_method_url; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="latipay2_payment_method_url" value="<?php echo $latipay2_payment_method_url; ?>" placeholder="<?php echo $entry_payment_method_url; ?>" id="input-payment_method_url" class="form-control" />
              <?php if ($error_payment_method_url) { ?>
              <div class="text-danger"><?php echo $error_payment_method_url; ?></div>
              <?php } ?>
            </div>
          </div>
          
          
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-gateway_url"><span data-toggle="tooltip" title="<?php echo $entry_gateway_url; ?>"><?php echo $entry_gateway_url; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="latipay2_gateway_url" value="<?php echo $latipay2_gateway_url; ?>" placeholder="<?php echo $entry_gateway_url; ?>" id="input-gateway_url" class="form-control" />
              <?php if ($error_gateway_url) { ?>
              <div class="text-danger"><?php echo $error_gateway_url; ?></div>
              <?php } ?>
            </div>
          </div>
            
          
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-total"><span data-toggle="tooltip" title="<?php echo $help_total; ?>"><?php echo $entry_total; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="latipay2_total" value="<?php echo $latipay2_total; ?>" placeholder="<?php echo $entry_total; ?>" id="input-total" class="form-control" />
            </div>
          </div>
          
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-order-status"><?php echo $entry_order_status; ?></label>
            <div class="col-sm-10">
              <select name="latipay2_order_status_id" id="input-order-status" class="form-control">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $latipay2_order_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-geo-zone"><?php echo $entry_geo_zone; ?></label>
            <div class="col-sm-10">
              <select name="latipay2_geo_zone_id" id="input-geo-zone" class="form-control">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                <?php if ($geo_zone['geo_zone_id'] == $latipay2_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
          
          
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="latipay2_status" id="input-status" class="form-control">
                <?php if ($latipay2_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
            <div class="col-sm-10">
              <input type="text" name="latipay2_sort_order" value="<?php echo $latipay2_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?> 