CREATE TABLE IF NOT EXISTS `#__itptfx_languages` (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `locale` char(5) NOT NULL,
  `code` char(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_itptfx_locale` (`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__itptfx_packages` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `alias` varchar(64) DEFAULT NULL,
  `filename` varchar(64) DEFAULT NULL,
  `description` text,
  `version` varchar(32) NOT NULL,
  `language` varchar(5) NOT NULL,
  `type` enum('component','module','plugin','library','template','other') NOT NULL,
  `project_id` smallint(5) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_pkg_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__itptfx_packages_map` (
  `package_id` int(10) UNSIGNED NOT NULL,
  `resource_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`package_id`,`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__itptfx_projects` (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` text,
  `source_language_code` char(5) DEFAULT NULL,
  `filename` varchar(64) DEFAULT '',
  `image` varchar(24) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `ordering` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `published` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `last_update` date NOT NULL DEFAULT '1000-01-01',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_proj_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__itptfx_resources` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alias` varchar(64) NOT NULL,
  `filename` varchar(64) DEFAULT NULL,
  `category` char(16) DEFAULT '',
  `path` varchar(255) DEFAULT NULL,
  `source` varchar(32) DEFAULT NULL,
  `i18n_type` varchar(64) DEFAULT NULL,
  `source_language_code` char(5) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `project_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;