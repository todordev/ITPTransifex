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
class ItpTransifexControllerPackage extends ITPrismControllerAdmin
{
    /**
     * Proxy for getModel.
     * @since   1.6
     */
    public function getModel($name = 'Package', $prefix = 'ItpTransifexModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * This method creates a language package.
     */
    public function create()
    {
        jimport("itprism.response.json");
        $response = new ITPrismResponseJson();

        // Get form data
        $projectId    = $this->input->getInt('project_id');
        $resourcesIDs = $this->input->get('resource', array(), 'array');
        $data         = $this->input->post->get('jform', array(), 'array');

        $model = $this->getModel();
        /** @var $model ItpTransifexModelPackage */

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
        /** @var $form JForm * */

        if (!$form) {
            throw new Exception(JText::_("COM_ITPTRANSIFEX_ERROR_FORM_CANNOT_BE_LOADED"));
        }

        // Validate form data
        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if ($validData === false) {

            $messages = array();

            $errors = $form->getErrors();
            /** @var $error Exception */

            foreach ($errors as $error) {
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

            $validData["project_id"] = $projectId;

            $packageId = $model->save($validData);
            $model->saveResourcesIds($packageId, $resourcesIDs);

        } catch (Exception $e) {

            JLog::add($e->getMessage());

            $response
                ->setTitle(JText::_("COM_ITPTRANSIFEX_FAIL"))
                ->setText($e->getMessage())
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        $response
            ->setTitle(JText::_("COM_ITPTRANSIFEX_SUCCESS"))
            ->setText(JText::_("COM_ITPTRANSIFEX_PACKAGE_CREATED"))
            ->success();

        echo $response;
        JFactory::getApplication()->close();

    }

    /**
     * This method remove a resource from a package.
     */
    public function removeResource()
    {
        jimport("itprism.response.json");
        $response = new ITPrismResponseJson();

        $packageId    = $this->input->getInt('pid');
        $resourceId   = $this->input->getInt('rid');

        $model = $this->getModel();
        /** @var $model ItpTransifexModelPackage */

        // Check for validation errors.
        if (!$packageId or !$resourceId) {

            $response
                ->setTitle(JText::_("COM_ITPTRANSIFEX_FAIL"))
                ->setText(JText::_("COM_ITPTRANSIFEX_INVALID_RESOURCES"))
                ->failure();

            echo $response;

            JFactory::getApplication()->close();
        }

        try {

            $model->removeResource($packageId, $resourceId);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }

        $response
            ->setTitle(JText::_("COM_ITPTRANSIFEX_SUCCESS"))
            ->setText(JText::_("COM_ITPTRANSIFEX_RESOURCE_REMOVED"))
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }

    /**
     * Load resources from database.
     *
     * @throws Exception
     * @return  void
     */
    public function loadResources()
    {
        // Get the input
        $query = $this->input->get->get('query', "", 'string');

        jimport('itprism.response.json');
        $response = new ITPrismResponseJson();

        // Get the model
        $model = $this->getModel();
        /** @var $model ItpTransifexModelPackage */

        try {
            $data = $model->getResources($query);
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }
        
        $response
            ->setData($data)
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }

    /**
     * Add a new resource from database.
     *
     * @throws Exception
     * @return  void
     */
    public function addResource()
    {
        // Get the input
        $packageId  = $this->input->getInt('pid');
        $resourceId = $this->input->getInt('rid');

        // Get the model
        $model = $this->getModel();
        /** @var $model ItpTransifexModelPackage */

        try {
            $success = $model->storeResource($packageId, $resourceId);
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }

        jimport("itptransifex.resource");
        $resource = new ItpTransifexResource(JFactory::getDbo());
        $resource->load($resourceId);

        if ($success and $resource->getId()) {

            $response = '
    <tr id="resource-id'.$resourceId.'">
        <td class="nowrap">
            '.$resource->getName().'
            <div class="small">
                '. JText::sprintf("COM_ITPTRANSIFEX_ALIAS_S", $resource->getAlias()) .'
            </div>
        </td>
        <td>
            <a href="'.JRoute::_("index.php?option=com_itptransifex&task=package.removeResource&format=raw").'"
               data-rid="'.$resourceId.'"
               data-pid="'.$packageId.'"
               class="btn btn-danger itptfx-btn-remove"
                >
                <i class="icon-trash"></i>
                '.JText::_("COM_ITPTRANSIFEX_REMOVE").'
            </a>
        </td>
    </tr>';

            echo $response;
        }

        JFactory::getApplication()->close();
    }

    public function batch()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        jimport("itprism.response.json");
        $response = new ITPrismResponseJson();

        // Get the input
        $packagesIds  = $this->input->post->getString('ids');
        $packagesIds  = explode(",", $packagesIds);

        $action       = $this->input->post->get('action');

        // Get the model
        $model = $this->getModel();
        /** @var $model ItpTransifexModelPackage */

        // Check for selected packages.
        if (!$packagesIds) {
            $response
                ->setTitle(JText::_("COM_ITPTRANSIFEX_FAIL"))
                ->setText(JText::_("COM_ITPTRANSIFEX_PACKAGES_NOT_SELECTED"))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }


        try {

            switch ($action) {
                case "copy":

                    $language     = $this->input->post->get('language');

                    // Check for valid language.
                    if (!$language) {
                        $response
                            ->setTitle(JText::_("COM_ITPTRANSIFEX_FAIL"))
                            ->setText(JText::_("COM_ITPTRANSIFEX_LANGUAGE_NOT_SELECTED"))
                            ->failure();

                        echo $response;
                        JFactory::getApplication()->close();
                    }

                    $model->copyPackages($packagesIds, $language);

                    $response
                        ->setTitle(JText::_("COM_ITPTRANSIFEX_SUCCESS"))
                        ->setText(JText::_("COM_ITPTRANSIFEX_PACKAGES_COPIED_SUCCESSFULLY"))
                        ->success();

                    break;

                case "replace_string":

                    $search     = $this->input->post->getString('search_string');
                    $replace    = $this->input->post->getString('replace_string');

                    if (!$search or !$replace) {
                        $response
                            ->setTitle(JText::_("COM_ITPTRANSIFEX_FAIL"))
                            ->setText(JText::_("COM_ITPTRANSIFEX_SEARCH_REPLACE_MISSING"))
                            ->failure();

                        echo $response;
                        JFactory::getApplication()->close();
                    }

                    $model->replaceText($packagesIds, $search, $replace);

                    $response
                        ->setTitle(JText::_("COM_ITPTRANSIFEX_SUCCESS"))
                        ->setText(JText::_("COM_ITPTRANSIFEX_PACKAGES_REPLACED_TEXT_SUCCESSFULLY"))
                        ->success();

                    break;

                case "change_version":
                    $newVersion = $this->input->post->get('version', 0, "float");

                    if (!$newVersion) {
                        $response
                            ->setTitle(JText::_("COM_ITPTRANSIFEX_FAIL"))
                            ->setText(JText::_("COM_ITPTRANSIFEX_VERSION_NOT_SPECIFIED"))
                            ->failure();

                        echo $response;
                        JFactory::getApplication()->close();
                    }

                    $model->changeVersion($packagesIds, $newVersion);

                    $response
                        ->setTitle(JText::_("COM_ITPTRANSIFEX_SUCCESS"))
                        ->setText(JText::_("COM_ITPTRANSIFEX_PACKAGES_VERSION_CHANGED_SUCCESSFULLY"))
                        ->success();
                    break;
            }

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }

        echo $response;
        JFactory::getApplication()->close();
    }
}
