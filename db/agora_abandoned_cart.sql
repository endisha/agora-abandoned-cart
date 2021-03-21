CREATE TABLE `{prefix}agora_abandoned_cart` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) DEFAULT NULL,
  `cart` text,
  `total_cart` double(10,3) unsigned NOT NULL DEFAULT '0.000',
  `counter` int(11) DEFAULT NULL,
  `status` varchar(255) DEFAULT 'pending',
  `created_at` datetime DEFAULT NULL,
  `refreshed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;