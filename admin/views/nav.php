<?php
use AgoraAbandonedCart\Classes\Helpers\AdminPageHelper;
use AgoraAbandonedCart\Classes\Helpers\InputFilterHelper;

$navUrl = AdminPageHelper::pluginTabUrl();
$nav = [
	'overview' => [
		'label' => __('Overview', 'agora-abandoned-cart'),
		'hidden' => false,
		'class' => 'nav-tab',
	],
	'general' => [
		'label' => __('General', 'agora-abandoned-cart'),
		'hidden' => false,
		'class' => 'nav-tab',
	],
	'settings' => [
		'label' => __('Settings', 'agora-abandoned-cart'),
		'hidden' => false,
		'class' => 'nav-tab',
	],
	'scheduled-notifications' => [
		'label' => __('Scheduled Notifications', 'agora-abandoned-cart'),
		'hidden' => !InputFilterHelper::isHigherThan($optionsValues['notifications_number'], 0),
		'class' => 'nav-tab',
	],
	'customer-carts-monitor' => [
		'label' => __('Customer carts', 'agora-abandoned-cart'),
		'hidden' => false,
		'class' => 'nav-tab',
	],
	'queue-monitor' => [
		'label' => __('Queue monitor', 'agora-abandoned-cart'),
		'hidden' => false,
		'class' => 'nav-tab',
	],
];
