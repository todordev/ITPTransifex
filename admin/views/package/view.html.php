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

class ItpTransifexViewPackage extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;
    protected $item;
    protected $form;

    protected $items;

    protected $documentTitle;
    protected $option;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');
        $this->state = $this->get('State');

        jimport("itptransifex.resources");
        $this->items = new ItpTransifexResources(JFactory::getDbo());

        $this->items->loadByPackageId($this->item->id);

        // Prepare actions, behaviors, scripts and document
        $this->addToolbar();
        $this->setDocument();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);

        $this->documentTitle = JText::_('COM_ITPTRANSIFEX_EDIT_PACKAGE');

        JToolBarHelper::title($this->documentTitle);

        JToolBarHelper::apply('package.apply');
        JToolBarHelper::save('package.save');

        if (!$isNew) {
            JToolBarHelper::cancel('package.cancel', 'JTOOLBAR_CANCEL');
        } else {
            JToolBarHelper::cancel('package.cancel', 'JTOOLBAR_CLOSE');
        }
    }

    /**
     * Method to set up the document properties.
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle($this->documentTitle);

        // Load language string in JavaScript
        JText::script('COM_ITPTRANSIFEX_DELETE_ITEM_QUESTION');
        JText::script('COM_ITPTRANSIFEX_ERROR_CANNOT_ADD_RESOURCE');
        JText::script('COM_ITPTRANSIFEX_FAIL');

        // Add scripts
        JHtml::_('behavior.formvalidation');
        JHtml::_('bootstrap.tooltip');

        JHtml::_('itprism.ui.pnotify');
        JHtml::_('itprism.ui.joomla_helper');
        JHtml::_('itprism.ui.bootstrap_typeahead');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . strtolower($this->getName()) . '.js');
    }
}
