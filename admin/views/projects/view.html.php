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

class ItpTransifexViewProjects extends JViewLegacy
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
    protected $numberOfPackages;
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

        // Get projects IDs
        $ids = array();
        foreach ($this->items as $item) {
            $ids[] = $item->id;
        }

        // Get the number of project resources
        $projects = new Transifex\Project\Projects(JFactory::getDbo());
        $this->numberOfResources = $projects->getNumberOfResources($ids);

        // Get number of packages.
        $this->numberOfPackages = $projects->getNumberOfPackages($ids);

        $languages = new Transifex\Language\Languages(JFactory::getDbo());
        $languages->load();

        $this->languages = $languages->toOptions("code", "name");

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
        ItpTransifexHelper::addSubmenu($this->getName());
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
        JToolBarHelper::title(JText::_('COM_ITPTRANSIFEX_PROJECTS_MANAGER'));
        JToolBarHelper::addNew('project.add');
        JToolBarHelper::editList('project.edit');
        JToolbarHelper::divider();
        JToolbarHelper::publishList("projects.publish");
        JToolbarHelper::unpublishList("projects.unpublish");
        JToolBarHelper::divider();
        JToolBarHelper::deleteList(JText::_("COM_ITPTRANSIFEX_DELETE_ITEMS_QUESTION"), "projects.delete");
        JToolBarHelper::divider();
        JToolBarHelper::custom('projects.update', "refresh", "", JText::_("COM_ITPTRANSIFEX_UPDATE"), false);
        JToolBarHelper::custom("package.downloadProject", 'download', null, JText::_("COM_ITPTRANSIFEX_DOWNLOAD"));

        JToolBarHelper::divider();

        JToolBarHelper::divider();
        JToolBarHelper::custom('projects.backToDashboard', "dashboard", "", JText::_("COM_ITPTRANSIFEX_BACK_DASHBOARD"), false);
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_ITPTRANSIFEX_PROJECTS_MANAGER'));

        // Load language string in JavaScript
        JText::script('COM_ITPTRANSIFEX_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');

        // Scripts
        JHtml::_('behavior.multiselect');
        JHtml::_('formbehavior.chosen', '#sortTable, #directionTable, #limit');
        JHtml::_('bootstrap.tooltip');

        JHtml::_('Prism.ui.joomlaList');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . JString::strtolower($this->getName()) . '.js');
    }
}
