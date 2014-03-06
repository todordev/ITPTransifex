<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
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
 * @since		 1.6
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
    public function save($key = null, $urlVar = null) {
        
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
        
        if (!$form) {
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
    
    public function download() {
    
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
    
        $ids        = $app->input->get("cid", array(), "array");
    
        $packageId  = (int)array_pop($ids);
    
        // Check for validation errors.
        if (!$packageId) {
            throw new Exception(JText::_('COM_ITPTRANSIFEX_INVALID_PACKAGE'));
        }
    
        $params = JComponentHelper::getParams($this->option);
        $serviceOptions = array(
            "username" => $params->get("username"),
            "password" => $params->get("password"),
            "url"      => $params->get("api_url")
        );
    
        try {
    
            $model    = $this->getModel();
            /** @var $model ItpTransifexModelPackage **/
            
            $model->setTransifexOptions($serviceOptions);
            
            $filePath = $model->prepareFiles($packageId);
            
            if(!$filePath) {
                $filePath = $model->createErrorFile();
            }
    
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }
    
        $filesize = filesize($filePath);
        $fileName = JFile::getName($filePath);
    
        JResponse::setHeader('Content-Type', 'application/octet-stream', true);
        JResponse::setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        JResponse::setHeader('Content-Transfer-Encoding', 'binary', true);
        JResponse::setHeader('Pragma', 'no-cache', true);
        JResponse::setHeader('Expires', '0', true);
        JResponse::setHeader('Content-Disposition', 'attachment; filename='.$fileName, true);
        JResponse::setHeader('Content-Length', $filesize, true);
        
        $doc = JFactory::getDocument();
        $doc->setMimeEncoding('application/octet-stream');
        
        JResponse::sendHeaders();
        
        echo file_get_contents($filePath);
        
        JFactory::getApplication()->close();
    }
    
}