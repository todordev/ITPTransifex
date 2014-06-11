<?php
/**
 * @package      ItpTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('itprism.controller.form.backend');

/**
 * ItpTransifex import controller.
 *
 * @package      ItpTransifex
 * @subpackage   Components
 */
class ItpTransifexControllerImport extends ITPrismControllerFormBackend
{
    /**
     * Proxy for getModel.
     * @since   1.6
     */
    public function getModel($name = 'Import', $prefix = 'ItpTransifexModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function project()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $data = $this->input->post->get('jform', array(), 'array');
        $file = $this->input->files->get('jform', array(), 'array');
        $data = array_merge($data, $file);

        $redirectOptions = array(
            "view" => "projects",
        );

        $model = $this->getModel();
        /** @var $model ItpTransifexModelImport */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new Exception(JText::_("COM_ITPTRANSIFEX_ERROR_FORM_CANNOT_BE_LOADED"), 500);
        }

        // Validate the form
        $validData = $model->validate($form, $data);

        // Check for errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);

            return;
        }

        $fileData = JArrayHelper::getValue($data, "data");
        if (empty($fileData) or empty($fileData["name"])) {
            $this->displayNotice(JText::_('COM_ITPTRANSIFEX_ERROR_FILE_CANT_BE_UPLOADED'), $redirectOptions);

            return;
        }

        try {

            jimport('joomla.filesystem.archive');
            jimport('itprism.file');
            jimport('itprism.file.uploader.local');
            jimport('itprism.file.validator.size');

            $destination = JPath::clean($app->get("tmp_path")) . DIRECTORY_SEPARATOR . JFile::makeSafe($fileData['name']);

            $file = new ITPrismFile();

            // Prepare size validator.
            $KB       = 1024 * 1024;
            $fileSize = (int)$this->input->server->get('CONTENT_LENGTH');

            $mediaParams   = JComponentHelper::getParams("com_media");
            /** @var $mediaParams Joomla\Registry\Registry */

            $uploadMaxSize = $mediaParams->get("upload_maxsize") * $KB;

            $sizeValidator = new ITPrismFileValidatorSize($fileSize, $uploadMaxSize);

            $file->addValidator($sizeValidator);

            // Validate the file
            $file->validate();

            // Prepare uploader object.
            $uploader = new ITPrismFileUploaderLocal($fileData);
            $uploader->setDestination($destination);

            // Upload the file
            $file->setUploader($uploader);
            $file->upload();

            $fileName = basename($destination);

            // Extract file if it is archive
            $ext = JString::strtolower(JFile::getExt($fileName));
            if (strcmp($ext, "zip") == 0) {

                $destFolder = JPath::clean($app->get("tmp_path")) . DIRECTORY_SEPARATOR . "project";
                if (JFolder::exists($destFolder)) {
                    JFolder::delete($destFolder);
                }

                $filePath = $model->extractFile($destination, $destFolder);

            } else {
                $filePath = $destination;
            }

            $override   = JArrayHelper::getValue($data, "override", false, "bool");
            $model->importProject($filePath, $override);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_ITPTRANSIFEX_PROJECT_IMPORTED'), $redirectOptions);

    }
}
