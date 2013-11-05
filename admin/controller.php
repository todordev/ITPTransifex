<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Default Controller
 *
 * @package		 ItpTransifex
 * @subpackage   Components
  */
class ItpTransifexController extends JControllerLegacy {
    
	public function display( ) {

		$app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        $option   = $app->input->getCmd("option");
        
        $document = JFactory::getDocument();
		/** @var $document JDocumentHtml **/
        
        $viewName      = $app->input->getCmd('view', 'dashboard');
        $app->input->set("view", $viewName);

        parent::display();
        return $this;
	}

}