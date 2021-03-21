<?php
use AgoraAbandonedCart\Classes\Helpers\AdminPageHelper;
use AgoraAbandonedCart\Classes\Helpers\InputFilterHelper;
use AgoraAbandonedCart\Classes\Helpers\LanguageHelper;

wp_enqueue_style( 'jquery-ui-css' );
wp_enqueue_script('jquery-ui');
wp_enqueue_script ( 'js-accordion' );

$customVariables = [
	'{{first_name}}' => 'First name',
	'{{last_name}}' => 'Last name',
	'{{count_items}}' => 'Count items',
];

$languages = LanguageHelper::getSelectedLanguags($optionsValues['languages']);
$notifications = $optionsValues['notifications'];
$notificationsNumber = $optionsValues['notifications_number'];
$countLangs = count($languages);
$gridClass = "grids-{$countLangs}";

?>

<form method="post" action="edit.php?post_type=agora_rest_api&page=abcart&tab=scheduled-notifications">
	<?php if(defined('AGORA_PLUGIN_DIR')){ ?>
	<input type="hidden" name="post_type" value="agora_rest_api">
	<input type="hidden" name="page" value="abcart">
	<?php } ?>
	<input type="hidden" name="tab" value="scheduled-notifications">

	<div id="accordion">
			<?php if(InputFilterHelper::isHigherThan($notificationsNumber, 0)){ ?>
						<?php 
							for ($i=0; $i < $notificationsNumber; $i++) { 

								$keyCounter = $i+1;
								$notificationLabel = "Notification #{$keyCounter}";

								$time = isset($notifications[$i]['time'])? InputFilterHelper::filterEmpty($notifications[$i]['time']) : '';
								$timeKey = "date_{$i}";
								$timeFieldName = "notifications[{$i}][time]";
								$duration = isset($notifications[$i]['duration'])? InputFilterHelper::filterTrim($notifications[$i]['duration']) : '';
								$durationKey = "duration_{$i}";
								$durationFieldName = "notifications[{$i}][duration]";
							?>
								<h3>
									<?php echo $notificationLabel;?> 
									<?php if(!empty($time)){ ?>  <br />
									<span class="dashicons dashicons-clock"></span> <?php echo __('every day at', 'agora-abandoned-cart'); ?>: <?php echo $time; ?>
									<?php } ?>
								</h3>

							<div>
								<div class="section">
									<p class="notifications-note"><?php echo __('All fields support custom variables', 'agora-abandoned-cart'); ?></p>

									<div class="item-section <?php echo $gridClass; ?>">
										<?php 
											foreach ($languages as $lang) { 
												$rtlStyle = AdminPageHelper::RTLStyle($lang);
												$message = '';
												if(isset($notifications[$i]['message'][$lang])){
													$message = InputFilterHelper::filterTrim($notifications[$i]['message'][$lang]);
												}
												$messageKey = "message_{$i}_{$lang}";
												$messageName = "notifications[{$i}][message][{$lang}]";
										?>
											<div class="grid">
												<label for="<?php echo $messageKey; ?>"><?php echo __('Push Notification Message', 'agora-abandoned-cart'); ?> <strong><?php echo LanguageHelper::get($lang); ?></strong></label>
												<div class="custom-short-codes">
													<?php foreach ($customVariables as $customVar => $label) { ?>
													<button type="button" class="button codes" onclick="abShortCode('<?php echo $customVar; ?>', '<?php echo $messageKey; ?>');"><?php echo $label; ?> </button>
													<?php } ?>
													<div class="alignright">
														<button type="button" onclick="direction('ltr', '<?php echo $messageKey; ?>')" class="button">
															<span class="dashicons dashicons-editor-alignleft"></span>
														</button>
														<button type="button" onclick="direction('rtl', '<?php echo $messageKey; ?>')" class="button" >
															<span class="dashicons dashicons-editor-alignright"></span>
														</button>
													</div>
												</div>
												 <textarea class="regular-text abandoned-cart-inputs" type="text" name="<?php echo $messageName; ?>" id="<?php echo $messageKey; ?>" style="<?php echo $rtlStyle; ?>" rows="6"><?php echo $message; ?></textarea> 
											</div>
										<?php } ?>
									</div>
									<div class="item-section <?php echo $gridClass; ?>">
										<?php 
											foreach ($languages as $lang) { 
												$rtlStyle = AdminPageHelper::RTLStyle($lang);
												$mailSubject = '';
												if(isset($notifications[$i]['mail_subject'][$lang])){
													$mailSubject = InputFilterHelper::filterTrim($notifications[$i]['mail_subject'][$lang]); 
												}
												$mailSubjectKey = "mail_subject_{$i}_{$lang}";
												$mailSubjectName = "notifications[{$i}][mail_subject][{$lang}]";
											?>
											<div class="grid">
												<label for="<?php echo $mailSubjectKey; ?>"><?php echo __('Mail Subject', 'agora-abandoned-cart'); ?> <strong><?php echo LanguageHelper::get($lang); ?></strong></label>
												<label for="mail_subject_<?php echo $i; ?>">
													 <input class="regular-text abandoned-cart-inputs" type="text" name="<?php echo $mailSubjectName; ?>" id="<?php echo $mailSubjectKey; ?>" style="<?php echo $rtlStyle; ?>" value="<?php echo $mailSubject; ?>"/>
												</label>
											</div>
										<?php } ?>
									</div>
									<div class="item-section <?php echo $gridClass; ?>">
									<?php 
										foreach ($languages as $lang) { 
											$isRtlStyle = AdminPageHelper::isRTLStyle($lang);
											$mailMessage = '';
											if(isset($notifications[$i]['mail_message'][$lang])){
												$mailMessage = InputFilterHelper::filterTrim(stripslashes_deep($notifications[$i]['mail_message'][$lang])); 
											}
											$mailMessageKey = "mail_message_{$i}_{$lang}";
											$mailMessageName = "notifications[{$i}][mail_message][{$lang}]";
										?>
										<div class="grid">
											<label for="<?php echo $mailMessageKey; ?>"><?php echo __('Mail Message', 'agora-abandoned-cart'); ?> <strong><?php echo LanguageHelper::get($lang); ?></strong></label>
											<div class="abandoned-cart-inputs">
										  	<?php 
												wp_editor($mailMessage, $mailMessageKey, [
													'classes' => 'abandoned-cart-inputs', 
													'media_buttons' => true,
													'textarea_rows' => 10,
													'textarea_name' => $mailMessageName,
													'directionality' => $isRtlStyle? 'rtl' : 'ltr',
												]); 
											?> 
											</div>
										</div>
									<?php } ?>
									</div>
									<div class="item-section">
										<label for="<?php echo $timeKey; ?>">
											 <?php echo __('Execute at', 'agora-abandoned-cart'); ?> <input class="small-text" type="time" name="<?php echo $timeFieldName; ?>" id="<?php echo $timeKey; ?>" value="<?php echo $time; ?>" style="width: 280px;"> 
										</label>
										<label for="<?php echo $durationKey; ?>">
											 <?php echo __('Cart abandoned for', 'agora-abandoned-cart'); ?> <input class="small-text" type="number" name="<?php echo $durationFieldName; ?>" id="<?php echo $durationKey; ?>" value="<?php echo $duration; ?>" style="width: 180px;"> <?php echo __('hours', 'agora-abandoned-cart'); ?>
										</label>
									</div>
								</div>
							</div>
						<?php } ?>

			<?php } ?>
	</div>
	<?php submit_button(); ?>
</form>
	<script>
	function direction(dir='rtl', id) {
		document.getElementById(id).style.direction = dir;
		document.getElementById(id).style.textAlign = dir == 'rtl'? 'right' : 'left';
	}

	function abShortCode(myValue, id) {
		myField = document.getElementById(id);
	    if (document.selection) {
	        myField.focus();
	        sel = document.selection.createRange();
	        sel.text = myValue;
	    }
	    else if(window.navigator.userAgent.indexOf("Edge") > -1) {
	      var startPos = myField.selectionStart; 
	      var endPos = myField.selectionEnd; 
	          
	      myField.value = myField.value.substring(0, startPos)+ myValue 
	             + myField.value.substring(endPos, myField.value.length); 
	      
	      var pos = startPos + myValue.length;
	      myField.focus();
	      myField.setSelectionRange(pos, pos);
	    }
	    else if (myField.selectionStart || myField.selectionStart == '0') {
	        var startPos = myField.selectionStart;
	        var endPos = myField.selectionEnd;
	        myField.value = myField.value.substring(0, startPos)
	            + myValue
	            + myField.value.substring(endPos, myField.value.length);
	    } else {
	        myField.value += myValue;
	    }
	}
	</script>