ALTER TABLE `#__itptfx_languages` DROP INDEX `idx_itptfx_code`;
ALTER TABLE `#__itptfx_languages` CHANGE `code` `locale` CHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `#__itptfx_languages` CHANGE `short_code` `code` CHAR(2) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `#__itptfx_languages` ADD UNIQUE `idx_itptfx_locale` (`locale`);

ALTER TABLE `#__itptfx_projects` CHANGE `filename` `filename` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '';
ALTER TABLE `#__itptfx_projects` CHANGE `last_update` `last_update` DATE NOT NULL DEFAULT '1000-01-01';

ALTER TABLE `#__itptfx_resources` CHANGE `type` `category` CHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '';
ALTER TABLE `#__itptfx_resources` ADD `path` VARCHAR(255) NULL DEFAULT NULL AFTER `category`;
ALTER TABLE `#__itptfx_resources` ADD `source` VARCHAR(32) NULL DEFAULT NULL AFTER `path`;

ALTER TABLE `#__itptfx_packages` DROP `folder`;
ALTER TABLE `#__itptfx_packages` CHANGE `type` `type` ENUM('component','module','plugin','library','template','other') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;