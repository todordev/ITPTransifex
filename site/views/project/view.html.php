<?php
/**
 * @package      ItpTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
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

    protected $items;
    protected $pagination;

    /**
     * @var Transifex\Project\Project
     */
    protected $project;

    protected $packagesNumber;
    protected $layoutData;
    protected $imageFolder;

    protected $option;

    protected $pageclass_sfx;

    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get current project
        $projectId = $app->input->getInt('id');
        if (!$projectId) {
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_INVALID_PROJECT'));
        }

        // Load project data.
        $this->project = new Transifex\Project\Project(JFactory::getDbo());
        $this->project->load($projectId);
        if (!$this->project->getId() or !$this->project->isPublished()) {
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_INVALID_PROJECT'));
        }

        $model = $this->getModel();
        /** @var $model ItpTransifexModelProject */

        // Initialise variables
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Get params
        $this->params = $this->state->get('params');
        /** @var  $this->params Joomla\Registry\Registry */

        // Get the folder with images
        $this->imageFolder = $this->params->get('images_directory', 'images/itptransifex');

        // Prepare layout data.
        $this->layoutData = new stdClass;
        $this->layoutData->params      = $this->params;
        $this->layoutData->project     = $this->project;
        $this->layoutData->cleanTitle  = $this->escape($this->project->getName());
        $this->layoutData->hTag        = (!$this->params->get('show_page_heading', 1)) ? 'h1' : 'h2';
        $this->layoutData->imageWidth  = $this->params->get('image_width', '200');
        $this->layoutData->imageHeight = $this->params->get('image_height', '200');
        $this->layoutData->imageFolder = $this->imageFolder;

        $this->packagesNumber = $model->getPackagesNumber($projectId);

        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document
     */
    protected function prepareDocument()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Prepare page suffix
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Prepare page heading
        $this->preparePageHeading();

        // Prepare page heading
        $this->preparePageTitle();

        // Meta Description
        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        // Meta keywords
        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        }

        // Breadcrumb
        $pathway           = $app->getPathway();
        $currentBreadcrumb = JHtmlString::truncate($this->project->getName(), 64);
        $pathway->addItem($currentBreadcrumb, '');

        JHtml::_('behavior.core');
        JHtml::_('bootstrap.framework');
    }

    private function preparePageHeading()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menus = $app->getMenu();
        $menu  = $menus->getActive();

        $defaultPageHeading = JText::sprintf('COM_ITPTRANSIFEX_PROJECT_DEFAULT_PAGE_HEADING', $this->project->getName());

        // Prepare page heading
        if (!$menu) {
            $this->params->def('page_heading', $defaultPageHeading);
        } else {
            $title = array_key_exists('id', $menu->query) ? $this->params->get('page_title', $menu->title) : $defaultPageHeading;
            $this->params->def('page_heading', $title);
        }
    }

    private function preparePageTitle()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menus = $app->getMenu();
        $menu  = $menus->getActive();

        $defaultTitle = JText::sprintf('COM_ITPTRANSIFEX_PROJECT_DEFAULT_PAGE_TITLE', $this->project->getName());

        // Prepare page title
        if (!$menu) {
            $title = $defaultTitle;
        } else {
            $title = array_key_exists('id', $menu->query) ? $this->params->get('page_title', $menu->title) : $defaultTitle;
        }

        // Add title before or after Site Name
        if (!$title) {
            $title = $app->get('sitename');
        } elseif ((int)$app->get('sitename_pagetitles', 0) === 1) {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ((int)$app->get('sitename_pagetitles', 0) === 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);
    }
}
