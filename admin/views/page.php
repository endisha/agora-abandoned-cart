<?php
use AgoraAbandonedCart\Classes\Helpers\AdminPageHelper;

include_once __DIR__ . '/nav.php'; 

$default = 'overview';
$tab = isset($_GET['tab'])? filter_var($_GET['tab'], FILTER_SANITIZE_STRING) : '';
$tab = AdminPageHelper::navKeys($nav, $tab, $default);
$navItems = AdminPageHelper::buildNavs($nav, $tab, $navUrl);
$page = AdminPageHelper::buildTabs($nav, $tab, __DIR__, $default);

?>

<h2><?php echo __('Agora Abandoned Cart', 'agora-abandoned-cart'); ?></h2><hr />
<p><?php echo __('Send asynchronous emails and push notifications to your customers, reminding them of their abandoned carts.', 'agora-abandoned-cart'); ?></p>
<div class="wrap woocommerce agora-abandoned-cart-wrapper">
	<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
	<?php foreach ($navItems as $navItem) { ?>
		<a href="<?php echo $navItem['href']; ?>" class="<?php echo $navItem['class']; ?>">
			<?php echo $navItem['label']; ?>
		</a>
	<?php } ?>
	</nav>
	<div class="tab-page">
	<?php include_once $page; ?>
	</div>
</div>