<?php 
namespace AgoraAbandonedCart\Classes;

use AgoraAbandonedCart\Classes\Helpers\AdminPageHelper;
use AgoraAbandonedCart\Classes\Helpers\InputFilterHelper;

class DashboardClass{

	use ScheduleTrait;

	/**
	 * __construct
	 */
	public function __construct(){
		add_action('admin_menu', [$this, 'menu'], 11);
		add_action( 'admin_enqueue_scripts', [$this, 'assets']);	
	}

	/**
	 * Plugin assets
	 * @param  string $hookSuffix
	 * @return void
	 */
	function assets($hookSuffix) {
	    if(!in_array($hookSuffix, ['toplevel_page_abcart', 'agora_rest_api_page_abcart'])) {
	        return;
	    }

	    // Load css.
	    wp_register_style( 'agora-abandoned-cart', plugin_dir_url(__DIR__) . 'admin/assets/css/style.css', false, '1.0.0' );
	    wp_enqueue_style( 'agora-abandoned-cart' );
	    wp_register_style( 'jquery-ui-css', plugin_dir_url(__DIR__) . 'admin/assets/css/jquery-ui.css', false, '1.0.0' );


		// Load scripts
		wp_enqueue_script( 'jquery-ui-core');
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script('jquery-ui-dialog');

	    // Script handle the data will be attached to vuejs localized script.
	    $params = array(
		  'url' => admin_url('admin-ajax.php'),
		  'nonce' => wp_create_nonce('agoraAbandonedCartNonce'),
		);

		// Load vuejs
		wp_register_script ( 'vuejs', plugin_dir_url(__DIR__).'/admin/assets/js/vue.js' );
		wp_register_script ( 'vue-pagination', plugin_dir_url(__DIR__).'/admin/assets/js/pagination.js' );
		wp_register_script ( 'vue-app', plugin_dir_url(__DIR__).'/admin/assets/js/app.js' );
		wp_register_script ( 'vue-customer-carts-monitor', plugin_dir_url(__DIR__).'/admin/assets/js/customer-carts-monitor.js' );
		wp_register_script ( 'vue-queue-monitor', plugin_dir_url(__DIR__).'/admin/assets/js/queue-monitor.js' );
		wp_register_script ( 'js-accordion', plugin_dir_url(__DIR__).'/admin/assets/js/accordion.js' );

		// Localizes a script, only if the script has already been added
		wp_localize_script ( 'vuejs', 'agoraAbandonedCart', $params );

	}

	/**
	 * Create page
	 * @return void
	 */
	public function menu() {
		$title = 'Abandoned Cart';
		$slug = 'abcart';
		if(defined('AGORA_PLUGIN_DIR')){
	    	add_submenu_page( 'edit.php?post_type=agora_rest_api', $title, $title, 'manage_options', $slug, [$this, 'page'] ,10);
		}else{
			$sidebarItems = [
				'overview' => __('Overview', 'agora-abandoned-cart'), 
				'general' => __('General', 'agora-abandoned-cart'), 
				'settings' => __('Settings', 'agora-abandoned-cart'), 
				'scheduled-notifications' => __('Scheduled Notifications', 'agora-abandoned-cart'), 
				'customer-carts-monitor' => __('Customer carts', 'agora-abandoned-cart'), 
				'queue-monitor' => __('Queue monitor', 'agora-abandoned-cart'), 
			];
			add_menu_page($title, $title, 'manage_options', $slug, [$this, 'page'], 'dashicons-cart', 50);
			foreach ($sidebarItems as $itemKey => $label) {
				$url = admin_url('admin.php?page=abcart&tab='.$itemKey);
				add_submenu_page($slug, $label, $label, 'manage_options', $url);
			}
		}
	}

	/**
	 * Admin pages
	 * @return void
	 */
	public function page() {
		global $wpdb;

		$fields = [
				'notifications_number' => [
					'tab' => 'general', 
					'label' => __('Notifications number', 'agora-abandoned-cart'), 
					'type' => 'number'
				],
				'notifications' => [
					'tab' => 'scheduled-notifications', 
					'hide' => true
				],
				'clear' => [
					'tab' => 'clear', 
					'hide' => true
				],
				'languages' => [
					'tab' => 'settings', 
					'hide' => true
				],
				'send_mail' => [
					'tab' => 'settings', 
					'hide' => true
				],
				'email_subject' => [
					'tab' => 'settings', 
					'hide' => true
				],
				'send_push_notifcation' => [
					'tab' => 'settings', 
					'hide' => true
				],
				'queue_interval' => [
					'tab' => 'settings', 
					'hide' => true
				],
				'queue_records_number' => [
					'tab' => 'settings', 
					'hide' => true
				],
				'abandoned_cart_interval_time' => [
					'tab' => 'settings', 
					'hide' => true
				],
				'abandoned_cart_interval_unit' => [
					'tab' => 'settings', 
					'hide' => true
				],
			];

		// Save actions
		if(isset($_REQUEST['submit']) && $_REQUEST['page'] == 'abcart'){
			$tab = isset($_REQUEST['tab'])? filter_var($_REQUEST['tab'], FILTER_SANITIZE_STRING) : '';

			// Clear old scheduled events notifications
			$oldNotifications = array_filter(get_option('agora_abcart_helper_notifications', []));
			if(!empty($oldNotifications)){
				foreach ($oldNotifications as $scheduleKey => $args) {
					$args['counter'] = $scheduleKey;
					$key = $this->buildDynamicCronJobKey($scheduleKey);
					wp_clear_scheduled_hook($key, [$args]);
				}
			}

			// Remove other scheduled events
			foreach (['queue_fire_abandoned_event', 'queue_fire_event'] as $queueFireEventKey) {
				wp_clear_scheduled_hook($queueFireEventKey);
			}

			// Save fields
			foreach ($fields as $field => $val) {
				if($val['tab'] == $tab){
					$key = 'agora_abcart_helper_'.$field;
					$value = '';
					if(isset($_REQUEST[$field])){
						$value = InputFilterHelper::sanitizeString($_REQUEST[$field]);
					}
					// general tab
					if($tab == 'general'){
						$newNotifications = [];
						$notificationsCount = filter_var($_REQUEST['notifications_number'], FILTER_SANITIZE_NUMBER_INT);
						for ($i=0; $i < $notificationsCount; $i++) { 
							$newNotifications[$i] = $oldNotifications[$i];
						}
	                    update_option('agora_abcart_helper_notifications', $newNotifications);
					}
	                if(!empty($value)) {
	                    update_option($key, $value);
	                }else {
	                    delete_option($key);
	                }
				}
            }
            $redirectUrl = AdminPageHelper::pluginTabUrl($tab);
			header("Location: {$redirectUrl}&saved=true");
            die;
		}
		$optionsValues = [];
		foreach ($fields as $field => $val) {
			$optionsValues[$field] = get_option('agora_abcart_helper_'.$field);
		}
		include __DIR__ . '/../admin/views/page.php';
	}

}
new DashboardClass;