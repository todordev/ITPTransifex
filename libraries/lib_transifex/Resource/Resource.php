<?php
/**
 * @package      Transifex\Resource
 * @subpackage   Resources
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Transifex\Resource;

use Prism\Database\Table;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing a resource.
 *
 * @package      Transifex\Resource
 * @subpackage   Resources
 */
class Resource extends Table
{
    protected $id;
    protected $name;
    protected $alias;
    protected $filename;
    protected $type;
    protected $i18n_type;
    protected $source_language_code;
    protected $published;
    protected $project_id;

    /**
     * Load package data.
     *
     * <code>
     * $keys = array(
     *     "alias" => "site-com_crowdfunding"
     * );
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $resource->load($keys);
     *
     * @param int|array $keys
     * @param array $options
     */
    public function load($keys, $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.name, a.alias, a.filename, a.type, a.i18n_type, a.source_language_code, a.published, a.project_id")
            ->from($this->db->quoteName("#__itptfx_resources", "a"));

        if (!is_array($keys)) {
            $query->where("a.id = " . (int)$keys);
        } else {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName("a.".$key) . "=" . $this->db->quote($value));
            }
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
     *    "name" => "[SITE] en-GB.com_crowdfunding.ini",
     *    "alias" => "site-com_crowdfunding",
     * );
     *
     * $resource    = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $resource->bind($data);
     * $resource->store();
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
        $filename    = (!$this->filename) ? "NULL" : $this->db->quote($this->filename);
        $type        = (!$this->type) ? "NULL" : $this->db->quote($this->type);
        $i18n_type   = (!$this->i18n_type) ? "NULL" : $this->db->quote($this->i18n_type);
        $language    = (!$this->source_language_code) ? "NULL" : $this->db->quote($this->source_language_code);

        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName("#__itptfx_resources"))
            ->set($this->db->quoteName("name") . "=" . $this->db->quote($this->name))
            ->set($this->db->quoteName("alias") . "=" . $this->db->quote($this->alias))
            ->set($this->db->quoteName("filename") . "=" . $filename)
            ->set($this->db->quoteName("type") . "=" . $type)
            ->set($this->db->quoteName("i18n_type") . "=" . $i18n_type)
            ->set($this->db->quoteName("published") . "=" . (int)$this->published)
            ->set($this->db->quoteName("source_language_code") . "=" . $language)
            ->set($this->db->quoteName("project_id") . "=" . (int)$this->project_id)
            ->where($this->db->quoteName("id") ."=". (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    protected function insertObject()
    {
        $filename    = (!$this->filename) ? "NULL" : $this->db->quote($this->filename);
        $type        = (!$this->type) ? "NULL" : $this->db->quote($this->type);
        $i18n_type   = (!$this->i18n_type) ? "NULL" : $this->db->quote($this->i18n_type);
        $language    = (!$this->source_language_code) ? "NULL" : $this->db->quote($this->source_language_code);

        $query = $this->db->getQuery(true);

        $query
            ->insert($this->db->quoteName("#__itptfx_resources"))
            ->set($this->db->quoteName("name") . "=" . $this->db->quote($this->name))
            ->set($this->db->quoteName("alias") . "=" . $this->db->quote($this->alias))
            ->set($this->db->quoteName("filename") . "=" . $filename)
            ->set($this->db->quoteName("type") . "=" . $type)
            ->set($this->db->quoteName("i18n_type") . "=" . $i18n_type)
            ->set($this->db->quoteName("published") . "=" . (int)$this->published)
            ->set($this->db->quoteName("source_language_code") . "=" . $language)
            ->set($this->db->quoteName("project_id") . "=" . (int)$this->project_id);

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
    }

    /**
     * Return the ID of the resource.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * if ($resource->getId()) {
     * ...
     * }
     * </code>
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return the name of the resource.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * echo $resource->getName();
     * </code>
     *
     * @return int
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the alias of the resource.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * echo $resource->getAlias();
     * </code>
     *
     * @return int
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Return the filename of the resource.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * echo $resource->getFilename();
     * </code>
     *
     * @return int
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Return the I18n type of the resource.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * echo $resource->getI18nType();
     * </code>
     *
     * @return int
     */
    public function getI18nType()
    {
        return $this->i18n_type;
    }

    /**
     * Check if the resource published.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * if (!$resource->isPublished()) {
     * ...
     * }
     * </code>
     *
     * @return int
     */
    public function isPublished()
    {
        return (!$this->published) ? false : true;
    }

    /**
     * Return the source language code of the resource.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * echo $resource->getSourceLanguageCode();
     * </code>
     *
     * @return int
     */
    public function getSourceLanguageCode()
    {
        return $this->source_language_code;
    }

    /**
     * Return the type of the resource.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * echo $resource->getType();
     * </code>
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return the project ID of the resource.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * echo $resource->getProjectId();
     * </code>
     *
     * @return int
     */
    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * Set the alias of the resource.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * $resource->setAlias("admin-com_crowdfunding");
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
     * Set the state of the resource.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * $resource->setState(Prism\Constants::PUBLISHED);
     * </code>
     *
     * @param int $state State of current resource - 1 = published, 0 = unpublished.
     *
     * @return self
     */
    public function setState($state)
    {
        $this->published = $state;

        return $this;
    }

    /**
     * Set the state of the resource.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * $resource->setState("INI");
     *
     * @param string $i18n_type
     *
     * @return self
     */
    public function setI18nType($i18n_type)
    {
        $this->i18n_type = $i18n_type;

        return $this;
    }

    /**
     * Set the filename of the resource.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * $resource->setFilename("com_gamification.ini");
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
     * Set the source language code of the resource.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * $resource->setSourceLanguageCode("en_GB");
     *
     * @param string $code
     *
     * @return self
     */
    public function setSourceLanguageCode($code)
    {
        $this->source_language_code = $code;

        return $this;
    }

    /**
     * Set the name of the resource.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * $resource->setName("[ADMIN] en-GB.com_crowdfunding.ini");
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
     * Set the project ID of the resource.
     *
     * <code>
     * $resourceId = 1;
     * $projectId = 2;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * $resource->setProjectId($projectId);
     *
     * @param int $projectId
     *
     * @return self
     */
    public function setProjectId($projectId)
    {
        $this->project_id = $projectId;

        return $this;
    }

    /**
     * Set the type of the resource.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource = new Transifex\Resource\Resource(\JFactory::getDbo());
     * $project->load($resourceId);
     *
     * $resource->setType("site");
     *
     * @param string $type The type of the resource - site or admin.
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}
