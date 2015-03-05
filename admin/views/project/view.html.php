<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

class ItpTransifexViewProject extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $params;

    protected $item;
    protected $form;
    protected $imagesUrl;

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

        // Prepare parameters
        $this->params = $this->state->get("params");
        /** @var $this->params Joomla\Registry\Registry */

        $imagesFolder    = $this->params->get("images_directory", "images/itptransifex");
        $this->imagesUrl = JUri::root() . $imagesFolder;

        // Prepare actions, behaviors, script and document
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

        $this->documentTitle = $isNew ? JText::_('COM_ITPTRANSIFEX_NEW_PROJECT')
            : JText::_('COM_ITPTRANSIFEX_EDIT_PROJECT');

        JToolBarHelper::title($this->documentTitle);

        JToolBarHelper::apply('project.apply');
        JToolBarHelper::save('project.save');

        if (!$isNew) {
            JToolBarHelper::cancel('project.cancel', 'JTOOLBAR_CANCEL');
        } else {
            JToolBarHelper::cancel('project.cancel', 'JTOOLBAR_CLOSE');
        }
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle($this->documentTitle);

        // Add scripts
        JHtml::_('behavior.formvalidation');
        JHtml::_('bootstrap.tooltip');

        $this->document->addScript(JURI::root() . 'media/' . $this->option . '/js/admin/' . strtolower($this->getName()) . '.js');
    }
}
