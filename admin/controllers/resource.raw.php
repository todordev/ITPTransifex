<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * ItpTransifex Resource controller
 *
 * @package     ItpTransifex
 * @subpackage  Components
 */
class ItpTransifexControllerResource extends JControllerLegacy
{
    public function getModel($name = 'Resource', $prefix = 'ItpTransifexModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * This method save a filename.
     */
    public function saveFilename()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $response = new Prism\Response\Json();

        // Get form data
        $itemId    = $this->input->getInt('pk');
        $filename  = $this->input->get('value');

        $model = $this->getModel();
        /** @var $model ItpTransifexModelResource */

        // Check for validation errors.
        if (!$itemId or !$filename) {
            $response
                ->setTitle(JText::_('COM_ITPTRANSIFEX_FAIL'))
                ->setText(JText::_('COM_ITPTRANSIFEX_INVALID_RESOURCE'))
                ->failure();

            echo $response;

            $app->close();
        }

        try {
            $model->saveFilename($itemId, $filename);
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_userideas');

            $response
                ->setTitle(JText::_('COM_ITPTRANSIFEX_FAIL'))
                ->setText($e->getMessage())
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setTitle(JText::_('COM_ITPTRANSIFEX_SUCCESS'))
            ->setText(JText::_('COM_ITPTRANSIFEX_FILENAME_SAVED_SUCCESSFULLY'))
            ->success();

        echo $response;
        $app->close();
    }

    /**
     * This method save a category.
     */
    public function saveCategory()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $response = new Prism\Response\Json();

        // Get form data
        $itemId     = $this->input->getInt('pk');
        $category   = $this->input->get('value');

        $model = $this->getModel();
        /** @var $model ItpTransifexModelResource */

        // Check for validation errors.
        if (!$itemId or !$category) {
            $response
                ->setTitle(JText::_('COM_ITPTRANSIFEX_FAIL'))
                ->setText(JText::_('COM_ITPTRANSIFEX_INVALID_RESOURCE'))
                ->failure();

            echo $response;
            $app->close();
        }

        try {
            $model->saveCategory($itemId, $category);
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_userideas');

            $response
                ->setTitle(JText::_('COM_ITPTRANSIFEX_FAIL'))
                ->setText($e->getMessage())
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setTitle(JText::_('COM_ITPTRANSIFEX_SUCCESS'))
            ->setText(JText::_('COM_ITPTRANSIFEX_TYPE_SAVED_SUCCESSFULLY'))
            ->success();

        echo $response;
        $app->close();
    }
}
