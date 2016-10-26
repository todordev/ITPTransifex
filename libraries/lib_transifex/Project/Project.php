<?php
/**
 * @package      Transifex\Project
 * @subpackage   Projects
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Transifex\Project;

use Prism\Database\Table;
use Transifex\Package\Packages;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing a project.
 *
 * @package      Transifex\Project
 * @subpackage   Projects
 */
class Project extends Table
{
    protected $id;
    protected $name;
    protected $alias;
    protected $description;
    protected $source_language_code;
    protected $filename = '';
    protected $image;
    protected $link;
    protected $published = 0;
    protected $ordering = 0;
    protected $last_update = '1000-01-01';

    protected $slug;
    
    protected $packages;

    /**
     * Load project data.
     *
     * <code>
     * $projectId = 1;
     *
     * // Or other keys.
     * $keys = array(
     *     "alias" => "crowdfunding-component-en_gb"
     * );
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     *
     * // Load by package ID.
     * $project->load($projectId);
     *
     * // Load by other keys.
     * $project->load($keys);
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
            ->select(
                'a.id, a.name, a.alias, a.description, a.source_language_code, ' .
                'a.filename, a.link, a.image, a.published, a.ordering, a.last_update, ' .
                $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug'
            )
            ->from($this->db->quoteName('#__itptfx_projects', 'a'));

        if (!is_array($keys)) {
            $query->where('a.id = ' . (int)$keys);
        } else {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName('a.'.$key) . '=' . $this->db->quote($value));
            }
        }

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

        $this->bind($result);
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
     * $project    = new Transifex\Project\Project(\JFactory::getDbo());
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
        $description   = (!$this->description) ? 'NULL' : $this->db->quote($this->description);
        $language   = (!$this->source_language_code) ? 'NULL' : $this->db->quote($this->source_language_code);
        $image      = (!$this->image) ? 'NULL' : $this->db->quote($this->image);

        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName('#__itptfx_projects'))
            ->set($this->db->quoteName('name') . '=' . $this->db->quote($this->name))
            ->set($this->db->quoteName('alias') . '=' . $this->db->quote($this->alias))
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('source_language_code') . '=' . $language)
            ->set($this->db->quoteName('filename') . '=' . $this->db->quote($this->filename))
            ->set($this->db->quoteName('image') . '=' . $image)
            ->set($this->db->quoteName('ordering') . '=' . (int)$this->ordering)
            ->set($this->db->quoteName('published') . '=' . (int)$this->published)
            ->set($this->db->quoteName('last_update') . '=' . $this->db->quote($this->last_update))
            ->where($this->db->quoteName('id') .'='. (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    protected function insertObject()
    {
        $description   = (!$this->description) ? 'NULL' : $this->db->quote($this->description);
        $language   = (!$this->source_language_code) ? 'NULL' : $this->db->quote($this->source_language_code);
        $image      = (!$this->image) ? 'NULL' : $this->db->quote($this->image);

        $query = $this->db->getQuery(true);

        $query
            ->insert($this->db->quoteName('#__itptfx_projects'))
            ->set($this->db->quoteName('name') . '=' . $this->db->quote($this->name))
            ->set($this->db->quoteName('alias') . '=' . $this->db->quote($this->alias))
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('source_language_code') . '=' . $language)
            ->set($this->db->quoteName('filename') . '=' . $this->db->quote($this->filename))
            ->set($this->db->quoteName('image') . '=' . $image)
            ->set($this->db->quoteName('ordering') . '=' . (int)$this->ordering)
            ->set($this->db->quoteName('published') . '=' . (int)$this->published)
            ->set($this->db->quoteName('last_update') . '=' . $this->db->quote($this->last_update));

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
    }

    /**
     * Return the ID of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * if ($project->getId()) {
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
     * Return the name of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * echo $project->getName();
     * </code>
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the alias of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * echo $project->getAlias();
     * </code>
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Return the slug of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * echo $project->getSlug();
     * </code>
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Return the description of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * echo $project->getDescription();
     * </code>
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Return the language of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * echo $project->getLanguage();
     * </code>
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->source_language_code;
    }

    /**
     * Return the filename of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * echo $project->getFilename();
     * </code>
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Return the link of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * echo $project->getLink();
     * </code>
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Return the image of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * echo $project->getImage();
     * </code>
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the name of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $project->setName("Gamification Platform");
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
     * Set the alias of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $project->setAlias("gamification-platform");
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
     * Set the description of the project.
     *
     * <code>
     * $projectId = 1;
     * $description = "...";
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $project->setDescription($description);
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
     * Set the filename of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $project->setFileName("UNZIPFIRST_Gamification");
     * </code>
     *
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
     * Set the language of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $project->setLanguage("de_DE");
     * </code>
     *
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
     * <code>
     * $projectId = 1;
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * $project->setName("Gamification Platform");
     * </code>
     *
     * @param array $options
     * @param bool  $force
     *
     * @throws \RuntimeException
     * @return Packages
     */
    public function getPackages(array $options = array(), $force = false)
    {
        if ($this->packages === null or $force) {
            $options['project_id'] = (int)$this->id;
            
            $this->packages = new Packages(\JFactory::getDbo());
            $this->packages->load($options);
        }

        return $this->packages;
    }

    /**
     * Check if the project published.
     *
     * <code>
     * $projectId = 1;
     *
     * $project = new Transifex\Project\Project(\JFactory::getDbo());
     * $project->load($projectId);
     *
     * if ($project->isPublished()) {
     * ...
     * }
     * </code>
     *
     * @return bool
     */
    public function isPublished()
    {
        return (!$this->published) ? false : true;
    }
}
