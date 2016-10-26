<?php
/**
 * @package      Transifex\Package
 * @subpackage   Packages
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Transifex\Package;

use Prism\Database\Table;
use Transifex\Resource\Resources;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing a package.
 *
 * @package      Transifex\Package
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
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($keys);
     * </code>
     *
     * @param int|array $keys
     * @param array $options
     *
     * @throws \RuntimeException
     */
    public function load($keys, array $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('a.id, a.name, a.alias, a.filename, a.description, a.version, a.language, a.type, a.project_id')
            ->from($this->db->quoteName('#__itptfx_packages', 'a'));

        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName('a.'.$key) . '=' . $this->db->quote($value));
            }
        } else {
            $query->where('a.id = ' . (int)$keys);
        }

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

        $this->bind($result);
    }

    /**
     * Store package data to database.
     *
     * <code>
     * $data = array(
     *    "name"  => "CrowdFunding Package",
     *    "alias" => "crowdfunding-package-en_gb",
     * );
     *
     * $package    = new Transifex\Package\Package(\JFactory::getDbo());
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
        $description   = (!$this->description) ? 'NULL' : $this->db->quote($this->description);

        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName('#__itptfx_packages'))
            ->set($this->db->quoteName('name') . '=' . $this->db->quote($this->name))
            ->set($this->db->quoteName('alias') . '=' . $this->db->quote($this->alias))
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('filename') . '=' . $this->db->quote($this->filename))
            ->set($this->db->quoteName('version') . '=' . $this->db->quote($this->version))
            ->set($this->db->quoteName('language') . '=' . $this->db->quote($this->language))
            ->set($this->db->quoteName('type') . '=' . $this->db->quote($this->type))
            ->set($this->db->quoteName('project_id') . '=' . (int)$this->project_id)
            ->where($this->db->quoteName('id') .'='. (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    protected function insertObject()
    {
        $description   = (!$this->description) ? 'NULL' : $this->db->quote($this->description);

        $query = $this->db->getQuery(true);

        $query
            ->insert($this->db->quoteName('#__itptfx_packages'))
            ->set($this->db->quoteName('name') . '=' . $this->db->quote($this->name))
            ->set($this->db->quoteName('alias') . '=' . $this->db->quote($this->alias))
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('filename') . '=' . $this->db->quote($this->filename))
            ->set($this->db->quoteName('version') . '=' . $this->db->quote($this->version))
            ->set($this->db->quoteName('language') . '=' . $this->db->quote($this->language))
            ->set($this->db->quoteName('type') . '=' . $this->db->quote($this->type))
            ->set($this->db->quoteName('project_id') . '=' . (int)$this->project_id);

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
    }

    /**
     * Return the ID of the package.
     *
     * <code>
     * $id = 1;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * if (!$this->getId()) {
     * ...
     * }
     * </code>
     *
     * @return int
     */
    public function getId()
    {
        return (int)$this->id;
    }

    /**
     * Return the name of the package.
     *
     * <code>
     * $id = 1;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * echo $this->getName();
     * </code>
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the alias of the package.
     *
     * <code>
     * $id = 1;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * echo $this->getAlias();
     * </code>
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Return the filename of the package.
     *
     * <code>
     * $id = 1;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * echo $this->getFilename();
     * </code>
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Return the description of the package.
     *
     * <code>
     * $id = 1;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * echo $this->getDescription();
     * </code>
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Return the version of the package.
     *
     * <code>
     * $id = 1;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * echo $this->getVersion();
     * </code>
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Return the language of the package.
     *
     * <code>
     * $id = 1;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * echo $this->getLanguage();
     * </code>
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Return the type of the package.
     *
     * <code>
     * $id = 1;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * echo $this->getType();
     * </code>
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return the project ID of the package.
     *
     * <code>
     * $id = 1;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * echo $this->getProject();
     * </code>
     *
     * @return string
     */
    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * Set the alias of the package.
     *
     * <code>
     * $id = 1;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * $this->setAlias("mod-gamification-de_DE");
     * </code>
     *
     * @param string $alias
     *
     * @return self
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Set the description of the package.
     *
     * <code>
     * $id = 1;
     * $description = "...";
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * $this->setDescription($description);
     * </code>
     *
     * @param string $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the description of the package.
     *
     * <code>
     * $id = 1;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * $this->setFilename("lang_mod_gamification");
     * </code>
     *
     * @param string $filename
     *
     * @return self
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Set the language of the package.
     *
     * <code>
     * $id = 1;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * $this->setLanguage("de_DE");
     * </code>
     *
     * @param string $language
     *
     * @return self
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Set the name of the package.
     *
     * <code>
     * $id = 1;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * $this->setName("Module - Gamification");
     * </code>
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the project ID of the package.
     *
     * <code>
     * $id = 1;
     * $projectId = 2;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * $this->setProjectId($projectId);
     * </code>
     *
     * @param int $projectId
     *
     * @return self
     */
    public function setProjectId($projectId)
    {
        $this->project_id = (int)$projectId;

        return $this;
    }

    /**
     * Set the type of the package.
     *
     * <code>
     * $id = 1;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * $this->setType("module");
     * </code>
     *
     * @param string $type The package type - component, module, plugin, library.
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set the version of the package.
     *
     * <code>
     * $id = 1;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * $this->setVersion("1.0");
     * </code>
     *
     * @param string $version
     *
     * @return self
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Return the resources assigned to the package.
     *
     * <code>
     * $id = 1;
     *
     * $package = new Transifex\Package\Package(\JFactory::getDbo());
     * $package->load($id);
     *
     * $this->setVersion("1.0");
     * </code>
     *
     * @param int $state The state of the resources - 0 = unpublished, 1 = published, -2 = trashed.
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return Resources
     */
    public function getResources($state = null)
    {
        if ($this->resources === null) {
            // Get package resources.
            $query = $this->db->getQuery(true);
            $query
                ->select('a.resource_id')
                ->from($this->db->quoteName('#__itptfx_packages_map', 'a'))
                ->where('a.package_id = ' . (int)$this->getId());

            $this->db->setQuery($query);
            $resourcesIds = $this->db->loadColumn();

            // Load resources.

            $options = array(
                'ids'  => $resourcesIds,
                'state' => $state
            );

            $this->resources = new Resources($this->db);
            $this->resources->load($options);
        }

        return $this->resources;
    }
}
