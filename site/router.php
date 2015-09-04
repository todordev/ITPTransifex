<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport("Transifex.init");

/**
 * Method to build Route
 *
 * @param array $query
 *
 * @return array
 */
function ItpTransifexBuildRoute(&$query)
{
    $segments = array();

    // get a menu item based on Itemid or currently active
    $app  = JFactory::getApplication();
    $menu = $app->getMenu();

    // we need a menu item.  Either the one specified in the query, or the current active one if none specified
    if (empty($query['Itemid'])) {
        $menuItem      = $menu->getActive();
        $menuItemGiven = false;
    } else {
        $menuItem      = $menu->getItem($query['Itemid']);
        $menuItemGiven = true;
    }

    // Check again
    if ($menuItemGiven and isset($menuItem) and strcmp("com_itptransifex", $menuItem->component) != 0) {
        $menuItemGiven = false;
        unset($query['Itemid']);
    }

    $mView   = (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
    $mId     = (empty($menuItem->query['id'])) ? null : $menuItem->query['id'];
//    $mOption = (empty($menuItem->query['option'])) ? null : $menuItem->query['option'];
//    $mCatid  = (empty($menuItem->query['catid'])) ? null : $menuItem->query['catid'];

    // If is set view and Itemid missing, we have to put the view to the segments
    if (isset($query['view'])) {
        $view = $query['view'];
    } else {
        return $segments;
    }

    // Are we dealing with a category that is attached to a menu item?
    if (($menuItem instanceof stdClass) and isset($view) and ($mView == $view) and (isset($query['id'])) and ($mId == (int)$query['id'])) {

        unset($query['view']);

        if (isset($query['layout'])) {
            unset($query['layout']);
        }

        unset($query['id']);

        return $segments;
    }

    // Views
    if (isset($view)) {

        switch ($view) {

            case "project":

                if (!$menuItemGiven) {
                    $segments[] = $view;
                }
                unset($query['view']);

                if (isset($query['id'])) {
                    $segments[] = $query['id'];
                    unset($query['id']);
                }

                break;

            case "packages":

                if (!$menuItemGiven) {
                    $segments[] = $view;
                }
                unset($query['view']);

                if (isset($query['id'])) {
                    if (!$menuItemGiven) {
                        $segments[] = $query['id'];
                    } else {
                        $menuItemProjectId = (isset($menuItem->query["id"])) ? (int)$menuItem->query["id"] : 0;
                        if ($menuItemProjectId != $query['id']) {
                            $segments[] = $query['id'];
                        }
                    }
                    unset($query['id']);
                }

                if (isset($query['lang'])) {
                    $segments[] = $query['lang'];
                    unset($query['lang']);
                }

                break;

        }

    }

    // Layout
    if (isset($query['layout'])) {
        if ($menuItemGiven and isset($menuItem->query['layout'])) {
            if ($query['layout'] == $menuItem->query['layout']) {
                unset($query['layout']);
            }
        } else {
            if ($query['layout'] == 'default') {
                unset($query['layout']);
            }
        }
    }

    $total = count($segments);

    for ($i = 0; $i < $total; $i++) {
        $segments[$i] = str_replace(':', '-', $segments[$i]);
    }

    return $segments;
}

/**
 * Method to parse Route
 *
 * @param array $segments
 *
 * @return array
 */
function ItpTransifexParseRoute($segments)
{
    $total = count($segments);
    $vars = array();

    for ($i = 0; $i < $total; $i++) {
        $segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
    }

    //Get the active menu item.
    $app  = JFactory::getApplication();
    $menu = $app->getMenu();
    $item = $menu->getActive();

    // Count route segments
    $count = count($segments);

    // Standard routing for articles.  If we don't pick up an Itemid then we get the view from the segments
    // the first segment is the view and the last segment is the id of the details, category or payment.
    if (!isset($item)) {
        $vars['view']  = $segments[0];
        $vars['id']    = $segments[$count - 1];

        return $vars;
    }

    // COUNT == 1

    // Category
    if ($count == 1) {

        // We check to see if an alias is given.  If not, we assume it is a package,
        // because the project always have alias.
        if (5 == Joomla\String\String::strlen($segments[0])) {
            $language = ItpTransifexHelperRoute::getLanguage($segments[0]);

            if (!empty($language) and isset($item->query["id"])) {
                $vars['view'] = 'packages';
                $vars['id']   = (int)$item->query["id"];
                $vars['lang'] = $segments[0];
            } else {
                $vars['view'] = 'project';
                $vars['id']   = (int)$segments[0];
            }

            return $vars;
        }

        $vars['view'] = 'project';
        $vars['id']   = (int)$segments[0];

        return $vars;

    }

    // COUNT >= 2

    if ($count >= 2) {

        $vars['view']  = 'packages';
        $vars['id']    = (int)$segments[$count - 2];
        $vars['lang']  = $segments[$count - 1];

        return $vars;

    }

    return $vars;
}
