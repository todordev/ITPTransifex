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

    /**
     * @var Joomla\Registry\Registry
     */
    protected $params;

    /**
     * @var Transifex\Project
     */
    protected $project;

    protected $items = null;
    protected $pagination = null;

    protected $filterPaginationLimit;

    protected $displayPackagesNumber;
    protected $layoutsBasePath;
    protected $layoutData;

    /**
     * @var Transifex\Language
     */
    protected $language;

    protected $option;

    protected $pageclass_sfx;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->getCmd("option");

        $this->layoutsBasePath = JPath::clean(JPATH_COMPONENT_ADMINISTRATOR . "/layouts");
    }

    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get current project
        $projectId = $app->input->getInt("id");
        if (!$projectId) {
            throw new Exception(JText::_("COM_ITPTRANSIFEX_ERROR_INVALID_PROJECT"));
        }

        $languageCode = $app->input->getCmd("lang");
        if (!$languageCode) {
            throw new Exception(JText::_("COM_ITPTRANSIFEX_ERROR_INVALID_LANGUAGE"));
        }

        // Load project data.
        $this->project = new Transifex\Project(JFactory::getDbo());
        $this->project->load($projectId);
        if (!$this->project->getId() or !$this->project->isPublished()) {
            throw new Exception(JText::_("COM_ITPTRANSIFEX_ERROR_INVALID_PROJECT"));
        }

        // Load project data.
        $keys = array(
            "code" => $languageCode
        );
        $this->language = new Transifex\Language(JFactory::getDbo());
        $this->language->load($keys);
        if (!$this->language->getId()) {
            throw new Exception(JText::_("COM_ITPTRANSIFEX_ERROR_INVALID_LANGUAGE"));
        }

        // Initialise variables
        $this->state      = $this->get("State");
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Get params
        $this->params = $this->state->get("params");
        /** @var  $this->params Joomla\Registry\Registry */

        // Prepare layout data.
        $this->layoutData = array(
            "params"  => $this->params,
            "project" => $this->project,
            "clean_title" => $this->escape($this->project->getName()),
            "h_tag" => (!$this->params->get('show_page_heading', 1)) ? "h1" : "h2",
            "image_width" => $this->params->get("image_width", "200"),
            "image_height" => $this->params->get("image_height", "200"),
            "images_folder" => $this->params->get("images_directory", "images/itptransifex")
        );

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
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        // Breadcrumb
        $pathway           = $app->getPathWay();

        // Project name
        $projectBreadcrumb = JHtmlString::truncate($this->project->getName(), 64);
        $projectLink = ItpTransifexHelperRoute::getProjectRoute($this->project->getSlug());
        $pathway->addItem($projectBreadcrumb, $projectLink);

        $languageBreadcrumb = JHtmlString::truncate($this->language->getName(), 64);
        $pathway->addItem($languageBreadcrumb, '');

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

        $defaultPageHeading = JText::sprintf("COM_ITPTRANSIFEX_PACKAGES_DEFAULT_PAGE_HEADING", $this->project->getName(), $this->language->getName());

        // Prepare page heading
        if (!$menu) {
            $this->params->def('page_heading', $defaultPageHeading);
        } else {
            $title = (isset($menu->query["id"])) ? $this->params->get('page_title', $menu->title) : $defaultPageHeading;
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

        $defaultTitle = JText::sprintf("COM_ITPTRANSIFEX_PACKAGES_DEFAULT_PAGE_TITLE", $this->project->getName(), $this->language->getName());

        // Prepare page title
        if (!$menu) {
            $title = $defaultTitle;
        } else {
            $title = (isset($menu->query["id"])) ? $this->params->get('page_title', $menu->title) : $defaultTitle;
        }

        // Add title before or after Site Name
        if (!$title) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);
    }
}
