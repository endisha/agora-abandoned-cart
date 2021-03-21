<?php 
namespace AgoraAbandonedCart\Classes;

class HooksClass{

	use QueueManager, CartManager;
	/**
	 * __construct
	 */
	public function __construct(){

		add_action( 'woocommerce_add_to_cart', [$this, 'addToCartHook'],10, 6);
		add_action( 'woocommerce_cart_item_removed', [$this, 'removeProductFromCartHook'],10, 2 );
		add_action( 'woocommerce_cart_emptied', [$this, 'clearCartHook'] );
		add_action( 'woocommerce_checkout_order_created', [$this, 'orderCreatedHook'] );
		add_action( 'woocommerce_cart_item_set_quantity', [$this, 'updateCartItemsHook'], 20, 4 );

	}

	/**
	 * Add product to cart
	 * @param string $cart_item_key
	 * @param int $product_id
	 * @param int $quantity
	 * @param int $variation_id
	 * @param object $variation
	 * @param string $cart_item_data
	 * @return void
	 */
	public function addToCartHook( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) { 

		// Allow registered customers only
		if( !is_user_logged_in() ){
			return false;
		}

		$customerId = get_current_user_id();
		$cart = WC()->cart->get_cart();
		WC()->cart->calculate_totals();
		$total = WC()->cart->total;

		$count = $this->customerHasCart($customerId);
		if($count > 0){
			return $this->updateCustomerCart($customerId, $cart, $total);
		}else{
			return $this->insertCustomerCart($customerId, $cart, $total);
		}
	}
	
	/**
	 * Update cart items
	 * @param string $cart_item_key
	 * @param int $quantity
	 * @param object $cart
	 * @return void
	 */
	public function updateCartItemsHook( $cart_item_key, $quantity, $cart ) { 

		// Allow registered customers only
		if( !is_user_logged_in() ){
			return false;
		}

		$customerId = get_current_user_id();
		$cart = WC()->cart->get_cart();
		WC()->cart->calculate_totals();
		$total = WC()->cart->total;

		$count = $this->customerHasCart($customerId);
		if($count > 0){
			return $this->updateCustomerCart($customerId, $cart, $total);
		}else{
			return $this->insertCustomerCart($customerId, $cart, $total);
		}
	}

	/**
	 * Remove product from cart
	 * @param  string $cart_item_key
	 * @param  object $cart
	 * @return void
	 */
	public function removeProductFromCartHook( $cart_item_key, $cart ) { 

		// Allow registered customers only
		if( !is_user_logged_in() ){
			return false;
		}

		$customerId = get_current_user_id();
		$cart = WC()->cart->get_cart();
		WC()->cart->calculate_totals();
		$total = WC()->cart->total;

		if(WC()->cart->is_empty()){
			return $this->cancelAbandonedCart($customerId);
		}else{
			$count = $this->customerHasCart($customerId);
			if($count > 0){
				return $this->updateCustomerCart($customerId, $cart, $total);
			}
		}
	}

	/**
	 * Clear cart action
	 * @param  boolean $clear_persistent_cart
	 * @return void
	 */
	public function clearCartHook($clear_persistent_cart){

		// Allow registered customers only
		if( !is_user_logged_in() ){
			return false;
		}

		$customerId = get_current_user_id();
		return $this->cancelAbandonedCart($customerId);
	}

	/**
	 * Order created action
	 * @param  object $order
	 * @return void
	 */
	public function orderCreatedHook($order){

		// Allow registered customers only
		if( !is_user_logged_in() ){
			return false;
		}

		$customerId = get_current_user_id();
		return $this->recoverdAbandonedCart($customerId);
	}

}
new HooksClass;