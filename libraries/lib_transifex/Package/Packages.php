<?php
/**
 * @package      Transifex\Package
 * @subpackage   Packages
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Transifex\Package;

use Prism\Database\ArrayObject;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage packages.
 *
 * @package      Transifex\Package
 * @subpackage   Packages
 */
class Packages extends ArrayObject
{
    protected $resources;

    /**
     * Load packages from database.
     *
     * <code>
     * // Can be list with package ids or project ID.
     * $options = array(
     *     "ids" => array(1,2,3),
     *     "project_id" = 1,
     *     "language" => "en_GB"
     * );
     *
     * $packages    = new Transifex\Package\Packages(\JFactory::getDbo());
     * $packages->load($ids);
     *
     * foreach ($packages as $project) {
     *      echo $project["title"];
     *      echo $project["filename"];
     * }
     * </code>
     *
     * @param array $options
     */
    public function load($options = array())
    {
        $ids       = ArrayHelper::getValue($options, "ids", array(), "array");
        $projectId = ArrayHelper::getValue($options, "project_id", 0, "int");
        $ids = ArrayHelper::toInteger($ids);

        // Load project data
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.name, a.alias, a.filename, a.description, a.version, a.language, a.type, a.project_id")
            ->from($this->db->quoteName("#__itptfx_packages", "a"));

        // Filter by record ids.
        if (!empty($ids)) {
            $query->where("a.id IN ( " . implode(",", $ids) . " )");
        }

        // Filter by project ID.
        if (!empty($projectId)) {
            $query->where("a.project_id = " . (int)$projectId);
        }

        // Filter by language
        $language = (isset($options["language"])) ? $options["language"] : null;
        if (!empty($language)) {
            $query->where("a.language = " . $this->db->quote($language));
        }

        $this->db->setQuery($query);
        $this->items = (array)$this->db->loadAssocList();
    }

    /**
     * Count and return resources number of packages.
     *
     * <code>
     * $packagesIds = array(1,2,3);
     *
     * $packages    = new Transifex\Package\Packages(JFactory::getDbo());
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

        $results = (array)$this->db->loadAssocList("package_id");

        return $results;
    }

    /**
     * Get the resources of the packages.
     *
     * <code>
     * $packagesIds = array(1,2,3);
     *
     * $packages    = new Transifex\Package\Packages(JFactory::getDbo());
     * $packages->load($packagesIds);
     * $resourcesNumber = $packages->getNumberOfResources();
     * </code>
     *
     * @param bool   $force Force to load the resources from databases.
     *
     * @return null|array
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

            $this->resources = (array)$this->db->loadAssocList();
        }

        return $this->resources;
    }
}
