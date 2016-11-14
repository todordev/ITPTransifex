<?php
/**
 * @package      ItpTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * ItpTransifex import controller.
 *
 * @package      ItpTransifex
 * @subpackage   Components
 */
class ItpTransifexControllerImport extends Prism\Controller\Form\Backend
{
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
            'view' => 'projects',
        );

        $model = $this->getModel();
        /** @var $model ItpTransifexModelImport */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_FORM_CANNOT_BE_LOADED'));
        }

        // Validate the form
        $validData = $model->validate($form, $data);

        // Check for errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }

        $fileData = Joomla\Utilities\ArrayHelper::getValue($data, 'data');
        if (empty($fileData) or empty($fileData['name'])) {
            $this->displayNotice(JText::_('COM_ITPTRANSIFEX_ERROR_FILE_CANT_BE_UPLOADED'), $redirectOptions);
            return;
        }

        try {
            $filePath = $model->uploadFile($fileData);

            $override   = Joomla\Utilities\ArrayHelper::getValue($data, 'override', false, 'bool');
            $model->importProject($filePath, $override);

        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_userideas');
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_ITPTRANSIFEX_PROJECT_IMPORTED'), $redirectOptions);
    }
}
