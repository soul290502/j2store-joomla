CREATE TABLE IF NOT EXISTS `#__j2store_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `zone_id` varchar(255) NOT NULL,
  `country_id` varchar(255) NOT NULL,
  `phone_1` varchar(255) NOT NULL,
  `phone_2` varchar(255) NOT NULL,
  `fax` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
)  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__j2store_mycart` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(200) NOT NULL,
  `product_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `product_attributes` text NOT NULL COMMENT 'A CSV of productattributeoption_id values, always in numerical order',
  `product_qty` int(11) NOT NULL DEFAULT '1',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cartitem_params` text COMMENT 'Params for the cart item',
  PRIMARY KEY (`cart_id`),
  KEY `idx_user_product` (`user_id`,`product_id`)
)  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `#__j2store_orderfiles` (
  `orderfile_id` int(11) NOT NULL AUTO_INCREMENT,
  `orderitem_id` int(11) NOT NULL,
  `productfile_id` int(11) NOT NULL,
  `limit_count` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  PRIMARY KEY (`orderfile_id`)
)  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__j2store_orderinfo` (
  `orderinfo_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `orderpayment_id` int(11) NOT NULL COMMENT 'Ref FK to the id of the order table',
  `billing_company` varchar(64) DEFAULT NULL,
  `billing_last_name` varchar(32) DEFAULT NULL,
  `billing_first_name` varchar(32) DEFAULT NULL,
  `billing_middle_name` varchar(32) DEFAULT NULL,
  `billing_phone_1` varchar(32) DEFAULT NULL,
  `billing_phone_2` varchar(32) DEFAULT NULL,
  `billing_fax` varchar(32) DEFAULT NULL,
  `billing_address_1` varchar(64) NOT NULL DEFAULT '',
  `billing_address_2` varchar(64) DEFAULT NULL,
  `billing_city` varchar(32) NOT NULL DEFAULT '',
  `billing_zone_name` varchar(32) NOT NULL DEFAULT '',
  `billing_country_name` varchar(64) NOT NULL DEFAULT '',
  `billing_zone_id` int(11) NOT NULL DEFAULT '0',
  `billing_country_id` int(11) NOT NULL DEFAULT '0',
  `billing_zip` varchar(32) NOT NULL DEFAULT '',
  `billing_tax_number` varchar(32) DEFAULT NULL,
  `shipping_company` varchar(64) DEFAULT NULL,
  `shipping_last_name` varchar(32) DEFAULT NULL,
  `shipping_first_name` varchar(32) DEFAULT NULL,
  `shipping_middle_name` varchar(32) DEFAULT NULL,
  `shipping_phone_1` varchar(32) DEFAULT NULL,
  `shipping_phone_2` varchar(32) DEFAULT NULL,
  `shipping_fax` varchar(32) DEFAULT NULL,
  `shipping_address_1` varchar(64) NOT NULL DEFAULT '',
  `shipping_address_2` varchar(64) DEFAULT NULL,
  `shipping_city` varchar(32) NOT NULL DEFAULT '',
  `shipping_zip` varchar(255) NOT NULL,
  `shipping_zone_name` varchar(32) NOT NULL DEFAULT '',
  `shipping_country_name` varchar(64) NOT NULL DEFAULT '',
  `shipping_zone_id` int(11) NOT NULL DEFAULT '0',
  `shipping_country_id` int(11) NOT NULL DEFAULT '0',
  `shipping_id` varchar(32) NOT NULL DEFAULT '',
  `shipping_tax_number` varchar(32) DEFAULT NULL,
  `user_email` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`orderinfo_id`),
  KEY `idx_orderinfo_order_id` (`order_id`)
)  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__j2store_orderitemattributes` (
  `orderitemattribute_id` int(11) NOT NULL AUTO_INCREMENT,
  `orderitem_id` int(11) NOT NULL,
  `productattributeoption_id` int(11) NOT NULL,
  `orderitemattribute_name` varchar(255) NOT NULL,
  `orderitemattribute_price` decimal(12,5) NOT NULL,
  `orderitemattribute_code` varchar(255) NOT NULL,
  `orderitemattribute_prefix` varchar(1) NOT NULL,
  PRIMARY KEY (`orderitemattribute_id`),
  KEY `productattribute_id` (`productattributeoption_id`)
)  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__j2store_orderitems` (
  `orderitem_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `orderitem_attributes` text NOT NULL COMMENT 'A CSV of productattributeoption_id values, always in numerical order',
  `orderitem_attribute_names` text NOT NULL COMMENT 'A CSV of productattributeoption_name values',
  `orderitem_sku` varchar(64) NOT NULL DEFAULT '',
  `orderitem_name` varchar(64) NOT NULL DEFAULT '',
  `orderitem_quantity` int(11) DEFAULT NULL,
  `orderitem_price` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT 'Base price of the item',
  `orderitem_attributes_price` varchar(64) NOT NULL COMMENT 'The increase or decrease in price per item as a result of attributes. Includes + or - sign',
  `orderitem_discount` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT 'Coupon discount applied to each item',
  `orderitem_final_price` decimal(15,5) NOT NULL DEFAULT '0.00000' COMMENT 'Price of item inclusive of quantity, attributes, tax, and shipping',
  `orderitem_tax` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `orderitem_shipping` decimal(12,5) NOT NULL DEFAULT '0.00000',
  `orderitem_shipping_tax` decimal(12,5) NOT NULL DEFAULT '0.00000',
  `orderitem_status` char(1) DEFAULT NULL,
  `modified_date` datetime NOT NULL COMMENT 'GMT',
  PRIMARY KEY (`orderitem_id`),
  KEY `idx_order_item_order_id` (`order_id`)
)  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `#__j2store_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL DEFAULT '',
  `shipping_method_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `orderpayment_type` varchar(255) NOT NULL DEFAULT '' COMMENT 'Element name of payment plugin',
  `orderpayment_amount` decimal(15,5) DEFAULT '0.00000',
  `orderpayment_tax` decimal(15,5) DEFAULT '0.00000',
  `order_total` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `order_subtotal` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `order_tax` decimal(10,2) NOT NULL DEFAULT '0.00',
  `order_shipping` decimal(10,2) NOT NULL,
  `order_discount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(255) NOT NULL DEFAULT '',
  `transaction_status` varchar(255) NOT NULL DEFAULT '',
  `transaction_details` text NOT NULL,
  `created_date` datetime NOT NULL COMMENT 'GMT',
  `order_state` varchar(255) NOT NULL,
  `order_state_id` int(11) NOT NULL,
  `paypal_status` varchar(255) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `customer_note` text NOT NULL,
  PRIMARY KEY (`id`)
)  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `#__j2store_prices` (
  `price_id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(8) NOT NULL,
  `item_price` decimal(11,3) NOT NULL,
  `item_tax` int(8) NOT NULL,
  `item_shipping` int(8) NOT NULL,
  `product_enabled` tinyint(2) NOT NULL,
  `item_sku` varchar(255) NOT NULL,
  `params` text NOT NULL,  
  PRIMARY KEY (`price_id`),
  KEY `article_id` (`article_id`)
)  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `#__j2store_productattributeoptions` (
  `productattributeoption_id` int(11) NOT NULL AUTO_INCREMENT,
  `productattribute_id` int(11) NOT NULL,
  `productattributeoption_name` varchar(255) NOT NULL,
  `productattributeoption_price` decimal(12,5) NOT NULL,
  `productattributeoption_code` varchar(255) NOT NULL,
  `productattributeoption_prefix` varchar(1) NOT NULL,
  `productattributeoption_short_desc` varchar(255) NOT NULL,
  `productattributeoption_long_desc` varchar(255) NOT NULL,
  `productattributeoption_ref` varchar(255) NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`productattributeoption_id`),
  KEY `productattribute_id` (`productattribute_id`)
)  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `#__j2store_productattributes` (
  `productattribute_id` int(11) NOT NULL AUTO_INCREMENT,
  `productattribute_name` varchar(255) NOT NULL,
  `productattribute_display_type` varchar(255) NOT NULL,
  `productattribute_required` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`productattribute_id`),
  KEY `product_id` (`product_id`)
)  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

