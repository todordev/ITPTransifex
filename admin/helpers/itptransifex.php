<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class ItpTransifexHelper
{
    /**
     * Configure the Linkbar.
     *
     * @param    string  $vName  The name of the active view.
     *
     * @since    1.6
     */
    public static function addSubmenu($vName = 'dashboard')
    {
        JHtmlSidebar::addEntry(
            JText::_('COM_ITPTRANSIFEX_DASHBOARD'),
            'index.php?option=com_itptransifex&view=dashboard',
            $vName == 'dashboard'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_ITPTRANSIFEX_PROJECTS'),
            'index.php?option=com_itptransifex&view=projects',
            $vName == 'projects'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_ITPTRANSIFEX_PACKAGES'),
            'index.php?option=com_itptransifex&view=packages',
            $vName == 'packages'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_ITPTRANSIFEX_RESOURCES'),
            'index.php?option=com_itptransifex&view=resources',
            $vName == 'resources'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_ITPTRANSIFEX_LANGUAGES'),
            'index.php?option=com_itptransifex&view=languages',
            $vName == 'languages'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_ITPTRANSIFEX_IMPORT_EXPORT'),
            'index.php?option=com_itptransifex&view=export',
            $vName == 'export'
        );
    }

    public static function validateCaptcha($secret, $response)
    {
        $params = array(
            "secret" => $secret,
            "response" => $response
        );

        $postData = "";
        foreach ($params as $k => $v) {
            $postData .= $k . '='.$v.'&';
        }
        rtrim($postData, '&');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $response = curl_exec($ch);
        
        curl_close($ch);

        $response = json_decode($response, true);

        return (!$response['success']) ? false : true;
    }
}
