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
 * ItpTransifex Packages Controller
 *
 * @package     ItpTransifex
 * @subpackage  Components
 */
class ItpTransifexControllerPackages extends Prism\Controller\Admin
{
    public function getModel($name = 'Package', $prefix = 'ItpTransifexModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Remove record from packages map.
     *
     * @param ItpTransifexModelPackage $model
     * @param array                    $cid
     *
     * @throws Exception
     */
    protected function postDeleteHook(JModelLegacy $model, $cid = null)
    {
        try {
            $model->removeResourcesFromMap($cid);
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }
    }
}
