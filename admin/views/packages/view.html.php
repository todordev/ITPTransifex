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

class ItpTransifexViewPackages extends JViewLegacy
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
    protected $pagination;

    protected $numberOfResources;
    protected $languages;

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

        // Get the number of project resources
        $this->numberOfResources = $this->getNumberOfResources($this->items);

        jimport("itptransifex.languages");
        $languages = new ItpTransifexLanguages(JFactory::getDbo());
        $languages->load();

        $this->languages = $languages->toOptions();

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

    protected function getNumberOfResources($items)
    {
        $ids = array();
        foreach ($items as $item) {
            $ids[] = $item->id;
        }

        jimport("itptransifex.packages");
        $packages = new ItpTransifexPackages(JFactory::getDbo());
        return $packages->getNumberOfResources($ids);
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
            'c.name' => JText::_('COM_ITPTRANSIFEX_LANGUAGE'),
            'b.name' => JText::_('COM_ITPTRANSIFEX_PROJECT'),
            'a.id'   => JText::_('JGRID_HEADING_ID')
        );
    }

    /**
     * Add a menu on the sidebar of page
     */
    protected function addSidebar()
    {
        JHtmlSidebar::setAction('index.php?option=' . $this->option . '&view=' . $this->getName());

        jimport("itptransifex.filters");
        $fitlers = new ItpTransifexFilters(JFactory::getDbo());

        JHtmlSidebar::addFilter(
            JText::_('COM_ITPTRANSIFEX_SELECT_PROJECT'),
            'filter_project',
            JHtml::_('select.options', $fitlers->getProjects(), 'value', 'text', $this->state->get('filter.project'), true)
        );

        JHtmlSidebar::addFilter(
            JText::_('COM_ITPTRANSIFEX_SELECT_LANGUAGE'),
            'filter_language',
            JHtml::_('select.options', $fitlers->getLanguages("code"), 'value', 'text', $this->state->get('filter.language'), true)
        );

        JHtmlSidebar::addFilter(
            JText::_('COM_ITPTRANSIFEX_SELECT_TYPE'),
            'filter_type',
            JHtml::_('select.options', $fitlers->getResourceTypes(), 'value', 'text', $this->state->get('filter.type'), true)
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
        JToolBarHelper::title(JText::_('COM_ITPTRANSIFEX_PACKAGES_MANAGER'));
        JToolBarHelper::editList('package.edit');
        JToolBarHelper::divider();
        JToolBarHelper::deleteList(JText::_("COM_ITPTRANSIFEX_DELETE_ITEMS_QUESTION"), "packages.delete");
        JToolBarHelper::divider();

        JToolBarHelper::custom("package.download", 'download', null, JText::_("COM_ITPTRANSIFEX_DOWNLOAD"));
        JToolBarHelper::divider();

        // Get the toolbar object instance
        $bar = JToolBar::getInstance('toolbar');

        $layoutData = array(
            'title' => JText::_('JTOOLBAR_BATCH')
        );

        // Instantiate a new JLayoutFile instance and render the batch button
        $layout = new JLayoutFile('joomla.toolbar.batch');
        $html = $layout->render($layoutData);
        $bar->appendButton('Custom', $html, 'batch');

        JToolBarHelper::divider();
        JToolBarHelper::custom('packages.backToDashboard', "dashboard", "", JText::_("COM_ITPTRANSIFEX_BACK_DASHBOARD"), false);
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_ITPTRANSIFEX_PACKAGES_MANAGER'));

        // Load language string in JavaScript
        JText::script('COM_ITPTRANSIFEX_PACKAGES_NOT_SELECTED');

        // Scripts
        JHtml::_('behavior.multiselect');
        JHtml::_('formbehavior.chosen', 'select');
        JHtml::_('bootstrap.tooltip');
        JHtml::_('bootstrap.modal', 'collapseModal');

        JHtml::_('itprism.ui.pnotify');
        JHtml::_('itprism.ui.joomla_list');
        JHtml::_('itprism.ui.joomla_helper');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . JString::strtolower($this->getName()) . '.js');
    }
}
