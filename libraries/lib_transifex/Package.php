<?php
/**
 * @package      ITPTransifex
 * @subpackage   Packages
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Transifex;

use Prism\Database\Table;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing a package.
 *
 * @package      ITPTransifex
 * @subpackage   Packages
 */
class Package extends Table
{
    protected $id;
    protected $name;
    protected $alias;
    protected $filename;
    protected $description;
    protected $version;
    protected $language;
    protected $type;
    protected $project_id;

    protected $resources;

    /**
     * Load package data.
     *
     * <code>
     * $keys = array(
     *     "alias" => "crowdfunding-component-en_gb"
     * );
     *
     * $package = new Transifex\Package(\JFactory::getDbo());
     * $package->load($keys);
     * </code>
     *
     * @param int|array $keys
     * @param array $options
     */
    public function load($keys, $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.name, a.alias, a.filename, a.description, a.version, a.language, a.type, a.project_id")
            ->from($this->db->quoteName("#__itptfx_packages", "a"));

        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName("a.".$key) . "=" . $this->db->quote($value));
            }
        } else {
            $query->where("a.id = " . (int)$keys);
        }

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!empty($result)) {
            $this->bind($result);
        }

    }

    /**
     * Store package data to database.
     *
     * <code>
     * $data = array(
     *    "name" => "CrowdFunding Package",
     *    "alias" => "crowdfunding-package-en_gb",
     * );
     *
     * $package    = new Transifex\Package(\JFactory::getDbo());
     * $package->bind($data);
     * $package->store();
     * </code>
     */
    public function store()
    {
        if (!$this->id) { // Insert
            $this->insertObject();
        } else { // Update
            $this->updateObject();
        }
    }

    protected function updateObject()
    {
        $description   = (!$this->description) ? "NULL" : $this->db->quote($this->description);

        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName("#__itptfx_packages"))
            ->set($this->db->quoteName("name") . "=" . $this->db->quote($this->name))
            ->set($this->db->quoteName("alias") . "=" . $this->db->quote($this->alias))
            ->set($this->db->quoteName("description") . "=" . $description)
            ->set($this->db->quoteName("filename") . "=" . $this->db->quote($this->filename))
            ->set($this->db->quoteName("version") . "=" . $this->db->quote($this->version))
            ->set($this->db->quoteName("language") . "=" . $this->db->quote($this->language))
            ->set($this->db->quoteName("type") . "=" . $this->db->quote($this->type))
            ->set($this->db->quoteName("project_id") . "=" . (int)$this->project_id)
            ->where($this->db->quoteName("id") ."=". (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    protected function insertObject()
    {
        $description   = (!$this->description) ? "NULL" : $this->db->quote($this->description);

        $query = $this->db->getQuery(true);

        $query
            ->insert($this->db->quoteName("#__itptfx_packages"))
            ->set($this->db->quoteName("name") . "=" . $this->db->quote($this->name))
            ->set($this->db->quoteName("alias") . "=" . $this->db->quote($this->alias))
            ->set($this->db->quoteName("description") . "=" . $description)
            ->set($this->db->quoteName("filename") . "=" . $this->db->quote($this->filename))
            ->set($this->db->quoteName("version") . "=" . $this->db->quote($this->version))
            ->set($this->db->quoteName("language") . "=" . $this->db->quote($this->language))
            ->set($this->db->quoteName("type") . "=" . $this->db->quote($this->type))
            ->set($this->db->quoteName("project_id") . "=" . (int)$this->project_id);

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * @param mixed $alias
     *
     * @return self
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @param mixed $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param mixed $filename
     *
     * @return self
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @param mixed $language
     *
     * @return self
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @param mixed $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param mixed $projectId
     *
     * @return self
     */
    public function setProjectId($projectId)
    {
        $this->project_id = $projectId;

        return $this;
    }

    /**
     * @param mixed $type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param mixed $version
     *
     * @return self
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    public function getResources($state = null)
    {
        if (is_null($this->resources)) {

            // Get package resources.
            $query = $this->db->getQuery(true);
            $query
                ->select("a.resource_id")
                ->from($this->db->quoteName("#__itptfx_packages_map", "a"))
                ->where("a.package_id = " . (int)$this->getId());

            $this->db->setQuery($query);
            $resourcesIds = $this->db->loadColumn();

            // Load resources.

            $options = array(
                "ids" => $resourcesIds,
                "state" => $state
            );

            $this->resources = new Resources($this->db);
            $this->resources->load($options);
        }

        return $this->resources;
    }
}
