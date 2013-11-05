<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

jimport('itprism.controller.admin');

/**
 * ItpTransifex Packages Controller
 *
 * @package     ItpTransifex
 * @subpackage  Components
  */
class ItpTransifexControllerPackages extends ITPrismControllerAdmin {

    /**
     * Proxy for getModel.
     * @since   1.6
     */
    public function getModel($name = 'Package', $prefix = 'ItpTransifexModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function update() {
        
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        // Get form data 
        $pks   = $app->input->post->get('cid', array(), 'array');
        $model = $this->getModel();
        /** @var $model ItpTransifexModelProjects **/
        
        $redirectOptions = array(
            "view" => $this->view_list
        );
        
        JArrayHelper::toInteger($pks);
        
        // Check for validation errors.
        if (empty($pks)) {
            $this->displayWarning(JText::_("COM_ITPTRANSIFEX_INVALID_ITEM"), $redirectOptions);
            return;
        }
        
        $params = JComponentHelper::getParams($this->option);
        
        $options = array(
          "username" => $params->get("username"),      
          "password" => $params->get("password"),      
          "url"      => $params->get("api_url")."project",      
        );
        
        try {
            
            $model->synchronize($options, $pks);
            
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }
        
        $this->displayMessage(JText::plural('COM_ITPTRANSIFEX_PROJECTS_SYNCHRONIZED', count(pks)), $redirectOptions);
    }
    
    /**
     * Remove record from packages map.
     * 
     * @param JModelLegacy $model
     * @param array $cid
     */
    protected function postDeleteHook(JModelLegacy $model, $cid = null) {
        
        try {
            $model->removeResourcesFromMap($cid);
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }
        
    }
    
}