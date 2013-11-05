<?php
/**
 * @package      ITPTransifex
 * @subpackage   Libraries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

if(!defined("ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR")) {
    define("ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR", JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR. "components" . DIRECTORY_SEPARATOR ."com_itptransifex");
}

if(!defined("ITPTRANSIFEX_PATH_COMPONENT_SITE")) {
    define("ITPTRANSIFEX_PATH_COMPONENT_SITE", JPATH_SITE . DIRECTORY_SEPARATOR. "components" . DIRECTORY_SEPARATOR ."com_itptransifex");
}

if(!defined("ITPTRANSIFEX_PATH_LIBRARY")) {
    define("ITPTRANSIFEX_PATH_LIBRARY", ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR. "libraries");
}

if(!defined("ITPRISM_PATH_LIBRARY")) {
    define("ITPRISM_PATH_LIBRARY", JPATH_LIBRARIES . DIRECTORY_SEPARATOR. "itprism");
}

jimport('joomla.utilities.arrayhelper');

// Register Component libraries
JLoader::register("ItpTransifexVersion", ITPTRANSIFEX_PATH_LIBRARY . DIRECTORY_SEPARATOR . "version.php");

// Register helpers
JLoader::register("ItpTransifexHelper", ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "itptransifex.php");

JLoader::register("ItpTransifexProject", ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "libraries" . DIRECTORY_SEPARATOR . "project.php");
JLoader::register("ItpTransifexProjects", ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "libraries" . DIRECTORY_SEPARATOR . "projects.php");
