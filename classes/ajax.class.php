<?php 
namespace AgoraAbandonedCart\Classes;

class AjaxClass{

	// Ajax requests methods
	protected $ajaxRequests = [
		'getMonitorQueueData',
		'getMonitorCartsData',
		'getCartDetails',
		'getOverviewDetails'
	];

	/**
	 * __construct
	 */
	public function __construct(){
		$this->loadAjaxRequest();
	}

	/**
	 * Load ajax request
	 * @return void
	 */
	protected function loadAjaxRequest(){
		// Ajax requests
		foreach ($this->ajaxRequests as $ajaxRequest) {
			add_action( "wp_ajax_nopriv_{$ajaxRequest}", [$this, $ajaxRequest]);
			add_action( "wp_ajax_{$ajaxRequest}",        [$this, $ajaxRequest]);
		}
	}

	/**
	 * Get monitor queue data Ajax request
  	 * @return json format
	 */
  	public function getMonitorQueueData(){
  		global $wpdb;

		$nonce = isset($_POST['nonce'])? filter_var($_POST['nonce'], FILTER_SANITIZE_STRING) : 0;
		if (!wp_verify_nonce( $nonce, 'agoraAbandonedCartNonce')){
			die ($nonce);
		}

		$statuses = ['pending', 'process', 'success', 'failed'];
        $perPage = 15;
        $page = isset($_POST['page']) ? filter_var($_POST['page'], FILTER_SANITIZE_NUMBER_INT) : 1;
        $dynamicQuery = [];
        $dynamicQueryParams = [];
        $dynamicQuery['params'] = [];
        
        $dynamicQuery['sql']['count'][] = "
			SELECT count(*)
			FROM `{$wpdb->prefix}agora_abandoned_cart_queue` cq
			LEFT JOIN `{$wpdb->prefix}users` u ON cq.user_id = u.ID
		";					
		$dynamicQuery['sql']['select'][] = "
			SELECT cq.id, cq.user_id, u.user_login as username, cq.email_notification, cq.push_notification, cq.status, cq.created_at, cq.updated_at
			FROM `{$wpdb->prefix}agora_abandoned_cart_queue` cq
			LEFT JOIN `{$wpdb->prefix}users` u ON cq.user_id = u.ID
		";
		if (isset($_POST['filters'])) {
            if (isset($_POST['filters']['status']) && trim($_POST['filters']['status']) != '') {
				$status = filter_var($_POST['filters']['status'], FILTER_SANITIZE_STRING);
                if (in_array($status, $statuses)) {
                    $dynamicQuery['sql']['where'][] = 'cq.status = %s';
                    $dynamicQuery['params'][] = $status;
                }
            }
            if (isset($_POST['filters']['username'])) {
                $username = trim(filter_var($_POST['filters']['username'], FILTER_SANITIZE_STRING));
                if ($username != '') {
					$dynamicQuery['sql']['where'][] = 'u.user_login = %s';
                    $dynamicQuery['params'][] = $username;
                }
            }
        }
		$whereQuery = '';
		if(!empty($dynamicQuery['sql']['where'])){
			foreach($dynamicQuery['sql']['where'] as $c => $whereSql){
				$whereQuery .= $c == 0? ' WHERE ' : ' AND ';
				$whereQuery .= $whereSql;
			}
		}

		// Count
		$countQuery = implode(" ", $dynamicQuery['sql']['count']);
		$countQuery .= $whereQuery;
		$countparams = $dynamicQuery['params'];
		if(!empty($countparams)){
			$countQuery = $wpdb->prepare($countQuery, $countparams);
		}
		$count = $wpdb->get_var($countQuery);
        $pages = ceil($count / $perPage);


		$dynamicQuery['sql']['order'][] = ' ORDER BY id DESC ';
		$dynamicQuery['sql']['limit'][] = ' LIMIT %d ';
		$dynamicQuery['sql']['offset'][] = 'OFFSET %d';
		$dynamicQuery['params'][] = $perPage;
		$dynamicQuery['params'][] = $perPage * ($page - 1);

		
		$query = implode(" ", $dynamicQuery['sql']['select']);
		$query .= $whereQuery;
		$query .= implode(" ", $dynamicQuery['sql']['order']);
		$query .= implode(" ", $dynamicQuery['sql']['limit']);
		$query .= implode(" ", $dynamicQuery['sql']['offset']);
		$params = $dynamicQuery['params'];


		$queueRecords = $wpdb->get_results(
			$wpdb->prepare($query, $params)
		);

		return wp_send_json_success(['success' => true, 'list' => $queueRecords, 'count' => $count, 'current' => $page, 'pages' => $pages]);
  	}


