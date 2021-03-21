<?php
namespace AgoraAbandonedCart\Classes;

class LocalizationClass{

	public function __construct(){
		add_action('plugins_loaded', [$this, 'load'], 1000);
	}

	/**
	 * Load localization file
	 * 
	 * @return void
	 */
	public function load(){
		load_plugin_textdomain('agora-abandoned-cart', false, basename(dirname(dirname(__FILE__))) . '/languages/');
    }

}
new LocalizationClass;