ALTER TABLE `#__itptfx_packages` CHANGE `lang_code` `language` VARCHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
ALTER TABLE `#__itptfx_resources` DROP `category` ;
ALTER TABLE `#__itptfx_packages` ADD `type` ENUM( "component", "module", "plugin" ) NOT NULL AFTER `language` ;
ALTER TABLE `#__itptfx_packages` DROP INDEX package_hash;