  	/**
  	 * Get customer monitor carts data Ajax request
  	 * @return json format
  	 */
  	public function getMonitorCartsData(){
  		global $wpdb;

		$nonce = isset($_POST['nonce'])? filter_var($_POST['nonce'], FILTER_SANITIZE_STRING) : 0;
		if (!wp_verify_nonce( $nonce, 'agoraAbandonedCartNonce')){
			die ($nonce);
		}

		$statuses = ['pending', 'canceled', 'recoverd', 'abondoned','completed'];
        $perPage = 15;
        $page = isset($_POST['page']) ? filter_var($_POST['page'], FILTER_SANITIZE_NUMBER_INT) : 1;
        $dynamicQuery = [];
        $dynamicQueryParams = [];
        $dynamicQuery['params'] = [];

        $dynamicQuery['sql']['count'][] = "
			SELECT count(*)
			FROM `{$wpdb->prefix}agora_abandoned_cart` c
			LEFT JOIN `{$wpdb->prefix}users` u ON c.key = u.ID
		";					
		$dynamicQuery['sql']['select'][] = "
			SELECT c.id, c.key, u.user_login as username, c.status, c.counter, c.created_at, c.refreshed_at
			FROM `{$wpdb->prefix}agora_abandoned_cart` c
			LEFT JOIN `{$wpdb->prefix}users` u ON c.key = u.ID
		";
		if (isset($_POST['filters'])) {
            if (isset($_POST['filters']['status']) && trim($_POST['filters']['status']) != '') {
                $status = filter_var($_POST['filters']['status'], FILTER_SANITIZE_STRING);
                if (in_array($status, $statuses)) {
                    $dynamicQuery['sql']['where'][] = 'c.status = %s';
                    $dynamicQuery['params'][] = $status;
                }
            }
            if (isset($_POST['filters']['username'])) {
                $username = trim(filter_var($_POST['filters']['username'], FILTER_SANITIZE_STRING));
                if ($username != '') {
					$dynamicQuery['sql']['where'][] = 'u.user_login = %s';
                    $dynamicQuery['params'][] = $username;
                }
            }
        }
		$whereQuery = '';
		if(!empty($dynamicQuery['sql']['where'])){
			foreach($dynamicQuery['sql']['where'] as $c => $whereSql){
				$whereQuery .= $c == 0? ' WHERE ' : ' AND ';
				$whereQuery .= $whereSql;
			}
		}

		// Count
		$countQuery = implode(" ", $dynamicQuery['sql']['count']);
		$countQuery .= $whereQuery;
		$countparams = $dynamicQuery['params'];
		if(!empty($countparams)){
			$countQuery = $wpdb->prepare($countQuery, $countparams);
		}
		$count = $wpdb->get_var($countQuery);
        $pages = ceil($count / $perPage);


		$dynamicQuery['sql']['order'][] = ' ORDER BY id DESC ';
		$dynamicQuery['sql']['limit'][] = ' LIMIT %d ';
		$dynamicQuery['sql']['offset'][] = 'OFFSET %d';
		$dynamicQuery['params'][] = $perPage;
		$dynamicQuery['params'][] = $perPage * ($page - 1);

		
		$query = implode(" ", $dynamicQuery['sql']['select']);
		$query .= $whereQuery;
		$query .= implode(" ", $dynamicQuery['sql']['order']);
		$query .= implode(" ", $dynamicQuery['sql']['limit']);
		$query .= implode(" ", $dynamicQuery['sql']['offset']);
		$params = $dynamicQuery['params'];

		$queueRecords = $wpdb->get_results(
			$wpdb->prepare($query, $params)
		);

		return wp_send_json_success(['success' => true, 'list' => $queueRecords, 'count' => $count, 'current' => $page, 'pages' => $pages]);
  	}



