<?php
namespace AgoraAbandonedCart\Classes;

class ScheduleMangerClass{
	
	use ScheduleTrait, QueueManager, CartManager;

	/**
	 * __construct
	 */
	public function __construct(){
		add_filter( 'cron_schedules', [$this, 'queueSchedules'] );
		$this->getPendingScheduleEvent();
		$this->prepareExecuteScheduleEvent();
		$this->dynamicScheduler();
	}

	/**
	 * Queue schedules
	 * @param  array $schedules
	 * @return array
	 */
	public function queueSchedules( $schedules ) {
		$queueIntervalSeconds = get_option('agora_abcart_helper_queue_interval', 30);
	    $schedules['every_queue_interval_seconds'] = array(
	        'interval'  => $queueIntervalSeconds,
	        'display'   => 'Every '.$queueIntervalSeconds.' Seconds'
	    );

		$acIntervalTime = get_option('agora_abcart_helper_abandoned_cart_interval_time', 12);
		$acIntervalUnit = get_option('agora_abcart_helper_abandoned_cart_interval_unit', 'HOUR');
		$unitString = ucfirst(strtolower($acIntervalUnit));
		$acIntervalTimeSeconds = $this->convertToSeconds($acIntervalUnit, $acIntervalTime);
	    $schedules['every_queue_abandoned_interval_seconds'] = array(
	        'interval'  => $acIntervalTimeSeconds,
	        'display'   => "Every {$acIntervalTime} {$unitString}s"
	    );
	    return $schedules;
	}

	/**
	 * Pending schedule event
	 * @return void
	 */
	public function getPendingScheduleEvent(){
		if ( ! wp_next_scheduled( 'queue_fire_abandoned_event') ) {
			wp_schedule_event(time(), 'every_queue_abandoned_interval_seconds', 'queue_fire_abandoned_event');
		}
		add_action( 'queue_fire_abandoned_event', [$this, 'createAbandonedSchedule'] );
	}

	/**
	 * Prepare execute schedule event
	 * @return void
	 */
	public function prepareExecuteScheduleEvent(){
		if ( ! wp_next_scheduled( 'queue_fire_event') ) {
			wp_schedule_event(time(), 'every_queue_interval_seconds', 'queue_fire_event');
		}
		add_action( 'queue_fire_event', [$this, 'runScheduleEvent'] );
	}

	/**
	 * Run schedule event
	 * @return void
	 */
	public function runScheduleEvent(){
		global $wpdb;

		$failedIds = [];
		$successIds = [];
		$queueItems = [];

		$globalOptionPushNotifcation = get_option('agora_abcart_helper_send_push_notifcation');
		$globalOptionSendMail = get_option('agora_abcart_helper_send_mail');
		$notificationsSchedules = get_option('agora_abcart_helper_notifications', []);
		$queueRecordsNumber = get_option('agora_abcart_helper_queue_records_number', 50);
		$selectedLanguages = get_option('agora_abcart_helper_languages', []);

		if(empty($notificationsSchedules)){
			return;
		}
		if($globalOptionPushNotifcation != 'on' && $globalOptionSendMail != 'on'){
			return;
		}
		if(empty($selectedLanguages)){
			return;
		}
		// Get random pending records from queue
		$pendingQueue = $this->getPendingQueue($queueRecordsNumber);
		if(!empty($pendingQueue)){
			// Set all in process status
			// qid: queue id
			// cid: cart id
			$this->updateQueueStatus( array_column($pendingQueue, 'qid'), 'process');
			foreach ($pendingQueue as $record) {
				$queueItem = [
					'id' 					=> $record->qid,
					'user_id' 				=> $record->user_id,
					'email_notification' 	=> '',
					'push_notification' 	=> ''
				];
				if(array_key_exists($record->counter, $notificationsSchedules)){
					$args = $notificationsSchedules[$record->counter];
					$cart = maybe_unserialize($record->cart);
					if(!empty($cart) && count($cart) > 0){
						$countItems = count($cart);

						// Retrieves the locale of a user.
						$user = get_user_by( 'id', $record->user_id );
						if ($user ) {
							$locale = get_user_locale($user);
						}
						// Customer language is not defined 
						if(!in_array($locale, $selectedLanguages)){
							if(isset($selectedLanguages[0])){
								$locale = $selectedLanguages[0];
							}
						}
						// assign message variables
						$variables = [
							'first_name' => get_user_meta($record->user_id, 'first_name', true ),
							'last_name' => get_user_meta($record->user_id, 'last_name', true ),
							'count_items' => $countItems,
						];

						$data = [];
						$message = $this->prepareMessage($variables, $args['message'][$locale]);
						$mailSubject = $this->prepareMessage($variables, $args['mail_subject'][$locale]);
						$mailMessage = $this->prepareMessage($variables, stripslashes_deep($args['mail_message'][$locale]));

						$data['message'] = $message;
						$data['mail_subject'] = $mailSubject;
						$data['mail_message'] = $mailMessage;
						// Set success ids
						$successIds[] = $record->cid;

						// Notify User email
						if($globalOptionSendMail == 'on'){
							if(!empty($args['mail_message']) && !empty($args['mail_subject'])){
								if($user){
									$headers = ['Content-Type: text/html; charset=UTF-8'];
									//$emailNotificationSent = apply_filters( 'agora_abandoned_cart_notify_user_email', $record, $user, $mailSubject, $mailMessage, $headers);
									$queueItem['email_notification'] = wp_mail($user->user_email, $mailSubject, $mailMessage, $headers);
								}
							}
						}
						// Notify User push notification
						if($globalOptionPushNotifcation == 'on'){
							if(!empty($args['message'])){
								$pushNotificationSent = apply_filters( 'agora_abandoned_cart_notify_user_push_notification', false, $record, $user, $message);
								$queueItem['push_notification'] = $pushNotificationSent;
							}
						}
						$queueItem['status'] = 'success';

					}else{
						$failedIds[] = $record->user_id;
						$queueItem['status'] = 'failed';

					}
				}else{
					$failedIds[] = $record->user_id;
					$queueItem['status'] = 'failed';
				}

				$queueItem['updated_at'] = current_time('mysql');
				$queueItems[] = $queueItem;
			}
		}

		// Update success users abandoned cart to next level
		if(!empty($successIds)){
			$this->updateCartCounter($successIds);
		}
		// Update Queue
		if(!empty($queueItems)){
			$this->updateQueue($queueItems, 'process');
		}

	}

