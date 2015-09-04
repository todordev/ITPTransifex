<?php
/**
 * @package      ItpTransifex
 * @subpackage   Projects
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Transifex;

use Prism\Database\ArrayObject;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage projects.
 *
 * @package      ItpTransifex
 * @subpackage   Projects
 */
class Projects extends ArrayObject
{
    /**
     * Load projects from database.
     *
     * <code>
     * $options = array(
     *    "ids" => array(1,2,3)
     * );
     *
     * $projects    = new Transifex\Projects();
     * $projects->setDb(\JFactory::getDbo());
     * $projects->load($ids);
     *
     * foreach ($projects as $project) {
     *      echo $project["name"];
     *      echo $project["description"];
     * }
     * </code>
     *
     * @param array $options
     */
    public function load($options = array())
    {
        $ids = ArrayHelper::getValue($options, "ids", array(), "array");
        ArrayHelper::toInteger($ids);

        // Load project data
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.name, a.alias, a.description, a.source_language_code, a.filename")
            ->from($this->db->quoteName("#__itptfx_projects", "a"));

        if (!empty($ids)) {
            $query->where("a.id IN ( " . implode(",", $ids) . " )");
        }

        $this->db->setQuery($query);
        $this->items = (array)$this->db->loadAssocList();
    }

    /**
     * Count and return resources number of projects.
     *
     * <code>
     * $projectsIds = array(1,2,3);
     *
     * $projects    = new Transifex\Projects(\JFactory::getDbo());
     * $projects->load($projectsIds);
     * $resourcesNumber = $projects->getNumberOfResources();
     * </code>
     *
     * @param array $ids Projects IDs
     *
     * @return array
     */
    public function getNumberOfResources($ids = array())
    {
        // If it is missing IDs as parameter, get the IDs of the current items.
        if (!$ids and !empty($this->items)) {

            $ids = array();
            foreach ($this->items as $item) {
                $ids[] = $item->id;
            }

        }

        // If there are no IDs, return empty array.
        if (!$ids) {
            return array();
        }

        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select("a.project_id, COUNT(*) as number")
            ->from($this->db->quoteName("#__itptfx_resources", "a"))
            ->where("a.project_id IN (" . implode(",", $ids) . ")")
            ->group("a.project_id");

        $this->db->setQuery($query);

        return (array)$this->db->loadAssocList("project_id");
    }

    /**
     * Count and return resources number of projects.
     *
     * <code>
     * $projectsIds = array(1,2,3);
     *
     * $projects    = new Transifex\Projects(\JFactory::getDbo());
     * $packagesNumber = $projects->getNumberOfPackages($projectsIds);
     * </code>
     *
     * @param array $ids Projects IDs
     *
     * @return array
     */
    public function getNumberOfPackages($ids = array())
    {
        // If it is missing IDs as parameter, get the IDs of the current items.
        if (!$ids and !empty($this->items)) {
            $ids = array();
            foreach ($this->items as $item) {
                $ids[] = $item->id;
            }
        }

        // If there are no IDs, return empty array.
        if (!$ids) {
            return array();
        }

        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select("a.project_id, COUNT(*) as number")
            ->from($this->db->quoteName("#__itptfx_packages", "a"))
            ->where("a.project_id IN (" . implode(",", $ids) . ")")
            ->group("a.project_id");

        $this->db->setQuery($query);

        return (array)$this->db->loadAssocList("project_id");
    }
}
