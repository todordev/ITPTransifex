<?php
/**
 * @package      ITPTransifex
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
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

        // Load resource.
        $options = array(
            "package_id" => $packageId
        );
        $this->items = new Transifex\Resources(JFactory::getDbo());
        $this->items->load($options);

        $package = new Transifex\Package(JFactory::getDbo());
        $package->load($packageId);

        $this->projectId = $package->getProjectId();

        $this->setLayout("preview");

        parent::display($tpl);
    }
}
