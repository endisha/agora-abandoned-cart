<?php 
namespace AgoraAbandonedCart\Classes\Helpers;

class LanguageHelper{

	public static function load(){
		if ( function_exists('icl_object_id') ) {
		    $languages = apply_filters( 'wpml_active_languages', NULL );
		    $languages = array_column($languages, 'translated_name', 'language_code');
		}else{
			require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
			$languages = wp_get_available_translations();
			$languages = array_column($languages, 'native_name', 'language');
		}
		$languages = array_merge(['en_US' => 'English (United States)'], $languages);
		return $languages;
	}

	public static function get($key=''){
		$languages = self::load();
		if(!empty($key) && array_key_exists($key, $languages)){
			return $languages[$key];
		}
		return $key;
	}

	public static function getSelectedLanguags($languages = []){
		if(isset($languages) && !empty($languages)){
			return $languages;
		}
		return [];
	}

}

