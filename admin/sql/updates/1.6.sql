ALTER TABLE `#__itptfx_projects` ADD `ordering` SMALLINT UNSIGNED NOT NULL DEFAULT '0' AFTER `filename`;
ALTER TABLE `#__itptfx_projects` ADD `published` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `ordering`;
ALTER TABLE `#__itptfx_projects` ADD `image` VARCHAR(24) NULL DEFAULT NULL AFTER `filename`;
ALTER TABLE `#__itptfx_projects` ADD `last_update` DATE NOT NULL DEFAULT '0000-00-00' AFTER `published`;
ALTER TABLE `#__itptfx_projects` ADD `link` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `image` ;

ALTER TABLE `#__itptfx_projects` CHANGE `id` `id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__itptfx_packages` CHANGE `project_id` `project_id` SMALLINT UNSIGNED NOT NULL;
