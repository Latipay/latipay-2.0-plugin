<?php
$installer = $this;
$installer->startSetup();
$setup = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
$setup->addAttribute('quote_payment', 'latipay_method', array('type' => 'varchar'));
$setup->addAttribute('order_payment', 'latipay_method', array('type' => 'varchar'));
$installer->endSetup();