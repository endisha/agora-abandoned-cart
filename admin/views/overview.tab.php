<?php
    wp_enqueue_script ( 'vuejs' );
    wp_enqueue_script ( 'vue-app' );
?>
<div class="overview">
	<div class="overview-alert">
			<img src="<?php echo plugin_dir_url(__DIR__).'/assets/img/logo.png'; ?>" class="logo" />
			<h2><?php echo __('Agora Abandoned Cart', 'agora-abandoned-cart'); ?></h2>
			<?php echo __('Send asynchronous emails and push notifications to your customers, reminding them of their abandoned carts.', 'agora-abandoned-cart'); ?>
	</div>
	<div id="overview">
		<ul class="agora-abandoned-cart has-5-items">
			<li class="agora-abandoned-cart__item-container" v-for="item in list" :key="item.id" v-if="!isLoading" v-bind:class="{hide: isLoading }">
				<div class="agora-abandoned-cart__item">
					<div class="agora-abandoned-cart__item-label">
						<p>{{item.label}}</p>
					</div>
					<div class="agora-abandoned-cart__item-total">
						<div class="agora-abandoned-cart__item-value">
							<p>{{item.total}} <span class="currency">{{item.currency}}</span></p>
						</div>
					</div>
				</div>
			</li>
			<li class="agora-abandoned-cart__item-container" v-for="n in 5" v-if="isLoading" :class="{hide: !isLoading}" v-cloak>
				<div class="agora-abandoned-cart__item">
					<div class="agora-abandoned-cart__item-label">
						<p style="text-align:center;"> <i class="spinner is-active spin"></i> </p>
					</div>
				</div>
			</li>
		</ul>
	</div>
</div>