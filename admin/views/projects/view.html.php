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

jimport('joomla.application.component.view');
jimport('joomla.application.categories');

class ItpTransifexViewProjects extends JViewLegacy {
    
    protected $items;
    protected $pagination;
    protected $state;
    
    protected $option;
    
    public function __construct($config) {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }
    
    public function display($tpl = null){
        
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        
        $model = $this->getModel();
        
        // Load the component parameters.
        $this->resources  = $model->countResources();

        // HTML Helpers
        JHtml::addIncludePath(ITPRISM_PATH_LIBRARY.'/ui/helpers');
        
        // Add submenu
        ItpTransifexHelper::addSubmenu($this->getName());
        
        // Prepare sorting data
        $this->prepareSorting();
        
        // Prepare actions
        $this->addToolbar();
        $this->addSidebar();
        $this->setDocument();
        
        parent::display($tpl);
    }
    
    /**
     * Prepare sortable fields, sort values and filters.
     */
    protected function prepareSorting() {
    
        // Prepare filters
        $this->listOrder  = $this->escape($this->state->get('list.ordering'));
        $this->listDirn   = $this->escape($this->state->get('list.direction'));
        $this->saveOrder  = (strcmp($this->listOrder, 'a.ordering') != 0 ) ? false : true;
    
        $this->sortFields = array(
            'a.name'    => JText::_('COM_ITPTRANSIFEX_NAME'),
            'a.id'      => JText::_('JGRID_HEADING_ID')
        );
    
    }
    
    /**
     * Add a menu on the sidebar of page
     */
    protected function addSidebar() {
    
        $this->sidebar = JHtmlSidebar::render();
    
    }
    
    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar(){
        
        // Set toolbar items for the page
        JToolBarHelper::title(JText::_('COM_ITPTRANSIFEX_PROJECTS_MANAGER'));
        JToolBarHelper::addNew('project.add');
        JToolBarHelper::editList('project.edit');
        JToolBarHelper::divider();
		JToolBarHelper::custom('projects.update', "refresh", "", JText::_("COM_ITPTRANSIFEX_UPDATE"), false);
        JToolBarHelper::divider();
        JToolBarHelper::deleteList(JText::_("COM_ITPTRANSIFEX_DELETE_ITEMS_QUESTION"), "projects.delete");
        JToolBarHelper::divider();
        JToolBarHelper::custom('projects.backToDashboard', "dashboard", "", JText::_("COM_ITPTRANSIFEX_BACK_DASHBOARD"), false);
        
    }
    
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() {
	    
		$this->document->setTitle(JText::_('COM_ITPTRANSIFEX_PROJECTS_MANAGER'));

		// Load language string in JavaScript
		JText::script('COM_ITPTRANSIFEX_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
		
		// Scripts
		JHtml::_('behavior.multiselect');
		JHtml::_('formbehavior.chosen', 'select');
		JHtml::_('bootstrap.tooltip');
		
		JHtml::_('itprism.ui.joomla_list');
		
		$this->document->addScript('../media/'.$this->option.'/js/admin/'.JString::strtolower($this->getName()).'.js');
	}
    
}