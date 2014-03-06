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

jimport('joomla.application.component.view');

class ItpTransifexViewResources extends JViewLegacy {
    
    protected $packageId;
    protected $items;
    
    public function display($tpl = null){
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        $this->packageId  = $app->input->get->get("package_id");
        
        jimport("itptransifex.resources");
        $this->items      = new ItpTransifexResources(JFactory::getDbo());
        
        $this->items->loadByPackageId($this->packageId);
        
        $this->setLayout("preview");
        
        parent::display($tpl);
    }
    
}
