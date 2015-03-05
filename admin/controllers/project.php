<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

jimport('itprism.controller.form.backend');

/**
 * ItpTransifex project controller class.
 *
 * @package      ITPTransifex
 * @subpackage   Components
 * @since        1.6
 */
class ItpTransifexControllerProject extends ITPrismControllerFormBackend
{
    /**
     * Proxy for getModel.
     * @since   1.6
     */
    public function getModel($name = 'Project', $prefix = 'ItpTransifexModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Save an item
     */
    public function save($key = null, $urlVar = null)
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        // Get form data
        $data   = $app->input->post->get('jform', array(), 'array');
        $itemId = JArrayHelper::getValue($data, "id");

        $redirectOptions = array(
            "task" => $this->getTask(),
            "id"   => $itemId
        );

        $model = $this->getModel();
        /** @var $model ItpTransifexModelProject */

        $form = $model->getForm($data, false);
        /** @var $form JForm * */

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

            // Get image
            $files = $this->input->files->get('jform', array(), 'array');
            $image = JArrayHelper::getValue($files, "image");

            // Upload image
            if (!empty($image['name'])) {

                $imageName = $model->uploadImage($image);
                if (!empty($imageName)) {
                    $validData["image"] = $imageName;
                }

            }

            $itemId = $model->save($validData);

            $redirectOptions["id"] = $itemId;

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_ITPTRANSIFEX_PROJECT_SAVED'), $redirectOptions);
    }

    /**
     * Delete image
     */
    public function removeImage()
    {
        // Check for request forgeries.
        JSession::checkToken("get") or jexit(JText::_('JINVALID_TOKEN'));

        // Get item id
        $itemId    = $this->input->get->getInt("id");

        $redirectOptions = array(
            "view" => "projects",
        );

        // Check for registered user
        if (!$itemId) {
            $this->displayNotice(JText::_('COM_ITPTRANSIFEX_ERROR_INVALID_IMAGE'), $redirectOptions);
            return;
        }

        try {

            jimport('joomla.filesystem.folder');
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.path');

            $model = $this->getModel();
            $model->removeImage($itemId);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }

        $redirectOptions = array(
            "view"   => "project",
            "layout" => "edit",
            "id"     => $itemId
        );

        $this->displayMessage(JText::_('COM_ITPTRANSIFEX_IMAGE_DELETED'), $redirectOptions);
    }
}
