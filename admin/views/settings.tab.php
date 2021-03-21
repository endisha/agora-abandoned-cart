<?php 
use AgoraAbandonedCart\Classes\Helpers\InputFilterHelper;
use AgoraAbandonedCart\Classes\Helpers\LanguageHelper;

$sendMail = InputFilterHelper::isOption($optionsValues['send_mail']);
$sendPushNotification = InputFilterHelper::isOption($optionsValues['send_push_notifcation']);
$queueInterval = InputFilterHelper::filterEmpty($optionsValues['queue_interval']);
$abandonedCartIntervalTime = InputFilterHelper::filterEmpty($optionsValues['abandoned_cart_interval_time']);
$abandonedCartIntervalUnit = InputFilterHelper::filterEmpty($optionsValues['abandoned_cart_interval_unit']);
$abandonedCartIntervalUnitsList = ['day', 'hour', 'minute', 'second'];
$queueRecordsNumber = InputFilterHelper::filterEmpty($optionsValues['queue_records_number']);
$selectedLanguages = InputFilterHelper::filterEmptyArray($optionsValues['languages']);
$languages = LanguageHelper::load();

?>
<form method="post" action="edit.php?post_type=agora_rest_api&page=abcart&tab=settings">
	<?php if(defined('AGORA_PLUGIN_DIR')){ ?>
	<input type="hidden" name="post_type" value="agora_rest_api">
	<input type="hidden" name="page" value="abcart">
	<?php } ?>
	<input type="hidden" name="tab" value="settings">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><?php echo __('Languages', 'agora-abandoned-cart'); ?></th>
				<td>
					<label>
					<select class="select-submit2" name="languages[]" multiple="multiple" style="height:250px;">
						<?php foreach ($languages as $langKey => $langLabel) { ?>
							<option value="<?php echo $langKey; ?>" 
								<?php if(in_array($langKey, $selectedLanguages)){ echo "selected"; } ?>
								>
								<?php echo $langLabel; ?> [<?php echo $langKey; ?>]
							</option>
						<?php } ?>
					</select>
					<p><?php echo __('Default site language is', 'agora-abandoned-cart'); ?>: <strong><?php echo LanguageHelper::get(get_locale()); ?></strong></p>
					</label>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php echo __('Send e-mail', 'agora-abandoned-cart'); ?></th>
				<td>
					<label><input name="send_mail" type="checkbox" <?php if($sendMail){ echo'checked="checked"'; } ?>></label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo __('Send push notification', 'agora-abandoned-cart'); ?></th>
				<td>
					<label><input name="send_push_notifcation" type="checkbox" <?php if($sendPushNotification){ echo'checked="checked"'; } ?>></label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo __('Abandoned cart interval', 'agora-abandoned-cart'); ?></th>
				<td>
					<label>
					<input name="abandoned_cart_interval_time" type="number"  value="<?php echo $abandonedCartIntervalTime ;?>" class="regular-small">
					<select name="abandoned_cart_interval_unit" class="regular-small">
						<?php foreach ($abandonedCartIntervalUnitsList as $unit) { ?>
							<option 
								value="<?php echo strtoupper($unit); ?>" 
								<?php if(InputFilterHelper::isOption($abandonedCartIntervalUnit, strtoupper($unit))){ echo 'selected'; }?>
							><?php echo ucfirst($unit); ?>(s)</option>
						<?php } ?>
		            </select>
					<p><?php echo __('Consider cart abandoned after X  of item being added to cart & order not placed.', 'agora-abandoned-cart'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo __('Queue interval', 'agora-abandoned-cart'); ?></th>
				<td>
					<label>
					<input name="queue_interval" type="number"  value="<?php echo $queueInterval ;?>" class="regular-small">
					<p><?php echo __('interval (in seconds) after which the task should be repeated', 'agora-abandoned-cart'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo __('Queue records number', 'agora-abandoned-cart'); ?></th>
				<td>
					<label>
					<input name="queue_records_number" type="number"  value="<?php echo $queueRecordsNumber ;?>" class="regular-small">
					<p><?php echo __('The maximum number of records per queue', 'agora-abandoned-cart'); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
	<?php submit_button(); ?>
</form>