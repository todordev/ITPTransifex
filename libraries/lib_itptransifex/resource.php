<?php
/**
 * @package      ITPTransifex
 * @subpackage   Libraries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

jimport("itptransifex.resources");

/**
 * This class contains methods that are used for managing a resource.
 *
 * @package      ITPTransifex
 * @subpackage   Libraries
 */
class ItpTransifexResource
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
     * Database driver.
     *
     * @var JDatabaseDriver
     */
    protected $db;

    public function __construct(JDatabaseDriver $db)
    {
        $this->db = $db;
    }

    /**
     * Load package data.
     *
     * <code>
     * $keys = array(
     *     "alias" => "site-com_crowdfunding"
     * );
     *
     * $package = new ItpTransifexResource(JFactory::getDbo());
     * $package->load($keys);
     *
     * @param int|array $keys
     */
    public function load($keys)
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

    public function bind($data, $ignore = array())
    {
        foreach ($data as $key => $value) {

            if (!in_array($key, $ignore)) {
                $this->$key = $value;
            }

        }
    }

    /**
     * Store package data to database.
     *
     * <code>
     * $data = array(
     *  "name" => "[SITE] en-GB.com_crowdfunding.ini",
     *  "alias" => "site-com_crowdfunding",
     * );
     *
     * $package    = new ItpTransifexResource(JFactory::getDbo());
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
            ->set($this->db->quoteName("project_id") . "=" . $this->db->quote($this->project_id))
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
            ->set($this->db->quoteName("project_id") . "=" . $this->db->quote($this->project_id));

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

    public function getI18nType()
    {
        return $this->i18n_type;
    }

    public function isPublished()
    {
        return (!$this->published) ? false : true;
    }

    public function getSourceLanguageCode()
    {
        return $this->source_language_code;
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
     * @param mixed $state State of current resource - 1 = published, 0 = unpublished.
     *
     * @return self
     */
    public function setState($state)
    {
        $this->published = $state;

        return $this;
    }

    /**
     * @param mixed $i18n_type
     *
     * @return self
     */
    public function setI18nType($i18n_type)
    {
        $this->i18n_type = $i18n_type;

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
     * @param mixed $code
     *
     * @return self
     */
    public function setSourceLanguageCode($code)
    {
        $this->source_language_code = $code;

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
}
