CREATE TABLE IF NOT EXISTS `#__je_tpvv_common` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `virtuemart_payment_id` int(11) NOT NULL,
  `payment_key` char(50) CHARACTER SET utf8 NOT NULL,
  `payment_value` blob NOT NULL,
  PRIMARY KEY (`id`)
);
