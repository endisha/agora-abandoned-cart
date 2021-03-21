<?php
use AgoraAbandonedCart\Classes\Helpers\AdminPageHelper;

wp_enqueue_script ( 'vuejs' );
wp_enqueue_script ( 'vue-pagination' );
wp_enqueue_script ( 'vue-customer-carts-monitor' );

?>
<form method="post" action="<?php echo AdminPageHelper::pluginTabUrl('general'); ?>">
	<?php if(defined('AGORA_PLUGIN_DIR')){ ?>
	<input type="hidden" name="post_type" value="agora_rest_api">
	<input type="hidden" name="page" value="abcart">
	<?php } ?>
	<input type="hidden" name="tab" value="general">
	<table class="form-table">
		<tbody>
			<?php foreach ($fields as $id => $values) { 
				if(isset($values['hide']) && $values['hide']){
					continue;
				}
				?>
			<tr>
				<th scope="row"><label for="<?php echo $id; ?>"><?php echo $values['label']; ?></label></th>
				<td><input name="<?php echo $id; ?>" type="<?php echo $values['type']; ?>" id="<?php echo $id; ?>" value="<?php echo $optionsValues[$id] ;?>" class="regular-text">
					<p><?php echo __('number of notifications that will be sent to customers', 'agora-abandoned-cart'); ?></p>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php submit_button(); ?>
</form>