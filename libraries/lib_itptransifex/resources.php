<?php
/**
 * @package      ITPTransifex
 * @subpackage   Libraries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage resources.
 */
class ItpTransifexResources implements Iterator, Countable, ArrayAccess
{
    protected $items = array();

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
     * @param JDatabaseDriver $db Database object.
     */
    public function __construct(JDatabaseDriver $db)
    {
        $this->db = $db;
    }

    public function load($ids, $state = null)
    {
        if (!$ids) {
            return;
        }

        // Load project data
        $query = $this->getQuery();

        JArrayHelper::toInteger($ids);
        $query->where("a.id IN ( " . implode(",", $ids) . " )");

        // Filter by state
        if (is_numeric($state)) {
            $query->where("a.published = " . (int)$state);
        }

        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();

        if (!$results) {
            $results = array();
        }

        $this->items = $results;
    }

    public function loadByProjectId($id, $state = null)
    {
        if (!$id) {
            return;
        }

        $query = $this->getQuery();

        $query->where("a.project_id = " . (int)$id);

        // Filter by state
        if (is_numeric($state)) {
            $query->where("a.published = " . (int)$state);
        }

        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();

        if (!$results) {
            $results = array();
        }

        $this->items = $results;
    }

    public function loadByPackageId($id, $state = null)
    {
        if (!$id) {
            return;
        }

        $results = array();

        // Get package resources IDs.
        $query = $this->db->getQuery(true);
        $query
            ->select("a.resource_id")
            ->from($this->db->quoteName("#__itptfx_packages_map", "a"))
            ->where("a.package_id = " . (int)$id);

        $this->db->setQuery($query);
        $resourcesIds = $this->db->loadColumn();

        // Load resources.
        if (!empty($resourcesIds)) {

            $query = $this->getQuery();

            $query->where("a.id IN ( " . implode(",", $resourcesIds) . " )");

            // Filter by state
            if (is_numeric($state)) {
                $query->where("a.published = " . (int)$state);
            }

            $this->db->setQuery($query);
            $results = $this->db->loadAssocList();

            if (!$results) {
                $results = array();
            }

        }

        $this->items = $results;
    }

    protected function getQuery()
    {
        $query = $this->db->getQuery(true);

        $query
            ->select(
                "a.id, a.name, a.alias, a.filename, a.type, a.i18n_type, " .
                "a.source_language_code, a.project_id"
            )
            ->from($this->db->quoteName("#__itptfx_resources", "a"))
            ->order("a.name ASC");

        return $query;
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

    public function remove()
    {

        if (!empty($this->items)) {

            foreach ($this->items as $key => $item) {

                // Remove resources in resource map.
                $query = $this->db->getQuery(true);
                $query
                    ->delete($this->db->quoteName("#__itptfx_resources_map"))
                    ->where($this->db->quoteName("resource_id") . "=" . (int)$item["id"]);

                $this->db->setQuery($query);
                $this->db->execute();

                // Remove resources
                $query = $this->db->getQuery(true);
                $query
                    ->delete($this->db->quoteName("#__itptfx_resources"))
                    ->where($this->db->quoteName("id") . "=" . (int)$item["id"]);

                $this->db->setQuery($query);
                $this->db->execute();

                unset($this->items[$key]);
            }
        }
    }
}
