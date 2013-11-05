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

class ItpTransifexHelper {
	
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 * @since	1.6
	 */
	public static function addSubmenu($vName = 'dashboard') {
	    
	    JHtmlSidebar::addEntry(
			JText::_('COM_ITPTRANSIFEX_DASHBOARD'),
			'index.php?option=com_itptransifex&view=dashboard',
			$vName == 'dashboard'
		);
		
		JHtmlSidebar::addEntry(
			JText::_('COM_ITPTRANSIFEX_PROJECTS'),
			'index.php?option=com_itptransifex&view=projects',
			$vName == 'projects'
		);
		
		JHtmlSidebar::addEntry(
			JText::_('COM_ITPTRANSIFEX_PACKAGES'),
			'index.php?option=com_itptransifex&view=packages',
			$vName == 'packages'
		);
		
		JHtmlSidebar::addEntry(
    		JText::_('COM_ITPTRANSIFEX_LANGUAGES'),
    		'index.php?option=com_itptransifex&view=languages',
    		$vName == 'languages'
        );

	}
	
	/**
	 * Load and return projects as list options.
	 * 
	 * @return array
	 */
	public static function getProjectsOptions() {
	    
	    $db = JFactory::getDbo();
	    
	    $query = $db->getQuery(true);
	    
	    $query
	       ->select("a.id AS value, a.name AS text")
	       ->from($db->quoteName("#__itptfx_projects", "a"))
	       ->group("a.name");
	    
	    $db->setQuery($query);
	    $results = $db->loadAssocList();
	    
	    if(!$results) {
	        $results = array();
	    }
	    
	    return $results;
	}
	
}