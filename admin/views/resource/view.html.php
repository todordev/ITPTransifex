<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class ItpTransifexViewResource extends JViewLegacy
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

    protected $documentTitle;
    protected $option;

    public function display($tpl = null)
    {
        $this->option = JFactory::getApplication()->input->get('option');
        
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');
        $this->state = $this->get('State');

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
        $isNew = ((int)$this->item->id === 0);

        $this->documentTitle = JText::_('COM_ITPTRANSIFEX_EDIT_RESOURCE');

        JToolbarHelper::title($this->documentTitle);

        JToolbarHelper::apply('resource.apply');
        JToolbarHelper::save('resource.save');

        if (!$isNew) {
            JToolbarHelper::cancel('resource.cancel', 'JTOOLBAR_CANCEL');
        }
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        // Add behaviors
        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.formvalidation');

        $this->document->setTitle($this->documentTitle);

        // Add scripts
        $this->document->addScript(JUri::root() . 'media/' . $this->option . '/js/admin/' . strtolower($this->getName()) . '.js');
    }
}
