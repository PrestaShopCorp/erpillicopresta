--
-- Structure de la table `ps_erpip_feature`
--

CREATE TABLE IF NOT EXISTS `[DB_NAME]`.`[DB_PREFIX]erpip_feature` (
	`id_erpip_feature` int(11) NOT NULL AUTO_INCREMENT,
	`controller` varchar(50) NOT NULL,
	`picture` varchar(250) NOT NULL,
	`is_root` tinyint(1) NOT NULL DEFAULT '1',
        `order` int(11) NOT NULL,
	`status` varchar(32) NOT NULL DEFAULT 'light',
	`key1` varchar(250) NOT NULL,
	`key2` varchar(250) NOT NULL,
	PRIMARY KEY (`id_erpip_feature`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;;

--
-- Structure de la table `erpip_feature_language`
--

CREATE TABLE IF NOT EXISTS `[DB_NAME]`.`[DB_PREFIX]erpip_feature_language` (
  `id_erpip_feature_language` int(11) NOT NULL AUTO_INCREMENT,
  `id_erpip_feature` int(11) NOT NULL,
  `iso_code` varchar(10) NOT NULL,
  `name` varchar(250) NOT NULL,
  PRIMARY KEY (`id_erpip_feature_language`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;;

--
-- Structure de la table `ps_stock_image`
--

CREATE TABLE IF NOT EXISTS `[DB_NAME]`.`[DB_PREFIX]stock_image` (
	`id_stock_image` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(50) NOT NULL,
	`date_add` datetime NOT NULL,
	`type_stock` int(11) NOT NULL,
	PRIMARY KEY (`id_stock_image`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;;



-- --------------------------------------------------------

--
-- Structure de la table `ps_stock_image_content`
--

CREATE TABLE IF NOT EXISTS `[DB_NAME]`.`[DB_PREFIX]stock_image_content` (
	`id_stock_image_content` int(11) NOT NULL AUTO_INCREMENT,
	`id_product` int(11) NOT NULL,
	`id_product_attribute` int(11) NOT NULL,
	`id_stock_image` int(11) NOT NULL,
	`wholesale_price` int(11) NOT NULL,
	`price_te` int(11) NOT NULL,
	`valuation` varchar(10) NOT NULL,
	`quantity` int(11) DEFAULT NULL,
	`physical_quantity` int(11) DEFAULT NULL,
	`usable_quantity` int(11) DEFAULT NULL,
	`real_quantity` int(11) DEFAULT NULL,
	`location` varchar(250) DEFAULT NULL,
	PRIMARY KEY (`id_stock_image_content`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;;

-- --------------------------------------------------------

--
-- Table inventory
--
CREATE TABLE IF NOT EXISTS `[DB_NAME]`.`[DB_PREFIX]erpip_inventory` (
	`id_erpip_inventory` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(50) NOT NULL,
	`date_add` datetime NOT NULL,
	`date_upd` datetime NOT NULL,
	PRIMARY KEY (`id_erpip_inventory`),
	UNIQUE KEY `id_erpip_inventory` (`id_erpip_inventory`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;;

-- --------------------------------------------------------

--
-- Table inventory_product
--

CREATE TABLE IF NOT EXISTS `[DB_NAME]`.`[DB_PREFIX]erpip_inventory_product` (
	`id_erpip_inventory_product` int(11) NOT NULL AUTO_INCREMENT,
	`id_erpip_inventory` int(11) NOT NULL,
	`id_product` int(11) NOT NULL,
	`id_product_attribute` int(11) NOT NULL,
	`id_mvt_reason` int(11) NOT NULL,
	`qte_before` int(11) NOT NULL,
	`qte_after` int(11) NOT NULL,
        `id_warehouse` int(11),
	PRIMARY KEY (`id_erpip_inventory_product`),
	UNIQUE KEY `id_erpip_inventory_product` (`id_erpip_inventory_product`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;;

-- --------------------------------------------------------

--
-- Table erpip_supply_order_customer
--
CREATE TABLE IF NOT EXISTS `[DB_NAME]`.`[DB_PREFIX]erpip_supply_order_customer` (
	`id_erpip_supply_order_customer` int(11) NOT NULL AUTO_INCREMENT,
	`id_supply_order` int(11) unsigned NOT NULL,
	`id_supply_order_detail` int(11) unsigned NOT NULL,
	`id_order_detail` int(11) unsigned NOT NULL,
	`id_customer` int(11) unsigned NOT NULL,
	PRIMARY KEY (`id_erpip_supply_order_customer`),
	KEY `id_supply_order_detail` (`id_supply_order_detail`),
	KEY `id_supply_order` (`id_supply_order`),
	KEY `id_order_detail` (`id_order_detail`),
	KEY `id_customer` (`id_customer`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;;

-- --------------------------------------------------------

--
-- Table erpip_supplier
--

CREATE TABLE IF NOT EXISTS `[DB_NAME]`.`[DB_PREFIX]erpip_supplier` (
	`id_erpip_supplier` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`id_supplier` int(10) unsigned NOT NULL,
	`email` varchar(128) DEFAULT NULL,
	`fax` varchar(32) DEFAULT NULL,
	`franco_amount` decimal(20,6) DEFAULT NULL,
	`discount_amount` decimal(20,6) DEFAULT NULL,
	`shipping_amount` decimal(20,6) DEFAULT NULL,
	`escompte` decimal(20,6) DEFAULT NULL,
	`delivery_time` varchar(32) DEFAULT NULL,
	`account_number_accounting` varchar(128) DEFAULT NULL,
	PRIMARY KEY (`id_erpip_supplier`),
	KEY `id_supplier` (`id_supplier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;;

--
-- Table erpip_supply_order
--

CREATE TABLE IF NOT EXISTS `[DB_NAME]`.`[DB_PREFIX]erpip_supply_order` (
	`id_erpip_supply_order` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`id_supply_order` int(10) unsigned NOT NULL,
	`escompte` VARCHAR(64) NULL,
	`invoice_number` VARCHAR(64) NULL,
	`date_to_invoice` DATE NULL,
	`global_discount_amount` INT(11) NULL,
	`global_discount_type` VARCHAR(64)  NULL,
	`shipping_amount` VARCHAR(64)  NULL,
	`description` text,
	PRIMARY KEY (`id_erpip_supply_order`),
	KEY `id_supply_order` (`id_supply_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;;


--
-- Table erpip_supply_order_detail
--

CREATE TABLE IF NOT EXISTS `[DB_NAME]`.`[DB_PREFIX]erpip_supply_order_detail` (
	`id_erpip_supply_order_detail` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`id_supply_order_detail` int(10) unsigned NOT NULL,
	`comment` VARCHAR(64)  NULL,
	PRIMARY KEY (`id_erpip_supply_order_detail`),
	KEY `id_supply_order_detail` (`id_supply_order_detail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;;

--
-- Table erpip_supply_order_receipt_history
--

CREATE TABLE IF NOT EXISTS `[DB_NAME]`.`[DB_PREFIX]erpip_supply_order_receipt_history` (
	`id_erpip_supply_order_receipt_history` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`id_supply_order_receipt_history` int(10) unsigned NOT NULL,
	`unit_price` decimal(20,6) DEFAULT NULL,
	`discount_rate` decimal(20,6) DEFAULT NULL,
	`is_canceled` BOOL NOT NULL DEFAULT  '0',
	PRIMARY KEY (`id_erpip_supply_order_receipt_history`),
	KEY `id_supply_order_receipt_history` (`id_supply_order_receipt_history`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;;

--
-- Table erpip_warehouse_product_location
--

CREATE TABLE IF NOT EXISTS `[DB_NAME]`.`[DB_PREFIX]erpip_warehouse_product_location` (
        `id_erpip_warehouse_product_location` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_warehouse_product_location` int(10) unsigned NOT NULL,
        `id_zone` int(10) unsigned NOT NULL,
        `id_zone_parent` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id_erpip_warehouse_product_location`),
        KEY `id_warehouse_product_location` (`id_warehouse_product_location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;;

--
-- Table erpip_warehouse_product_location
--

CREATE TABLE IF NOT EXISTS `[DB_NAME]`.`[DB_PREFIX]erpip_zone` (
        `id_erpip_zone` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_warehouse` int(10) unsigned NOT NULL,
        `name` VARCHAR(64) NOT NULL,
        `id_parent` int(10) unsigned NOT NULL,
        `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
        `date_add` datetime NOT NULL,
        `date_upd` datetime NOT NULL,
        PRIMARY KEY (`id_erpip_zone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;;