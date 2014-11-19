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

class ItpTransifexViewResources extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;

    protected $items;
    protected $form;
    protected $pagination;

    protected $numberOfResources;
    protected $imagesFolder;
    protected $projectId;

    /**
     * @var ItpTransifexProject
     */
    protected $project;

    protected $option;
    protected $listOrder;
    protected $listDirn;
    protected $saveOrder;
    protected $saveOrderingUrl;

    protected $sortFields;

    protected $sidebar;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }

    public function display($tpl = null)
    {
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        $this->projectId = $this->state->get("project_id");

        jimport("itptransifex.project");
        $this->project = new ItpTransifexProject(JFactory::getDbo());
        $this->project->load($this->projectId);

        $model      = JModelLegacy::getInstance("Package", "ItpTransifexModel", $config = array('ignore_request' => true));
        $this->form = $model->getForm();

        // Add submenu
        ItpTransifexHelper::addSubmenu("resources");

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
    protected function prepareSorting()
    {
        // Prepare filters
        $this->listOrder = $this->escape($this->state->get('list.ordering'));
        $this->listDirn  = $this->escape($this->state->get('list.direction'));
        $this->saveOrder = (strcmp($this->listOrder, 'a.ordering') != 0) ? false : true;

        if ($this->saveOrder) {
            $this->saveOrderingUrl = 'index.php?option=' . $this->option . '&task=' . $this->getName() . '.saveOrderAjax&format=raw';
            JHtml::_('sortablelist.sortable', $this->getName() . 'List', 'adminForm', strtolower($this->listDirn), $this->saveOrderingUrl);
        }

        $this->sortFields = array(
            'a.name' => JText::_('COM_ITPTRANSIFEX_NAME'),
            'a.id'   => JText::_('JGRID_HEADING_ID')
        );
    }

    /**
     * Add a menu on the sidebar of page
     */
    protected function addSidebar()
    {
        JHtmlSidebar::setAction('index.php?option=' . $this->option . '&view=' . $this->getName());

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_PUBLISHED'),
            'filter_state',
            JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array("archived" => false)), 'value', 'text', $this->state->get('filter.state'), true)
        );

        // Prepare filter types
        $types = array(
            "site"  => "site",
            "admin" => "admin",
        );
        JHtmlSidebar::addFilter(
            JText::_('COM_ITPTRANSIFEX_SELECT_TYPE'),
            'filter_type',
            JHtml::_('select.options', $types, 'value', 'text', $this->state->get('filter.type'), true)
        );

        $this->sidebar = JHtmlSidebar::render();

    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        // Set toolbar items for the page
        JToolBarHelper::title(JText::sprintf('COM_ITPTRANSIFEX_RESOURCES_MANAGER_S', $this->escape($this->project->getName())));
        JToolBarHelper::custom('package.create', "plus", "", JText::_("COM_ITPTRANSIFEX_CREATE_PACKAGE"), false);
        JToolBarHelper::custom('resources.update', "refresh", "", JText::_("COM_ITPTRANSIFEX_UPDATE"), false);
        JToolBarHelper::divider();

        if ($this->state->get('filter.state') == -2) {
            JToolbarHelper::deleteList('', 'resources.delete', 'JTOOLBAR_EMPTY_TRASH');
        } else {
            JToolbarHelper::trash('resources.trash');
        }

        JToolBarHelper::divider();

        // Help button
        $bar = JToolBar::getInstance('toolbar');
        $bar->appendButton('Link', 'arrow-left-3', JText::_('COM_ITPTRANSIFEX_BACK_TO_PROJECTS'), "index.php?option=com_itptransifex&view=projects");

        JToolBarHelper::custom('projects.backToDashboard', "dashboard", "", JText::_("COM_ITPTRANSIFEX_BACK_DASHBOARD"), false);
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle(JText::sprintf('COM_ITPTRANSIFEX_RESOURCES_MANAGER_S', $this->escape($this->project->getName())));

        // Load language string in JavaScript
        JText::script('COM_ITPTRANSIFEX_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
        JText::script('COM_ITPTRANSIFEX_ENTER_FILENAME');
        JText::script('COM_ITPTRANSIFEX_EMPTY');
        JText::script('COM_ITPTRANSIFEX_SELECT_TYPE_EDITABLE');
        JText::script('COM_ITPTRANSIFEX_NOT_SELECTED');

        // Scripts
        JHtml::_('behavior.multiselect');
        JHtml::_('formbehavior.chosen', 'select');
        JHtml::_('bootstrap.tooltip');
        JHtml::_('itprism.ui.pnotify');
        JHtml::_('itprism.ui.bootstrap_editable');
        JHtml::_('itprism.ui.joomla_helper');
        JHtml::_('itprism.ui.joomla_list');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . JString::strtolower($this->getName()) . '.js');
    }
}
