CREATE TABLE IF NOT EXISTS `#__itptfx_languages` (
  `id` smallint(5) unsigned NOT NULL,
  `name` varchar(128) NOT NULL,
  `code` char(5) NOT NULL,
  `short_code` char(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__itptfx_packages` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(128) NOT NULL,
  `alias` varchar(64) DEFAULT NULL,
  `filename` varchar(128) NOT NULL,
  `description` text,
  `version` varchar(32) NOT NULL,
  `language` varchar(5) NOT NULL,
  `type` enum('component','module','plugin','library') NOT NULL,
  `project_id` smallint(5) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__itptfx_packages_map` (
  `package_id` int(10) unsigned NOT NULL,
  `resource_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__itptfx_projects` (
  `id` smallint(5) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` text,
  `source_language_code` char(5) DEFAULT NULL,
  `filename` varchar(64) DEFAULT NULL COMMENT 'This is the file name of the package.',
  `image` varchar(24) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `ordering` smallint(5) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `last_update` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__itptfx_resources` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `alias` varchar(64) NOT NULL,
  `filename` varchar(64) DEFAULT NULL,
  `type` char(5) DEFAULT NULL,
  `i18n_type` varchar(64) DEFAULT NULL,
  `source_language_code` char(5) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `project_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `#__itptfx_languages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_itptfx_code` (`code`);

ALTER TABLE `#__itptfx_packages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_pkg_alias` (`alias`);

ALTER TABLE `#__itptfx_packages_map`
  ADD PRIMARY KEY (`package_id`,`resource_id`);

ALTER TABLE `#__itptfx_projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_proj_alias` (`alias`);

ALTER TABLE `#__itptfx_resources`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `#__itptfx_languages`
  MODIFY `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__itptfx_packages`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__itptfx_projects`
  MODIFY `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__itptfx_resources`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

