<?php 
namespace AgoraAbandonedCart\Classes\Helpers;

class AdminPageHelper{

	public static function navKeys($nav, $tab, $default){
		return isset($tab) && !empty($tab) && in_array($tab, array_keys($nav))? $tab : $default;
	}

	public static function buildTabs($nav, $tab, $dir, $default){
		$page = self::navKeys($nav, $tab,$default);
		return $dir . '/'.$page.'.tab.php';
	}

	public static function buildNavs($nav, $tab, $navUrl){
		$navs = [];
		foreach ($nav as $tabId => $tabValue) { 
			if(isset($tabValue['hidden']) && $tabValue['hidden']) continue;
			$navs[] = [
				'href' => self::pluginTabUrl($tabId),
				'class' => $tabValue['class'] . ($tabId == $tab? ' nav-tab-active' : ''),
				'label' => $tabValue['label'],
			];
		}
		return $navs;
	}

	public static function pluginTabUrl($tab=''){
		if(defined('AGORA_PLUGIN_DIR')){
			$url = 'edit.php?post_type=agora_rest_api&page=abcart';
		}else{
			$url = 'admin.php?page=abcart';
		}
		if(!empty($tab)){
			$url .= '&tab=' . $tab;
		}
		return $url;
	}

	public static function RTLStyle($lang){
		return "direction: " . ($lang == 'ar'? 'rtl' : 'ltr') . "; text-align: " . ($lang == 'ar'? 'right' : 'left');
	}

	public static function isRTLStyle($lang){
		return $lang == 'ar';
	}

}