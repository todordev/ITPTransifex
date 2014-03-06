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

if(!defined("ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR")) {
    define("ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR", JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. "components" .DIRECTORY_SEPARATOR. "com_itptransifex");
}

if(!defined("ITPTRANSIFEX_PATH_COMPONENT_SITE")) {
    define("ITPTRANSIFEX_PATH_COMPONENT_SITE", JPATH_SITE .DIRECTORY_SEPARATOR. "components" .DIRECTORY_SEPARATOR. "com_itptransifex");
}

if(!defined("ITPTRANSIFEX_PATH_LIBRARY")) {
    define("ITPTRANSIFEX_PATH_LIBRARY", JPATH_LIBRARIES .DIRECTORY_SEPARATOR. "itptransifex");
}

jimport('joomla.utilities.arrayhelper');

// Register Component libraries
JLoader::register("ItpTransifexVersion", ITPTRANSIFEX_PATH_LIBRARY . DIRECTORY_SEPARATOR . "version.php");

// Register helpers
JLoader::register("ItpTransifexHelper", ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "itptransifex.php");

// Load observers
JLoader::register("ItpTransifexObserverResource", ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR .DIRECTORY_SEPARATOR. "tables" .DIRECTORY_SEPARATOR. "observers" .DIRECTORY_SEPARATOR. "resource.php");

// Register Observers
JObserverMapper::addObserverClassToClass('ItpTransifexObserverResource', 'ItpTransifexTableResource', array('typeAlias' => 'com_itptransifex.resource'));

// Load library language
$lang = JFactory::getLanguage();
$lang->load('lib_itptransifex', ITPTRANSIFEX_PATH_LIBRARY);