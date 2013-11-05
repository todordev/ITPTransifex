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
 * ItpTransifex package controller
 *
 * @package     ItpTransifex
 * @subpackage  Components
  */
class ItpTransifexControllerPackage extends ITPrismControllerAdmin {

    /**
     * Proxy for getModel.
     * @since   1.6
     */
    public function getModel($name = 'Package', $prefix = 'ItpTransifexModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
    public function loadPackageData() {
    
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
    
        // Get form data
        $projectId      = $app->input->getInt('project_id');
        $langCode       = $app->input->get('lang_code');
        $resourcesIDs   = $app->input->get('cid', array(), 'array');

        JArrayHelper::toInteger($resourcesIDs);
        
        // Remove the item that contains value 0.
        $key = array_search(0, $resourcesIDs);
        if(false !== $key) {
            unset($resourcesIDs[$key]);
        }
        
        $model = $this->getModel();
        /** @var $model ItpTransifexModelPackage **/
    
        // Check for validation errors.
        if (empty($langCode)) {
        
            $response = array(
                "success" => false,
                "title"   => JText::_("COM_ITPTRANSIFEX_FAIL"),
                "text"    => JText::_("COM_ITPTRANSIFEX_INVALID_LANG_CODE")
            );
        
            echo json_encode($response);
            JFactory::getApplication()->close();
        
        }
        
        // Check for validation errors.
        if (empty($resourcesIDs)) {
            
            $response = array(
                "success" => false,
                "title"   => JText::_("COM_ITPTRANSIFEX_FAIL"),
                "text"    => JText::_("COM_ITPTRANSIFEX_ERROR_SELECT_RESOURCES")
            );
            
            echo json_encode($response);
            JFactory::getApplication()->close();
            
        }
    
        try {
    
            // Store data
            $idsString    = implode(",", $resourcesIDs).",".$langCode;
            $packageHash  = md5($idsString);
            
            $data         = $model->loadPackageData($projectId, $packageHash);
            
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }
    
        // Prepare response
        if(!empty($data)) {
            $response = array(
                "success" => true,
                "title"   => JText::_("COM_ITPTRANSIFEX_SUCCESS"),
                "text"    => JText::_("COM_ITPTRANSIFEX_PACKAGE_DATA_LOADED_SUCCESSFULLY"),
                "data"    => $data
            );
        } else {
            $response = array(
                "success" => false,
                "title"   => JText::_("COM_ITPTRANSIFEX_FAIL"),
                "text"    => JText::_("COM_ITPTRANSIFEX_PACKAGE_DATA_MISSING"),
            );
        }
        
        echo json_encode($response);
        JFactory::getApplication()->close();
        
    }
}