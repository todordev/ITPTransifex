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

jimport('itprism.controller.form.backend');

/**
 * ItpTransifex package controller class.
 *
 * @package      ITPTransifex
 * @subpackage   Components
 * @since		1.6
 */
class ItpTransifexControllerPackage extends ITPrismControllerFormBackend {

    /**
     * Proxy for getModel.
     * @since   1.6
     */
    public function getModel($name = 'Package', $prefix = 'ItpTransifexModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * Save an item
     */
    public function save() {
        
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        // Get form data 
        $data = $app->input->post->get('jform', array(), 'array');
        $itemId = JArrayHelper::getValue($data, "id");
        
        $redirectOptions = array(
            "task" => $this->getTask(), 
            "id"   => $itemId
        );
        
        $model = $this->getModel();
        /** @var $model ItpTransifexModelPackage **/
        
        $form = $model->getForm($data, false);
        /** @var $form JForm **/
        
        if (! $form) {
            throw new Exception($model->getError(), 500);
        }
        
        // Validate form data
        $validData = $model->validate($form, $data);
        
        // Check for validation errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }
        
        try {
            
            $itemId = $model->save($validData);
            
            $redirectOptions["id"] = $itemId;
             
        } catch ( Exception $e ) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }
        
        $this->displayMessage(JText::_('COM_ITPTRANSIFEX_PACKAGE_SAVED'), $redirectOptions);
    }
    
    /**
     * This method  downloads the language files
     * from Transifex and creates a language package.
     *
     * @throws Exception
     */
    public function create() {
    
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
    
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
    
        // Get form data
        $projectId      = $app->input->getInt('project_id');
        $resourcesIDs   = $app->input->get('cid', array(), 'array');
        $storeData      = $app->input->getBool("store_data");
    
        $model = $this->getModel();
        /** @var $model ItpTransifexModelPackage **/
    
        $redirectOptions = array(
            "view" => "resources"
        );
    
        JArrayHelper::toInteger($resourcesIDs);
    
        // Check for validation errors.
        if (empty($resourcesIDs)) {
            $this->displayWarning(JText::_("COM_ITPTRANSIFEX_INVALID_ITEM"), $redirectOptions);
            return;
        }
    
        try {
    
            $date = new JDate();
    
            $data = array(
                "name"           => JString::trim($app->input->getString("name")),
                "filename"       => JString::trim($app->input->getString("filename")),
                "description"    => $app->input->getString("description"),
                "version"        => $app->input->getString("version", "1.0"),
                "creation_date"  => $date->format(JText::_("DATE_FORMAT_LC3")),
                "lang_code"      => $app->input->getString("lang_code"),
            );
    
            if(!$data["lang_code"]) {
                $this->displayWarning(JText::_("COM_ITPTRANSIFEX_INVALID_LANG_CODE"), $redirectOptions);
                return;
            }
    
            if(!$data["filename"]) {
                $this->displayWarning(JText::_("COM_ITPTRANSIFEX_INVALID_FILENAME"), $redirectOptions);
                return;
            }
    
            $params = JComponentHelper::getParams($this->option);
            $serviceOptions = array(
                "username"  => $params->get("username"),
                "password"  => $params->get("password"),
                "url"       => $params->get("api_url")."project"
            );
    
            $fileURI = $model->prepareFiles($projectId, $resourcesIDs, $data, $serviceOptions);
            $app->setUserState("file_download", $fileURI);
    
            if($storeData) {
    
                $modelPackage = JModelLegacy::getInstance("Package", "ItpTransifexModel", array('ignore_request' => true));
    
                // Store data
                $idsString    = implode(",", $resourcesIDs).",".$data["lang_code"];
                $packageHash  = md5($idsString);
    
                $data["project_id"] = $projectId;
                $data["hash"]       = $packageHash;
                $data["resources"]  = $resourcesIDs;
    
                $packageId = $modelPackage->save($data);
                $modelPackage->saveResourcesIds($packageId, $resourcesIDs);
                
            }
    
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }
    
        $this->displayMessage(JText::_('COM_ITPTRANSIFEX_PACKAGE_CREATED'), $redirectOptions);
    }
    
}