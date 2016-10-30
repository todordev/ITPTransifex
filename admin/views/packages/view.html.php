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

class ItpTransifexViewPackages extends JViewLegacy
{
    /**
     * @var JApplicationAdministrator
     */
    public $app;

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
    protected $projectId;
    protected $project;

    protected $sidebar;

    public function display($tpl = null)
    {
        $this->app        = JFactory::getApplication();
        $this->option     = $this->app->input->get('option');
        
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Get the number of project resources
        $this->numberOfResources = $this->getNumberOfResources($this->items);

        $languages = new Transifex\Language\Languages(JFactory::getDbo());
        $languages->load();

        $this->languages = $languages->toOptions('locale', 'name');

        // Get project.
        $this->projectId  = $this->state->get('filter.project');
        if (!$this->projectId) {
            $this->app->redirect(JRoute::_('index.php?option=com_itptransifex&view=projects', false));
            return;
        }

        $this->project = new Transifex\Project\Project(JFactory::getDbo());
        $this->project->load($this->projectId);

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

        $packages = new Transifex\Package\Packages(JFactory::getDbo());
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
        $this->saveOrder = (strcmp($this->listOrder, 'a.ordering') === 0);

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
        ItpTransifexHelper::addSubmenu($this->getName());

        JHtmlSidebar::setAction('index.php?option=' . $this->option . '&view=' . $this->getName());

        $filters = new Transifex\Filter\Filters(JFactory::getDbo());

        JHtmlSidebar::addFilter(
            JText::_('COM_ITPTRANSIFEX_SELECT_PROJECT'),
            'filter_project',
            JHtml::_('select.options', $filters->getProjects(), 'value', 'text', $this->state->get('filter.project'), true)
        );

        JHtmlSidebar::addFilter(
            JText::_('COM_ITPTRANSIFEX_SELECT_LANGUAGE'),
            'filter_language',
            JHtml::_('select.options', $filters->getPackageLanguages($this->projectId), 'value', 'text', $this->state->get('filter.language'), true)
        );

        JHtmlSidebar::addFilter(
            JText::_('COM_ITPTRANSIFEX_SELECT_SECOND_LANGUAGE'),
            'filter_language2',
            JHtml::_('select.options', $filters->getPackageLanguages($this->projectId), 'value', 'text', $this->state->get('filter.language2'), true)
        );

        JHtmlSidebar::addFilter(
            JText::_('COM_ITPTRANSIFEX_SELECT_TYPE'),
            'filter_type',
            JHtml::_('select.options', $filters->getResourceTypes(), 'value', 'text', $this->state->get('filter.type'), true)
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
        if (!empty($this->project) and $this->project->getId()) {
            JToolbarHelper::title(JText::sprintf('COM_ITPTRANSIFEX_PACKAGES_MANAGER_S', $this->escape($this->project->getName())));
        } else {
            JToolbarHelper::title(JText::_('COM_ITPTRANSIFEX_PACKAGES_MANAGER'));
        }

        JToolbarHelper::editList('package.edit');
        JToolbarHelper::divider();
        JToolbarHelper::deleteList(JText::_('COM_ITPTRANSIFEX_DELETE_ITEMS_QUESTION'), 'packages.delete');
        JToolbarHelper::divider();

        JToolbarHelper::custom('package.download', 'download', null, JText::_('COM_ITPTRANSIFEX_DOWNLOAD'));
        JToolbarHelper::divider();

        // Get the toolbar object instance
        $bar = JToolbar::getInstance('toolbar');

        $layoutData = array(
            'title' => JText::_('JTOOLBAR_BATCH')
        );

        // Instantiate a new JLayoutFile instance and render the batch button
        $layout = new JLayoutFile('joomla.toolbar.batch');
        $html = $layout->render($layoutData);
        $bar->appendButton('Custom', $html, 'batch');

        JToolbarHelper::divider();
        JToolbarHelper::custom('packages.backToDashboard', 'dashboard', '', JText::_('COM_ITPTRANSIFEX_BACK_DASHBOARD'), false);
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

        JHtml::_('Prism.ui.pnotify');
        JHtml::_('Prism.ui.joomlaList');
        JHtml::_('Prism.ui.joomlaHelper');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . strtolower($this->getName()) . '.js');
    }
}
