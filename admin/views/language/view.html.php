<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class ItpTransifexViewLanguage extends JViewLegacy {
    
    protected $state;
    protected $item;
    protected $form;
    
    protected $documentTitle;
    protected $option;
    
    public function __construct($config) {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }
    
    /**
     * Display the view
     */
    public function display($tpl = null){
        
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');
        $this->state = $this->get('State');
        
        // Prepare actions, behaviors, scritps and document
        $this->addToolbar();
        $this->setDocument();
        
        parent::display($tpl);
    }
    
    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar(){
        
        JFactory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);
        
        $this->documentTitle = $isNew ? JText::_('COM_ITPTRANSIFEX_NEW_LANGUAGE')
		                              : JText::_('COM_ITPTRANSIFEX_EDIT_LANGUAGE');
        
        JToolBarHelper::title($this->documentTitle);
		                             
        JToolBarHelper::apply('language.apply');
        JToolBarHelper::save('language.save');
    
        if(!$isNew){
            JToolBarHelper::cancel('language.cancel', 'JTOOLBAR_CANCEL');
        }else{
            JToolBarHelper::cancel('language.cancel', 'JTOOLBAR_CLOSE');
        }
        
    }
    
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() {
	    
		$this->document->setTitle($this->documentTitle);
        
		// Add scripts
		JHtml::_('behavior.formvalidation');
		JHtml::_('bootstrap.tooltip');
		
		$this->document->addScript(JURI::root() . 'media/'.$this->option.'/js/admin/'.strtolower($this->getName()).'.js');
        
	}

}