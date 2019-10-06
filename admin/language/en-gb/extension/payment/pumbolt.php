<?php
/* Opencart Module v2.3.0.2 for PayUmoney BOLT - Copyrighted file - Please do not modify/refactor/disasseble/extract any or all part content  */
// Heading
$_['heading_title']      	= 'PayUmoney BOLT'; 

// Text 
$_['text_payment']       	= 'Extensions';

$_['text_pumbolt']      		= '<a onclick="window.open(\'https://www.payumoney.com\');"><img src="view/image/payment/payulogo.png" alt="PayUmoney" title="PayUmoney" style="border: 1px solid #EEEEEE;" height="25" /></a>';

$_['text_edit'] 			= 'Edit Module Parameters';

//General Settings
$_['entry_module']   		= 'Gateway Mode:';  
$_['entry_module_id'] 		='Sandbox|Production';
$_['entry_geo_zone']     	= 'Geo Zone:';
$_['entry_currency']		= 'Currency';
$_['entry_total']        	= 'Total';
$_['entry_order_status'] 	= 'Success Order Status:';
$_['entry_order_fail_status'] = 'Cancel/Fail Order Status:';
$_['text_disabled']  		= 'Disabled';
$_['text_enabled']  		= 'Enabled';
$_['entry_status']       	= 'Status:';
$_['entry_sort_order']   	= 'Sort Order:';
$_['text_success']       	= 'Success: You have successfully modified Payment settings';
$_['help_total']       		= 'Order total on which this payment option to be available for checkout.';
$_['help_currency']			= 'Three-letter ISO 4217 currency code required. e.g. USD,INR,etc.';
// Entry PayUM
$_['entry_merchant']     	= 'Key:';
$_['entry_salt']         	= 'Salt:';
$_['help_salt']		     	= 'Enter the Salt value provided by PayUmoney.';
$_['help_merchant']		    = 'Enter the Merchant Key provided by PayUmoney.';

// Error
$_['error_permission']   	= 'Warning: You do not have permission to modify payment details!';

//Error PayUM
$_['error_merchant']     	= 'Merchant Key Required!';
$_['error_salt']         	= 'Salt Required!';
//Error Citrus
$_['error_module']   		= 'Invalid Mode';
$_['error_currency']		= 'Currency code required.';
$_['error_status'] 			= 'Either PayUmoney or Citrus Pay parameters must be configured to enable this module.';

/* config keys -
pumbolt_payu_key
pumbolt_payu_salt

pumbolt_module
pumbolt_geo_zone_id
pumbolt_currency
pumbolt_total
pumbolt_order_status_id
pumbolt_order_fail_status_id
pumbolt_status
pumbolt_sort_order
*/

?>