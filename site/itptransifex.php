<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

$controller = JControllerLegacy::getInstance('ITPTransifex');
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();