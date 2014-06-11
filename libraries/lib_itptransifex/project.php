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
 * This class contains methods that are used for managing a project.
 *
 * @package      ITPTransifex
 * @subpackage   Libraries
 */
class ItpTransifexProject
{
    protected $id;
    protected $name;
    protected $alias;
    protected $description;
    protected $source_language_code;
    protected $filename;

    protected $packages;

    /**
     * Database driver
     *
     * @var JDatabaseDriver
     */
    protected $db;

    public function __construct(JDatabaseDriver $db)
    {
        $this->db = $db;
    }

    /**
     * Load project data.
     *
     * @param $keys
     */
    public function load($keys)
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.name, a.alias, a.description, a.source_language_code, a.filename")
            ->from($this->db->quoteName("#__itptfx_projects", "a"));

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
     * Store project data to database.
     *
     * <code>
     * $data = array(
     *  "name" => "CrowdFunding",
     *  "alias" => "crowdfunding",
     * );
     *
     * $project    = new ItpTransifexProject(JFactory::getDbo());
     * $project->bind($data);
     * $project->store();
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
        $filename   = (!$this->filename) ? "NULL" : $this->db->quote($this->filename);
        $language   = (!$this->source_language_code) ? "NULL" : $this->db->quote($this->source_language_code);

        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName("#__itptfx_projects"))
            ->set($this->db->quoteName("name") . "=" . $this->db->quote($this->name))
            ->set($this->db->quoteName("alias") . "=" . $this->db->quote($this->alias))
            ->set($this->db->quoteName("description") . "=" . $description)
            ->set($this->db->quoteName("source_language_code") . "=" . $language)
            ->set($this->db->quoteName("filename") . "=" . $filename)
            ->where($this->db->quoteName("id") ."=". (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    protected function insertObject()
    {
        $description   = (!$this->description) ? "NULL" : $this->db->quote($this->description);
        $filename   = (!$this->filename) ? "NULL" : $this->db->quote($this->filename);
        $language   = (!$this->source_language_code) ? "NULL" : $this->db->quote($this->source_language_code);

        $query = $this->db->getQuery(true);

        $query
            ->insert($this->db->quoteName("#__itptfx_projects"))
            ->set($this->db->quoteName("name") . "=" . $this->db->quote($this->name))
            ->set($this->db->quoteName("alias") . "=" . $this->db->quote($this->alias))
            ->set($this->db->quoteName("description") . "=" . $description)
            ->set($this->db->quoteName("source_language_code") . "=" . $language)
            ->set($this->db->quoteName("filename") . "=" . $filename);

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

    public function getDescription()
    {
        return $this->description;
    }

    public function getLanguage()
    {
        return $this->source_language_code;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    /**
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
     * @param string $filename
     *
     * @return self
     */
    public function setFileName($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @param string $language
     *
     * @return self
     */
    public function setLanguage($language)
    {
        $this->source_language_code = $language;
        return $this;
    }


    /**
     * Return the packages of current project.
     *
     * @param array $options
     * @param bool  $force
     *
     * @return ItpTransifexPackages
     */
    public function getPackages($options = array(), $force = false)
    {
        if (is_null($this->packages) or $force) {

            jimport("itptransifex.packages");
            $this->packages = new ItpTransifexPackages(JFactory::getDbo());
            $this->packages->loadByProjectId($this->id, $options);
        }

        return $this->packages;
    }
}
