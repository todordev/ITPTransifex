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
 * ItpTransifex Resources Controller
 *
 * @package     ItpTransifex
 * @subpackage  Components
 */
class ItpTransifexControllerResources extends Prism\Controller\Admin
{
    public function getModel($name = 'Resource', $prefix = 'ItpTransifexModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function update()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        // Get form data
        $pks = $app->input->post->get('cid', array(), 'array');

        $model = $this->getModel();
        /** @var $model ItpTransifexModelResource */

        $redirectOptions = array(
            'view' => $this->view_list
        );

        $pks = Joomla\Utilities\ArrayHelper::toInteger($pks);

        // Check for validation errors.
        if (!$pks) {
            $this->displayWarning(JText::_('COM_ITPTRANSIFEX_INVALID_ITEM'), $redirectOptions);
            return;
        }

        $params = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        $options = array(
            'username' => $params->get('username'),
            'password' => $params->get('password'),
            'url'      => $params->get('api_url'),
        );

        try {
            $model->synchronize($pks, $options);
        } catch (RuntimeException $e) {
            $this->displayWarning($e->getMessage(), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_userideas');
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::plural('COM_ITPTRANSIFEX_N_ITEMS_SYNCHRONIZED', count($pks)), $redirectOptions);
    }
}