CREATE TABLE IF NOT EXISTS `#__j2store_productfiles` (
  `productfile_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_file_display_name` varchar(255) NOT NULL,
  `product_file_save_name` varchar(255) NOT NULL,
  `purchase_required` tinyint(1) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `download_limit` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`productfile_id`)
)  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__j2store_shippingmethods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shipping_method_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `published` tinyint(1) NOT NULL,
  `shipping_method_type` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
)  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__j2store_shippingrates` (
  `shipping_rate_id` int(11) NOT NULL AUTO_INCREMENT,
  `shipping_method_id` int(11) NOT NULL,
  `shipping_rate_price` decimal(12,5) NOT NULL,
  `shipping_rate_weight_start` decimal(11,3) NOT NULL,
  `shipping_rate_weight_end` decimal(11,3) NOT NULL,
  `shipping_rate_handling` decimal(12,5) NOT NULL,
  `created_date` datetime NOT NULL COMMENT 'GMT Only',
  `modified_date` datetime NOT NULL COMMENT 'GMT Only',
  PRIMARY KEY (`shipping_rate_id`)
)  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__j2store_taxprofiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `taxprofile_name` varchar(255) NOT NULL,
  `tax_percent` decimal(10,5) NOT NULL,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
)  DEFAULT CHARSET=utf8 ;
