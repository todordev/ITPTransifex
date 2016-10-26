<?php
/**
 * @package      Transifex\Resource
 * @subpackage   Resources
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Transifex\Resource;

use Joomla\Utilities\ArrayHelper;
use Prism\Database\Collection;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage resources.
 *
 * @package      Transifex\Resource
 * @subpackage   Resources
 */
class Resources extends Collection
{
    /**
     * Load resources from database.
     *
     * <code>
     * $options = array(
     *    "ids" => array(1,2,3), // Resource IDs
     *    "project_id" => 1,
     *    "package_id" => 2,
     *    "state" => Prism\Constants::PUBLISHED,
     * );
     *
     * $resources    = new Transifex\Resource\Resources(\JFactory::getDbo());
     * $resources->load($options);
     *
     * foreach ($resources as $resource) {
     *      echo $resource["name"];
     *      echo $resource["filename"];
     * }
     * </code>
     *
     * @param array $options
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function load(array $options = array())
    {
        $ids   = $this->getOptionIds($options);

        $query = $this->db->getQuery(true);
        $query
            ->select(
                'a.id, a.name, a.alias, a.filename, a.category, a.source, a.path, ' .
                'a.i18n_type, a.source_language_code, a.project_id'
            )
            ->from($this->db->quoteName('#__itptfx_resources', 'a'))
            ->order('a.name ASC');

        // Filter by project ID.
        $packageId = ArrayHelper::getValue($options, 'package_id', 0, 'int');
        if ($packageId > 0) {
            // Get package resources IDs.
            $subQuery = $this->db->getQuery(true);
            $subQuery
                ->select('a.resource_id')
                ->from($this->db->quoteName('#__itptfx_packages_map', 'a'))
                ->where('a.package_id = ' . (int)$packageId);

            $this->db->setQuery($subQuery);
            $ids_ = (array)$this->db->loadColumn();

            // Merge the IDs of the resourced based on package ID
            // and the IDs provided by the developer.
            if (count($ids_) > 0) {
                $ids = array_merge($ids, $ids_);
                $ids = array_unique($ids);
            }

            unset($ids_);

            // Filter by IDs.
            if (count($ids) > 0) {
                $query->where('a.id IN ( ' . implode(',', $ids) . ' )');
            } else {
                $query->where('a.id = 0');
            }
        }

        // Filter by IDs.
        if (count($ids) > 0 and !$packageId) {
            $query->where('a.id IN ( ' . implode(',', $ids) . ' )');
        }

        // Filter by project ID.
        $resourceId = ArrayHelper::getValue($options, 'project_id', 0, 'int');
        if ($resourceId > 0) {
            $query->where('a.project_id = ' . (int)$resourceId);
        }

        // Filter by state
        $state = ArrayHelper::getValue($options, 'state');
        if ($state !== null and is_numeric($state)) {
            $query->where('a.published = ' . (int)$state);
        }

        $this->db->setQuery($query);
        $this->items = (array)$this->db->loadAssocList();
    }

    /**
     * Remove resources from database.
     *
     * <code>
     * $data = array(
     *   0 => array("id" => 1),
     *   1 => array("id" => 2),
     *   2 => array("id" => 3),
     * );
     *
     * $resources    = new Transifex\Resource\Resources(\JFactory::getDbo());
     * $resources->set("items", $data);
     *
     * $resources->remove();
     * </code>
     *
     * @throws \RuntimeException
     */
    public function remove()
    {
        if (count($this->items) > 0) {
            foreach ($this->items as $key => $item) {
                // Remove resources in resource map.
                $query = $this->db->getQuery(true);
                $query
                    ->delete($this->db->quoteName('#__itptfx_resources_map'))
                    ->where($this->db->quoteName('resource_id') . '=' . (int)$item['id']);

                $this->db->setQuery($query);
                $this->db->execute();

                // Remove resources
                $query = $this->db->getQuery(true);
                $query
                    ->delete($this->db->quoteName('#__itptfx_resources'))
                    ->where($this->db->quoteName('id') . '=' . (int)$item['id']);

                $this->db->setQuery($query);
                $this->db->execute();

                unset($this->items[$key]);
            }
        }
    }
}
