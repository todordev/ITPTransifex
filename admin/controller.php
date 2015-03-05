<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Default Controller
 *
 * @package         ItpTransifex
 * @subpackage      Components
 */
class ItpTransifexController extends JControllerLegacy
{
    public function display($cachable = false, $urlparams = array())
    {
        $viewName = $this->input->getCmd('view', 'dashboard');
        $this->input->set("view", $viewName);

        $option = $this->input->getCmd("options", "com_itptransifex");

        $document = JFactory::getDocument();
        $document->addStyleSheet('../media/' . $option . '/css/backend.style.css');

        parent::display();

        return $this;
    }
}
