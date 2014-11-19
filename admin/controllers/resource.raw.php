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
 * ItpTransifex Resource controller
 *
 * @package     ItpTransifex
 * @subpackage  Components
 */
class ItpTransifexControllerResource extends JControllerLegacy
{
    /**
     * Proxy for getModel.
     * @since   1.6
     */
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
        jimport("itprism.response.json");
        $response = new ITPrismResponseJson();

        // Get form data
        $itemId    = $this->input->getInt('pk');
        $filename  = $this->input->get('value');

        $model = $this->getModel();
        /** @var $model ItpTransifexModelResource */

        // Check for validation errors.
        if (!$itemId or !$filename) {

            $response
                ->setTitle(JText::_("COM_ITPTRANSIFEX_FAIL"))
                ->setText(JText::_("COM_ITPTRANSIFEX_INVALID_RESOURCE"))
                ->failure();

            echo $response;

            JFactory::getApplication()->close();
        }

        try {

            $model->saveFilename($itemId, $filename);

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
            ->setText(JText::_("COM_ITPTRANSIFEX_FILENAME_SAVED_SUCCESSFULLY"))
            ->success();

        echo $response;
        JFactory::getApplication()->close();

    }

    /**
     * This method save a type.
     */
    public function saveType()
    {
        jimport("itprism.response.json");
        $response = new ITPrismResponseJson();

        // Get form data
        $itemId  = $this->input->getInt('pk');
        $type    = $this->input->get('value');

        $model = $this->getModel();
        /** @var $model ItpTransifexModelResource */

        // Check for validation errors.
        if (!$itemId or !$type) {

            $response
                ->setTitle(JText::_("COM_ITPTRANSIFEX_FAIL"))
                ->setText(JText::_("COM_ITPTRANSIFEX_INVALID_RESOURCE"))
                ->failure();

            echo $response;

            JFactory::getApplication()->close();
        }

        try {

            $model->saveType($itemId, $type);

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
            ->setText(JText::_("COM_ITPTRANSIFEX_TYPE_SAVED_SUCCESSFULLY"))
            ->success();

        echo $response;
        JFactory::getApplication()->close();

    }
}
