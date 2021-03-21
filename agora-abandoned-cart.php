<?php
/**
 * Plugin Name: Agora Abandoned Cart
 * Plugin URI: https://endisha.ly
 * Description: Send asynchronous emails and push notifications to your logged-in woocommerce customers, reminding them of their abandoned carts.
 * Author: Mohamed Endisha
 * Version: 1.0.0
 */

defined( 'ABSPATH' )or exit;

function agoraAbandonedCartSimpleLoader(){
	$files = [
		'migration-db.class', 
		'Helpers/language.helper', 
		'Helpers/input.filter.helper', 
		'Helpers/admin.page.helper', 
		'queue-manager.trait',
		'cart-manager.trait',
		'schedule.trait',
		'schedule-manger.class',
		'dashboard.class',
		'ajax.class',
		'hooks.class',
		'localization.class',
	];
	foreach ($files as $file) {
		$path = __DIR__ . '/classes/' . $file . '.php';
		if(file_exists($path)){
			include $path;
		}
	}
}
agoraAbandonedCartSimpleLoader();