	/**
	 * Dynamic Scheduler
	 * @return void
	 */
	protected function dynamicScheduler(){
		$notificationsSchedules = array_filter(get_option('agora_abcart_helper_notifications', []));
		if(!empty($notificationsSchedules)){
			foreach ($notificationsSchedules as $scheduleKey => $args) {
				$args['counter'] = $scheduleKey;
				$event = $this->queueTimestamp($args['time']);
				$key = $this->buildDynamicCronJobKey($scheduleKey);
				$recurrence = 'daily';
				if ( ! wp_next_scheduled( $key, [$args] ) ) {
					wp_schedule_event($event, $recurrence, $key, [$args] );
				}
				add_action( $key, [$this, 'createSchedule'] );
			}
		}
	}

	/**
	 * Create dynamic schedule and get customer abondoned carts
	 * @param  array $args
	 * @return void
	 */
	public function createSchedule($args) {
		// cancel all non-existent carts
		$this->cancelNonExistentCarts(['pending', 'abondoned']);

		$results = $this->getAbondonedCartRecords($args['counter'], $args['duration']);
		if(!is_null($results) && count($results) > 0){
			$items = [];
			$records = [];
			foreach ($results as $result) {
				$cart = maybe_unserialize($result->cart);
				if(!empty($cart) && count($cart) > 0){
					$records[] = $this->prepareQueueItem($result->key); 
				}
			}
			$this->insertQueueRecords($records);
		}
	}

	/**
	 * Retrieve Customer Cart
	 * @param  array $args
	 * @return void
	 */
	public function createAbandonedSchedule() {
		// cancel all non-existent carts
		$this->cancelNonExistentCarts(['pending', 'abondoned']);

		// abandoned cart after X [time unit] of item being added to cart & order not placed.
		$acIntervalTime = get_option('agora_abcart_helper_abandoned_cart_interval_time', 12); //dafualt 12
		$acIntervalUnit = get_option('agora_abcart_helper_abandoned_cart_interval_unit', 'HOUR');//dafualt HOUR

		$results = $this->getPendingCartsRecords(0, $acIntervalTime, $acIntervalUnit);
		if(!is_null($results) && count($results) > 0){
			$ids = [];
			foreach ($results as $result) {
				$cart = maybe_unserialize($result->cart);
				if(!empty($cart) && count($cart) > 0){
					$ids[] = $result->key; 
				}
			}
			$this->updatePendingCartsRecords($ids);
		}
	}
	
}

new ScheduleMangerClass;