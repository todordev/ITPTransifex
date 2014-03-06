CREATE TABLE IF NOT EXISTS `#__itptfx_languages` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `code` char(5) NOT NULL,
  `short_code` char(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_itptfx_code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__itptfx_packages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `filename` varchar(128) NOT NULL,
  `description` text,
  `version` varchar(32) NOT NULL,
  `language` varchar(5) NOT NULL,
  `type` enum('component','module','plugin') NOT NULL,
  `hash` varchar(32) NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__itptfx_packages_map` (
  `package_id` int(10) unsigned NOT NULL,
  `resource_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`package_id`,`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__itptfx_projects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` text,
  `source_language_code` char(5) DEFAULT NULL,
  `filename` varchar(64) DEFAULT NULL COMMENT 'This is the file name of the package.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__itptfx_resources` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alias` varchar(64) NOT NULL,
  `filename` varchar(64) DEFAULT NULL,
  `type` char(5) DEFAULT NULL,
  `i18n_type` varchar(64) DEFAULT NULL,
  `source_language_code` char(5) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `project_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
