<?php
/**
 * @package      ITPTransifex
 * @subpackage   Initialization
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

if (!defined('ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR')) {
    define('ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_itptransifex');
}

if (!defined('ITPTRANSIFEX_PATH_COMPONENT_SITE')) {
    define('ITPTRANSIFEX_PATH_COMPONENT_SITE', JPATH_SITE . '/components/com_itptransifex');
}

if (!defined('ITPTRANSIFEX_PATH_LIBRARY')) {
    define('ITPTRANSIFEX_PATH_LIBRARY', JPATH_LIBRARIES . '/Transifex');
}

jimport('joomla.filesystem.archive');

JLoader::registerNamespace('Transifex', JPATH_LIBRARIES);

// Register helpers
JLoader::register('ItpTransifexHelper', ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'itptransifex.php');
JLoader::register('ItpTransifexHelperRoute', ITPTRANSIFEX_PATH_COMPONENT_SITE . '/helpers/route.php');

// Load observers
JLoader::register('ItpTransifexObserverResource', ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR . '/tables/observers/resource.php');
JLoader::register('ItpTransifexObserverProject', ITPTRANSIFEX_PATH_COMPONENT_ADMINISTRATOR . '/tables/observers/project.php');

// Register Observers
JObserverMapper::addObserverClassToClass('ItpTransifexObserverResource', 'ItpTransifexTableResource', array('typeAlias' => 'com_itptransifex.resource'));
JObserverMapper::addObserverClassToClass('ItpTransifexObserverProject', 'ItpTransifexTableProject', array('typeAlias' => 'com_itptransifex.project'));

JLog::addLogger(
    array(
        'text_file' => 'com_userideas.errors.php'
    ),
    // Sets messages of specific log levels to be sent to the file
    JLog::CRITICAL + JLog::EMERGENCY + JLog::ALERT + JLog::ERROR + JLog::WARNING,
    // The log category/categories which should be recorded in this file
    // In this case, it's just the one category from our extension, still
    // we need to put it inside an array
    array('com_userideas')
);