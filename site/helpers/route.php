<?php
/**
 * @package      ItpTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport("itptransifex.init");

/**
 * Component Route Helper that help to find a menu item.
 * IMPORTANT: It help us to find right MENU ITEM.
 *
 * Use router ...BuildRoute to build a link
 *
 * @static
 * @package        ITPrism Components
 * @subpackage     ItpTransifex
 * @since          1.5
 */
abstract class ItpTransifexHelperRoute
{
    protected static $projects = array();
    protected static $languages = array();
    protected static $projectsAliases = array();
    protected static $lookup;

    /**
     * This method route item in the view "packages".
     */
    public static function getPackagesRoute($slug, $lang)
    {
        /**
         *
         * # category
         * We will check for view category first. If find a menu item with view "category" and "id" eqallity of the key,
         * we will get that menu item ( Itemid ).
         *
         * # categories view
         * If miss a menu item with view "category" we continue with searchin but now for view "categories".
         * It is assumed view "categories" will be in the first level of the menu.
         * The view "categories" won't contain category ID so it has to contain 0 for ID key.
         */
        $needles = array(
            'packages' => array((int)$slug),
        );

        //Create the link
        $link = 'index.php?option=com_itptransifex&view=packages&id='.$slug."&lang=".$lang;

        // Looking for menu item (Itemid)
        if ($item = self::findItem($needles)) {
            $link .= '&Itemid=' . $item;
        } elseif ($item = self::findItem()) { // Get the menu item (Itemid) from the active (current) item.
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    /**
     * This method route item in the view "project".
     *
     * @param int $id
     *
     * @return string
     */
    public static function getProjectRoute($id)
    {
        $needles = array(
            'project' => array((int)$id),
        );

        //Create the link
        $link = 'index.php?option=com_itptransifex&view=project&id='.$id;

        // Looking for menu item (Itemid)
        if ($item = self::findItem($needles)) {
            $link .= '&Itemid=' . $item;
        } elseif ($item = self::findItem()) { // Get the menu item (Itemid) from the active (current) item.
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    protected static function findItem($needles = null)
    {
        $app   = JFactory::getApplication();
        $menus = $app->getMenu('site');

        // Prepare the reverse lookup array.
        // Collect all menu items and creat an array that contains
        // the ID from the query string of the menu item as a key,
        // and the menu item id (Itemid) as a value
        // Example:
        // array( "category" =>
        //     1(id) => 100 (Itemid),
        //     2(id) => 101 (Itemid)
        // );
        if (self::$lookup === null) {
            self::$lookup = array();

            $component = JComponentHelper::getComponent('com_itptransifex');
            $items     = $menus->getItems('component_id', $component->id);

            if ($items) {
                foreach ($items as $item) {
                    if (isset($item->query) && isset($item->query['view'])) {
                        $view = $item->query['view'];

                        if (!isset(self::$lookup[$view])) {
                            self::$lookup[$view] = array();
                        }

                        if (isset($item->query['id'])) {
                            self::$lookup[$view][$item->query['id']] = $item->id;
                        } else { // If it is a root element that have no a request parameter ID ( categories, authors ), we set 0 for an key
                            self::$lookup[$view][0] = $item->id;
                        }
                    }
                }
            }
        }

        if ($needles) {

            foreach ($needles as $view => $ids) {
                if (isset(self::$lookup[$view])) {

                    foreach ($ids as $id) {
                        if (isset(self::$lookup[$view][(int)$id])) {
                            return self::$lookup[$view][(int)$id];
                        }
                    }

                }
            }

        } else {
            $active = $menus->getActive();
            if ($active) {
                return $active->id;
            }
        }

        return null;
    }

    /**
     * Load data about project.
     * We use this method in the router "ItpTransifexParseRoute".
     *
     * @param int $id
     *
     * @return array
     */
    public static function getProject($id)
    {
        $result = array();
        $id     = (int)$id;

        // Check for valid ID.
        if (!$id) {
            return $result;
        }

        // Return cached data.
        if (isset(self::$projects[$id])) {
            return self::$projects[$id];
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("a.id, a.alias, " . $query->concatenate(array("a.id", "a.alias"), "-") . " AS slug")
            ->from($query->quoteName("#__itptfx_projects", "a"))
            ->where("a.id = " . (int)$id);

        $db->setQuery($query);
        $result = $db->loadAssoc();

        if (!$result) {
            $result = array();
        }

        self::$projects[$id] = $result;

        return self::$projects[$id];
    }

    /**
     * Load the project alias from database.
     * We use this method in the router "ItpTransifexParseRoute".
     *
     * @param int $id
     *
     * @return string
     */
    public static function getProjectAlias($id)
    {
        $result = "";
        $id     = (int)$id;

        // Check for valid ID.
        if (!$id) {
            return $result;
        }

        // Return cached data.
        if (isset(self::$projectsAliases[$id])) {
            return self::$projectsAliases[$id];
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("a.alias")
            ->from($query->quoteName("#__itptfx_projects", "a"))
            ->where("a.id = " . (int)$id);

        $db->setQuery($query, 0, 1);
        $result = $db->loadResult();

        if (!$result) {
            $result = "";
        }

        self::$projectsAliases[$id] = $result;

        return self::$projectsAliases[$id];
    }

    /**
     * Load the project alias from database.
     * We use this method in the router "ItpTransifexParseRoute".
     *
     * @param string $languageCode
     *
     * @return int
     */
    public static function getLanguage($languageCode)
    {
        $result = 0;

        // Check for valid ID.
        if (!$languageCode) {
            return $result;
        }

        // Return cached data.
        if (isset(self::$languages[$languageCode])) {
            return self::$languages[$languageCode];
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("a.id")
            ->from($query->quoteName("#__itptfx_languages", "a"))
            ->where("a.code = " . $db->quote($languageCode));

        $db->setQuery($query, 0, 1);
        $result = $db->loadResult();

        if (!$result) {
            $result = 0;
        }

        self::$projectsAliases[$languageCode] = (int)$result;

        return self::$projectsAliases[$languageCode];
    }
}
