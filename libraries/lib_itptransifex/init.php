<?php
/**
 * @package      ITPTransifex
 * @subpackage   Libraries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

if (!defined("ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR")) {
    define("ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR", JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_itptransifex");
}

if (!defined("ITPTRANSIFEX_PATH_COMPONENT_SITE")) {
    define("ITPTRANSIFEX_PATH_COMPONENT_SITE", JPATH_SITE . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_itptransifex");
}

if (!defined("ITPTRANSIFEX_PATH_LIBRARY")) {
    define("ITPTRANSIFEX_PATH_LIBRARY", JPATH_LIBRARIES . DIRECTORY_SEPARATOR . "itptransifex");
}

// Register Component libraries
JLoader::register("ItpTransifexVersion", ITPTRANSIFEX_PATH_LIBRARY . "/version.php");
JLoader::register("ItpTransifexLanguages", ITPTRANSIFEX_PATH_LIBRARY . "/languages.php");

// Register helpers
JLoader::register("ItpTransifexHelper", ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "itptransifex.php");
JLoader::register("ItpTransifexHelperRoute", ITPTRANSIFEX_PATH_COMPONENT_SITE . "/helpers/route.php");

// Load observers
JLoader::register("ItpTransifexObserverResource", ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR . "/tables/observers/resource.php");
JLoader::register("ItpTransifexObserverProject", ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR . "/tables/observers/project.php");

// Register Observers
JObserverMapper::addObserverClassToClass('ItpTransifexObserverResource', 'ItpTransifexTableResource', array('typeAlias' => 'com_itptransifex.resource'));
JObserverMapper::addObserverClassToClass('ItpTransifexObserverProject', 'ItpTransifexTableProject', array('typeAlias' => 'com_itptransifex.project'));
