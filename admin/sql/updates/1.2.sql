ALTER TABLE `#__itptfx_packages` DROP `hash` ;
ALTER TABLE `#__itptfx_packages` ADD `alias` VARCHAR( 64 ) NULL DEFAULT NULL AFTER `name`;

ALTER TABLE `#__itptfx_packages` ADD UNIQUE `idx_pkg_alias` (`alias`);
ALTER TABLE `#__itptfx_projects` ADD UNIQUE `idx_proj_alias` (`alias`);