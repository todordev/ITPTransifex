<?php
/**
 * @package      ITPTransifex
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

class ItpTransifexViewResources extends JViewLegacy
{
    protected $projectId;
    protected $items;

    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $packageId = $app->input->get->get("package_id");

        // Load resource
        jimport("itptransifex.resources");
        $this->items = new ItpTransifexResources(JFactory::getDbo());
        $this->items->loadByPackageId($packageId);

        jimport("itptransifex.package");
        $package = new ItpTransifexPackage(JFactory::getDbo());
        $package->load($packageId);

        $this->projectId = $package->getProjectId();

        $this->setLayout("preview");

        parent::display($tpl);
    }
}
