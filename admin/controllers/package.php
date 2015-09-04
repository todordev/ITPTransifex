<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * ItpTransifex package controller class.
 *
 * @package       ITPTransifex
 * @subpackage    Components
 * @since         1.6
 */
class ItpTransifexControllerPackage extends Prism\Controller\Form\Backend
{
    public function getModel($name = 'Package', $prefix = 'ItpTransifexModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
    public function save($key = null, $urlVar = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        // Get form data
        $data   = $app->input->post->get('jform', array(), 'array');
        $itemId = Joomla\Utilities\ArrayHelper::getValue($data, "id");

        $redirectOptions = array(
            "task" => $this->getTask(),
            "id"   => $itemId
        );

        $model = $this->getModel();
        /** @var $model ItpTransifexModelPackage */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new Exception(JText::_("COM_ITPTRANSIFEX_ERROR_FORM_CANNOT_BE_LOADED"));
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

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_ITPTRANSIFEX_PACKAGE_SAVED'), $redirectOptions);
    }

    public function download()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $ids = $app->input->get("cid", array(), "array");

        Joomla\Utilities\ArrayHelper::toInteger($ids);
        $ids = array_filter($ids);

        if (!$ids) {
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_INVALID_PACKAGE'));
        }

        // Get package ID if I have to create a one file.
        $packageId = null;
        if (1 == count($ids)) {
            $packageId = (int)array_pop($ids);

            // Check for validation errors.
            if (!$packageId) {
                throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_INVALID_PACKAGE'));
            }
        }

        $params         = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        $includeLanguageName = $params->get("include_lang_name", 1);

        $serviceOptions = array(
            "username" => $params->get("username"),
            "password" => $params->get("password"),
            "url"      => $params->get("api_url")
        );

        try {

            $model = $this->getModel();
            /** @var $model ItpTransifexModelPackage */

            $model->setTransifexOptions($serviceOptions);

            if (!is_null($packageId)) { // Create a file for one package.
                $filePath = $model->preparePackage($packageId, $includeLanguageName);
            } else { // Create a file that contains many packages.
                $filePath = $model->prepareProjectPackage($ids, "UNZIPFIRST", $includeLanguageName);
            }

            if (!$filePath) {
                $filePath = $model->createErrorFile();
            }

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }

        $fileSize = filesize($filePath);
        $fileName = basename($filePath);

        $app->setHeader('Content-Type', 'application/octet-stream', true);
        $app->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $app->setHeader('Content-Transfer-Encoding', 'binary', true);
        $app->setHeader('Pragma', 'no-cache', true);
        $app->setHeader('Expires', '0', true);
        $app->setHeader('Content-Disposition', 'attachment; filename=' . $fileName, true);
        $app->setHeader('Content-Length', $fileSize, true);

        $doc = JFactory::getDocument();
        $doc->setMimeEncoding('application/octet-stream');

        $app->sendHeaders();

        echo file_get_contents($filePath);

        $app->close();
    }

    public function downloadProject()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $projectId= $this->input->getInt("id");
        $language = $this->input->getCmd("language");

        $filePath  = null;
        $ids       = array();

        // Check for validation errors.
        if (!$projectId) {
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_INVALID_PROJECT'));
        }

        $params         = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        $serviceOptions = array(
            "username" => $params->get("username"),
            "password" => $params->get("password"),
            "url"      => $params->get("api_url")
        );

        try {

            $model = $this->getModel();
            /** @var $model ItpTransifexModelPackage */

            $model->setTransifexOptions($serviceOptions);

            // Get project.
            $project    = new Transifex\Project(JFactory::getDbo());
            $project->load($projectId);

            // Get packages.
            $options = array("language" => $language);
            $packages   = $project->getPackages($options);

            foreach ($packages as $package) {
                $ids[] = $package["id"];
            }

            if (!empty($ids)) {
                $fileName = $project->getFileName();
                if (!$fileName) {
                    $fileName = "UNZIPFIRST";
                }

                $fileName .= "_".$language;
                $filePath = $model->prepareProjectPackage($ids, $fileName);
            }

            if (!$filePath) {
                $filePath = $model->createErrorFile();
            }

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }

        $fileSize = filesize($filePath);
        $fileName = basename($filePath);

        $app->setHeader('Content-Type', 'application/octet-stream', true);
        $app->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $app->setHeader('Content-Transfer-Encoding', 'binary', true);
        $app->setHeader('Pragma', 'no-cache', true);
        $app->setHeader('Expires', '0', true);
        $app->setHeader('Content-Disposition', 'attachment; filename=' . $fileName, true);
        $app->setHeader('Content-Length', $fileSize, true);

        $doc = JFactory::getDocument();
        $doc->setMimeEncoding('application/octet-stream');

        $app->sendHeaders();

        echo file_get_contents($filePath);

        $app->close();
    }
}
