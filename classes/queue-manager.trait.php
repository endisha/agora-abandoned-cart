<?php 
namespace AgoraAbandonedCart\Classes;

use DateTime;

trait QueueManager{

	/**
	 * Queue timestamp
	 * @param  string $time
	 * @return double
	 */
	private function queueTimestamp($time){
		$date = new DateTime($time, wp_timezone());
		return $date->format('U');
	}

	/**
	 * Insert queue records [description]
	 * @return void
	 */
	private function insertQueueRecords($records){
		global $wpdb;
	    foreach( $records as $record ){
	        $wpdb->insert($wpdb->prefix . "agora_abandoned_cart_queue", $record);  
	    }
	}

	/**
	 * Get pending carts records
	 * @param  int $counter
	 * @param  int $duration
	 * @param  string $interval
	 * @return array|object|null Database query results.
	 */
	private function getPendingCartsRecords($counter, $duration, $interval='HOUR'){
		global $wpdb;
		return $wpdb->get_results( 
			$wpdb->prepare( "
				SELECT * FROM `{$wpdb->prefix}agora_abandoned_cart`
				WHERE `counter` = %s
				AND `status` = %s
				AND `refreshed_at` <= DATE_SUB(NOW(),INTERVAL %d {$interval}); 
			", $counter, 'pending', $duration)
		);
	}

	/**
	 * Prepare queue item 
	 * @param  int $key
	 * @param  string $status
	 * @return array
	 */
	private function prepareQueueItem($key, $status='pending'){
		return [
			'id' => null, 
			'user_id' => $key, 
			'email_notification' => null,
			'push_notification' => null,
			'status' => $status, 
			'created_at' => current_time('mysql'),
			'updated_at' => current_time('mysql')
		];
	}

	/**
	 * Update queue 
	 * @param  array $queueItems
	 * @param  string $status 
	 * @return void
	 */
	private function updateQueue($queueItems, $status='process'){
		global $wpdb;
		if(!empty($queueItems)){
			foreach ($queueItems as $queueItem) {
				$where = ['id' => $queueItem['id'], 'status' => $status];
				$data = $queueItem;
				unset($data['user_id']);
				$wpdb->update("{$wpdb->prefix}agora_abandoned_cart_queue", $data, $where);
			}
		}
	}

	/**
	 * Update queue status
	 * @param  array $ids
	 * @param  string $status
	 * @param  string $whereStatus
	 * @return void
	 */
	private function updateQueueStatus($ids, $status='process', $whereStatus='pending'){
		global $wpdb;
		if(!empty($ids)){
			 $wpdb->query(
				$wpdb->prepare("
						UPDATE `{$wpdb->prefix}agora_abandoned_cart_queue` 
						SET `status` = %s 
						WHERE `status` = %s 
						AND `id` IN (".implode(', ', $ids).")
						", $status, $whereStatus
					)
			);
		}
	}

	/**
	 * Get pending queue
	 * @param  integer $number
	 * @return array|object|null Database query results.
	 */
	private function getPendingQueue($number=50){
		global $wpdb;
		return $wpdb->get_results(
					$wpdb->prepare("
						SELECT `c`.`id` as `cid`, `cq`.`id` as `qid`, `cq`.`user_id`, `c`.`counter`, `c`.`cart`
						FROM `{$wpdb->prefix}agora_abandoned_cart_queue` `cq`
						LEFT JOIN `{$wpdb->prefix}agora_abandoned_cart` `c` on `c`.`key` = `cq`.`user_id`
						WHERE `c`.`status` = %s
						AND `cq`.`status` = %s
						ORDER BY Rand()
						LIMIT %d
					", 'abondoned', 'pending', $number)
			);
	}

}