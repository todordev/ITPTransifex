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
 * ItpTransifex Projects Controller
 *
 * @package     ItpTransifex
 * @subpackage  Components
 */
class ItpTransifexControllerProjects extends Prism\Controller\Admin
{
    public function getModel($name = 'Project', $prefix = 'ItpTransifexModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function update()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Get form data
        $pks   = $this->input->post->get('cid', array(), 'array');
        $model = $this->getModel();
        /** @var $model ItpTransifexModelProject */

        $redirectOptions = array(
            "view" => $this->view_list
        );

        Joomla\Utilities\ArrayHelper::toInteger($pks);

        // Check for validation errors.
        if (empty($pks)) {
            $this->displayWarning(JText::_("COM_ITPTRANSIFEX_INVALID_ITEM"), $redirectOptions);

            return;
        }

        $params = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        $options = array(
            "username" => $params->get("username"),
            "password" => $params->get("password"),
            "url"      => $params->get("api_url") . "project",
        );

        try {

            $model->synchronize($pks, $options);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::plural('COM_ITPTRANSIFEX_PROJECTS_SYNCHRONIZED', count($pks)), $redirectOptions);
    }

    /**
     * Function that allows child controller access to model data
     * after the item has been deleted.
     *
     * @param   ItpTransifexModelProject  $model  The data model object.
     * @param   mixed       $id     The validated data.
     *
     * @return  void
     *
     * @since   12.2
     * @throws Exception
     */
    protected function postDeleteHook(JModelLegacy $model, $id = null)
    {
        try {

            $model->removePackages($id);
            $model->removeResources($id);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }
    }
}
