<?php 
namespace AgoraAbandonedCart\Classes;

trait ScheduleTrait{
	
	public $cronJobKey = 'agora_abandoned_cart_cron_hook';

	/**
	 * Convert to seconds
	 * @param  integer $time
	 * @param  string $unit
	 * @return integer
	 */
	public function convertToSeconds($time, $unit){
		$unit = strtolower($unit);
		switch ($unit) {
			case 'minute':
				$value = $time * 60;
				break;
			case 'hour':
				$value = $time * 60 * 60;
				break;
			case 'day':
				$value = $time * 60 * 60 * 24;
				break;
			default:
				$value = $time;
				break;
		}
		return $value;
	}

	/**
	 * Build key
	 * @param  string $key
	 * @return string
	 */
	public function buildDynamicCronJobKey($key, $period=null){
		if(is_null($period)){
			return $this->cronJobKey.'_' . $key;
		}
		return $this->cronJobKey.'_' . $key . '_' . $period;
	}

	/**
	 * Prepare message
	 * @param  array $variables
	 * @param  string $message
	 * @return string
	 */
	public function prepareMessage($variables, $message){
		return preg_replace_callback('/\\{\\{([^{}]+)\}\\}/i',
		            function($matches) use ($variables){
		                return array_key_exists($matches[1], $variables)? $variables[$matches[1]] : $matches[1];
		            }, $message);
	}


}