  	/**
  	 * Get customer monitor carts data Ajax request
  	 * @return json format
  	 */
  	public function getCartDetails(){
  		global $wpdb;

        $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT) : 0;
		$nonce = isset($_POST['nonce'])? filter_var($_POST['nonce'], FILTER_SANITIZE_STRING) : 0;
		if (!wp_verify_nonce( $nonce, 'agoraAbandonedCartNonce')){
			die ();
		}

		$cart = $wpdb->get_row(
			$wpdb->prepare("
				SELECT cart, total_cart
				FROM `{$wpdb->prefix}agora_abandoned_cart`
				WHERE `id` = %d
			", $id)
		);
		$products = [];
		$total = 0;
		$currency = get_option('woocommerce_currency');

		if(!empty($cart)){
			$total = $cart->total_cart;
			$cart = maybe_unserialize($cart->cart);
			if(!empty($cart) && is_array($cart)){
				foreach ($cart  as $key => $product) {
					$productId = $product['variation_id'] > 0? $product['variation_id'] : $product['product_id'];
					//$productInfo = wc_get_product($productId);
					$productInfo = $product['data'];
					$image = wc_placeholder_img_src('woocommerce_thumbnail');
					if($thumbnail = $productInfo->get_image_id()){
						$image = wp_get_attachment_image_url($thumbnail, 'woocommerce_thumbnail');
					}

					$products[] = [
						'name' => $productInfo->get_name(),
						'image' => $image,
						'quantity' => $product['quantity'],
						'price' => (float) $productInfo->get_price(),
						'subtotal' => (float) $productInfo->get_price() * $product['quantity'],
					];

				}
			}
		}

		return wp_send_json_success(['success' => true, 'products' => $products, 'total' => $total, 'currency' => $currency]);
  	}


  	/**
  	 * Get customer monitor carts data Ajax request
  	 * @return json format
  	 */
  	public function getOverviewDetails(){
  		global $wpdb;

		$nonce = isset($_POST['nonce'])? filter_var($_POST['nonce'], FILTER_SANITIZE_STRING) : 0;
		if (!wp_verify_nonce( $nonce, 'agoraAbandonedCartNonce')){
			die ();
		}

		$totalsOverview = $wpdb->get_results("
				SELECT 
				status, 
				sum(`total_cart`) as total
				FROM `{$wpdb->prefix}agora_abandoned_cart`
				GROUP BY `status`
			");

		$values = array_column($totalsOverview, 'total', 'status');

		$overviewStatus = [
			'recoverd' => __('Recoverd', 'agora-abandoned-cart'),
			'canceled' => __('Canceled', 'agora-abandoned-cart'),
			'abondoned' => __('Abondoned', 'agora-abandoned-cart'),
			'pending' => __('Pending', 'agora-abandoned-cart'),
			'completed' => __('Completed', 'agora-abandoned-cart'),
		];
		$widgeyOverview = [];
		foreach ($overviewStatus as $status => $label) {
			$widgeyOverview[] = [
				'key' => $status,
				'label' => $label,
				'total' => isset($values[$status])? $values[$status] : 0,
				'currency' => get_option('woocommerce_currency'),
			];
		}

		return wp_send_json_success(['success' => true, 'overview_widget' => $widgeyOverview]);
  	}

}
new AjaxClass;