CREATE TABLE `{prefix}agora_abandoned_cart_queue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) NOT NULL DEFAULT '',
  `email_notification` tinyint(1) DEFAULT NULL,
  `push_notification` tinyint(1) DEFAULT NULL,
  `status` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;