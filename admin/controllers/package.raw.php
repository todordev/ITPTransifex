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
    
    /**
     * This method creates a language package.
     */
    public function create() {
    
        jimport("itprism.response.json");
        $response = new ITPrismResponseJson();
        
        // Get form data
        $projectId      = $this->input->getInt('project_id');
        $resourcesIDs   = $this->input->get('resource', array(), 'array');
        $data           = $this->input->post->get('jform', array(), 'array');
    
        $model = $this->getModel();
        /** @var $model ItpTransifexModelPackage **/
    
        JArrayHelper::toInteger($resourcesIDs);
    
        // Check for validation errors.
        if (!$resourcesIDs) {
            
            $response
                ->setTitle(JText::_("COM_ITPTRANSIFEX_FAIL"))
                ->setText(JText::_("COM_ITPTRANSIFEX_INVALID_RESOURCES"))
                ->failure();
                
            echo $response;
            
            JFactory::getApplication()->close();
        }
    
        $form = $model->getForm($data, false);
        /** @var $form JForm **/
        
        if (!$form) {
            throw new Exception($model->getError(), 500);
        }
        
        // Validate form data
        $validData = $model->validate($form, $data);
        
        // Check for validation errors.
        if ($validData === false) {
            
            $errors = $form->getErrors();
            foreach($errors as $error) {
                $messages[] = $error->getMessage();
            }
            
            $response
                ->setTitle(JText::_("COM_ITPTRANSIFEX_FAIL"))
                ->setText(implode("\n", $messages))
                ->failure();
            
            echo $response;
            
            JFactory::getApplication()->close();
        }
        
        try {
    
            // Store data
            $idsString    = implode(",", $resourcesIDs).",".$validData["language"];
            $packageHash  = md5($idsString);
            
            $validData["project_id"] = $projectId;
            $validData["hash"]       = $packageHash;

            $packageId = $model->save($validData);
            $model->saveResourcesIds($packageId, $resourcesIDs);
    
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }
    
        $response
            ->setTitle(JText::_("COM_ITPTRANSIFEX_SUCCESS"))
            ->setText(JText::_("COM_ITPTRANSIFEX_PACKAGE_CREATED"))
            ->success();
        
        echo $response;
        JFactory::getApplication()->close();
        
    }
    
}