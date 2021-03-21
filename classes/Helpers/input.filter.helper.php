<?php 
namespace AgoraAbandonedCart\Classes\Helpers;

class InputFilterHelper{

	public static function isOption($key, $default='on'){
		return isset($key) && $key == $default;
	}
	public static function isOptionNot($key, $default='on'){
		return isset($key) && $key != $default;
	}
	public static function isHigherThan($value, $secondValue){
		return isset($value) && $value > $secondValue;
	}
	public static function filterTrim($key){
		return isset($key) && !empty($key)? trim($key) : '';
	}
	public static function filterEmpty($key, $default=''){
		return isset($key) && !empty($key)? $key : '';
	}
	public static function filterEmptyArray($key){
		return isset($key) && !empty($key)? $key : [];
	}

	public static function sanitizeString($data){
		if(!empty($data)){
			if(is_array($data)){
				foreach ($data as $key => $value) {
					$data[$key] = self::sanitizeString($value);
				}
			}else{
				$data = filter_var($data, FILTER_SANITIZE_STRING);
			}
		}
		return $data;
	}

}

