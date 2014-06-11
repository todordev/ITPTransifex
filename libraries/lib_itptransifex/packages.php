<?php
/**
 * @package      ItpTransifex
 * @subpackage   Packages
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage packages.
 *
 * @package      ItpTransifex
 * @subpackage   Packages
 */
class ItpTransifexPackages implements Iterator, Countable, ArrayAccess
{
    protected $items = array();

    protected $resources;

    /**
     * Database driver.
     *
     * @var JDatabaseDriver
     */
    protected $db;

    protected $position = 0;

    /**
     * Initialize the object.
     *
     * <code>
     * $packages    = new ItpTransifexPackages(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db Database object.
     */
    public function __construct(JDatabaseDriver $db = null)
    {
        $this->db = $db;
    }

    /**
     * Set a database object.
     *
     * <code>
     * $packages    = new ItpTransifexPackages();
     * $packages->setDb(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db
     *
     * @return self
     */
    public function setDb(JDatabaseDriver $db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * Load packages from database.
     *
     * <code>
     * $ids = array(1,2,3);
     *
     * $packages    = new ItpTransifexPackages();
     * $packages->setDb(JFactory::getDbo());
     * $packages->load($ids);
     *
     * foreach ($packages as $project) {
     *      echo $project["title"];
     *      echo $project["filename"];
     * }
     *
     * </code>
     *
     * @param array $ids Packages IDs
     */
    public function load($ids)
    {
        // Set the newest ids.
        if (!is_array($ids)) {
            return;
        }

        JArrayHelper::toInteger($ids);
        if (!$ids) {
            return;
        }

        // Load project data
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.name, a.alias, a.filename, a.description, a.version, a.language, a.type, a.project_id")
            ->from($this->db->quoteName("#__itptfx_packages", "a"))
            ->where("a.id IN ( " . implode(",", $ids) . " )");

        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();

        if (!$results) {
            $results = array();
        }

        $this->items = $results;
    }

    /**
     * Load packages from database.
     *
     * <code>
     * $projectId = 1;
     *
     * $options = array(
     *     "language" => "en_GB"
     * );
     *
     * $packages    = new ItpTransifexPackages();
     * $packages->setDb(JFactory::getDbo());
     * $packages->loadByProjectId($projectId, $options);
     *
     * foreach ($packages as $project) {
     *      echo $project["title"];
     *      echo $project["filename"];
     * }
     *
     * </code>
     *
     * @param int $projectId Project ID
     * @param array $options
     *
     */
    public function loadByProjectId($projectId, $options = array())
    {
        // Set the newest ids.
        if (!$projectId) {
            return;
        }

        // Load project data
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.name, a.alias, a.filename, a.description, a.version, a.language, a.type, a.project_id")
            ->from($this->db->quoteName("#__itptfx_packages", "a"))
            ->where("a.project_id = " .(int)$projectId);

        // Filter by language
        $language = (isset($options["language"])) ? $options["language"] : null;
        if (!empty($language)) {
            $query->where("a.language = " . $this->db->quote($language));
        }

        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();

        if (!$results) {
            $results = array();
        }

        $this->items = $results;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return (!isset($this->items[$this->position])) ? null : $this->items[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->items[$this->position]);
    }

    public function count()
    {
        return (int)count($this->items);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    /**
     * Count and return resources number of packages.
     *
     * <code>
     * $packagesIds = array(1,2,3);
     *
     * $packages    = new ItpTransifexPackages(JFactory::getDbo());
     * $packages->load($packagesIds);
     * $resourcesNumber = $packages->getNumberOfResources();
     * </code>
     *
     * @param array $ids Packages IDs
     *
     * @return array
     */
    public function getNumberOfResources($ids = array())
    {
        // If it is missing IDs as parameter, get the IDs of the current items.
        if (!$ids and !empty($this->items)) {

            $ids = array();
            foreach ($this->items as $item) {
                $ids[] = $item['id'];
            }

        }

        // If there are no IDs, return empty array.
        if (!$ids) {
            return array();
        }

        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select("a.package_id, COUNT(*) as number")
            ->from($this->db->quoteName("#__itptfx_packages_map", "a"))
            ->where("a.package_id IN (" . implode(",", $ids) . ")")
            ->group("a.package_id");

        $this->db->setQuery($query);

        $results = $this->db->loadAssocList("package_id");

        if (!$results) {
            $results = array();
        }

        return $results;
    }

    /**
     * @param bool   $force
     *
     * @return array
     */
    public function getResources($force = false)
    {
        if (is_null($this->resources) or (!is_null($this->resources) and $force)) {

            // If it is missing IDs as parameter, get the IDs of the current items.
            $ids = array();
            foreach ($this->items as $item) {
                $ids[] = $item['id'];
            }

            // If there are no IDs, return empty array.
            if (!$ids) {
                return array();
            }

            // Create a new query object.
            $query = $this->db->getQuery(true);

            $query
                ->select(
                    "a.id, a.name, a.alias, a.filename, a.type, a.i18n_type, a.source_language_code, a.published, a.project_id, " .
                    "b.package_id, ".
                    "c.alias AS package_alias"
                )
                ->from($this->db->quoteName("#__itptfx_resources", "a"))
                ->innerJoin($this->db->quoteName("#__itptfx_packages_map", "b") . " ON a.id = b.resource_id")
                ->innerJoin($this->db->quoteName("#__itptfx_packages", "c") . " ON b.package_id = c.id")
                ->where("b.package_id IN (" . implode(",", $ids) . ")");

            $this->db->setQuery($query);

            $results = $this->db->loadAssocList();

            if (!$results) {
                $results = array();
            }

            $this->resources = $results;
        }

        return $this->resources;
    }
}
