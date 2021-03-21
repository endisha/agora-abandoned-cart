<?php 
namespace AgoraAbandonedCart\Classes;

trait CartManager{

	/**
	 * Customer has cart
	 * @param  int $customerId
	 * @return string|null Database query result (as string), or null on failure.
	 */
	public function customerHasCart($customerId, $statuses=['pending', 'abondoned']){
		global $wpdb;
		$inlineStatuses = "'" . implode( "','", $statuses) . "'";
		return $wpdb->get_var( 
			$wpdb->prepare( "
				SELECT count(*) 
				FROM `{$wpdb->prefix}agora_abandoned_cart` 
				WHERE `key` = %s
				AND `status` IN ({$inlineStatuses})
				ORDER BY id DESC
				LIMIT 1
			", $customerId)
		);
	}

	/**
	 * Insert customer cart
	 * @param  int $customerId
	 * @param  object $cart
	 * @return int|bool
	 */
	public function insertCustomerCart($customerId, $cart, $total){
		global $wpdb;
		return $wpdb->query(
			$wpdb->prepare("
				INSERT INTO `{$wpdb->prefix}agora_abandoned_cart` (`id`, `key`, `cart`, `total_cart`, `counter`, `status`, `created_at`, `refreshed_at`) VALUES (%s, %s, %s, %f, %s, %s, %s, %s)",
				NULL,
				$customerId,
				maybe_serialize($cart),
				$total,
				0,
				'pending',
				current_time('mysql'),
				current_time('mysql')
			)
		);
	}

	/**
	 * Update customer cart
	 * @param  int $customerId
	 * @param  object $cart
	 * @return int|bool
	 */
	public function updateCustomerCart($customerId, $cart, $total){
		global $wpdb;
		return $wpdb->query(
			$wpdb->prepare("
				UPDATE `{$wpdb->prefix}agora_abandoned_cart` 
				SET `cart` = %s , `total_cart` = %f , `refreshed_at` = %s  
				WHERE `key` = %s
				AND `status` IN ('pending', 'abondoned')
				ORDER BY `id` DESC
				LIMIT 1
				",
				maybe_serialize($cart),
				$total,
				current_time('mysql'),
				$customerId
			)
		);
	}

	/**
	 * Cancel abandoned carts for customer
	 * @param  int $customerId
	 * @return int|bool
	 */
	protected function cancelAbandonedCart($customerId){
		global $wpdb;
		return $wpdb->query(
			$wpdb->prepare("
				UPDATE `{$wpdb->prefix}agora_abandoned_cart` 
				SET `status` = 'canceled' 
				WHERE `key` = %s
				AND `status` IN ('pending', 'abondoned')
			", $customerId)
		);
	}

	/**
	 * Cancel abandoned carts for customer
	 * @param  int $customerId
	 * @return int|bool
	 */
	protected function recoverdAbandonedCart($customerId){
		global $wpdb;
		return $wpdb->query(
			$wpdb->prepare("
				UPDATE `{$wpdb->prefix}agora_abandoned_cart` 
				SET `status` = IF(`status` = 'abondoned', 'recoverd', 'completed')
				WHERE `key` = %s
				ORDER BY `id` DESC
				LIMIT 1
			", $customerId)
		);
	}

	/**
	 * Cancel all non-existent carts
	 * @param  array $statuses
	 * @return int|bool
	 */
	protected function cancelNonExistentCarts($statuses=['pending', 'abondoned']){
		global $wpdb;
		if(!empty($statuses)){
			$inlineStatuses = "'" . implode( "','", $statuses) . "'";
			return $wpdb->query("
				UPDATE `{$wpdb->prefix}agora_abandoned_cart` `c`
				LEFT JOIN `{$wpdb->prefix}woocommerce_sessions` `ws` ON `ws`.`session_key` = `c`.`key`
				SET `c`.`status` = 'canceled' 
				WHERE `c`.`status` IN ({$inlineStatuses})
				AND `c`.`key` IS NULL
			");
		}
	}

	/**
	 * Get cart abondoned records
	 * @param  int $counter
	 * @param  int $duration
	 * @param  string $interval
	 * @param  string $status 
	 * @return array|object|null Database query results.
	 */
	public function getAbondonedCartRecords($counter, $duration, $interval='HOUR', $status='abondoned'){
		global $wpdb;
		return $wpdb->get_results( 
			$wpdb->prepare( "
				SELECT * FROM `{$wpdb->prefix}agora_abandoned_cart`
				WHERE `counter` = %s
				AND `status` = %s
				AND `refreshed_at` <= DATE_SUB(NOW(),INTERVAL %d {$interval}); 
			", $counter, $status, $duration)
		);
	}

	/**
	 * Update pending carts records
	 * @param  array $ids
	 * @param  string $status
	 * @param  string $statusTo
	 * @return int|bool
	 */
	private function updatePendingCartsRecords($ids, $status='pending', $statusTo='abondoned'){
		global $wpdb;
		if(!empty($ids)){
			 return $wpdb->query(
				$wpdb->prepare("
						UPDATE `{$wpdb->prefix}agora_abandoned_cart` 
						SET `status` = %s 
						WHERE `status` = %s 
						AND `key` IN (".implode(', ', $ids).")
						", $statusTo, $status
					)
			);
		}
	}

	/**
	 * Update cart counter
	 * @param  array $ids
	 * @return int|bool
	 */
	private function updateCartCounter($ids){
		global $wpdb;
		if(!empty($ids)){
			return $wpdb->query("
					UPDATE `{$wpdb->prefix}agora_abandoned_cart` 
					SET `counter` = counter+1  
					WHERE `status` = 'abondoned'
					AND `id` IN (".implode(', ', $ids).")
					"
			);
		}
	}

}