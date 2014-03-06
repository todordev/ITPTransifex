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
 * This class contains methods that are used for managing a package.
 *
 * @package      ITPTransifex
 * @subpackage   Libraries
 */
class ItpTransifexPackage {
    
    protected $id;
    protected $name;
    protected $filename;
    protected $description;
    protected $version;
    protected $language;
    protected $type;
    protected $hash;
    protected $project_id;
    
    protected $resources;
    
    protected $db;
    
    public function __construct(JDatabase $db) {
        $this->db = $db;
    }
    
    public function load($id) {
        
        $query = $this->db->getQuery(true);
        
        $query
            ->select("a.id, a.name, a.filename, a.description, a.version, a.language, a.type, a.hash, a.project_id")
            ->from($this->db->quoteName("#__itptfx_packages", "a"))
            ->where("a.id = " .(int)$id); 
        
        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();
        
        if(!empty($result)) {
            $this->bind($result);
        }
        
    }
    
    public function bind($data, $ignore = array()) {
        
        foreach($data as $key => $value) {
            
            if(!in_array($key, $ignore)) {
                $this->$key = $value;
            }            
            
        }
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getFilename() {
        return $this->filename;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
	public function getVersion() {
        return $this->version;
    }

	public function getLanguage() {
        return $this->language;
    }
    
	public function getType() {
        return $this->type;
    }

	public function getHash() {
        return $this->hash;
    }

	public function getProjectId() {
        return $this->project_id;
    }
    
    public function getResources($state = null) {
        
        if(is_null($this->resources)) {
            
            // Get package resources.
            $query = $this->db->getQuery(true);
            $query
                ->select("a.resource_id")
                ->from($this->db->quoteName("#__itptfx_packages_map", "a"))
                ->where("a.package_id = ".(int)$this->getId());
            
            $this->db->setQuery($query);
            $resourcesIds = $this->db->loadColumn();

            // Load resources.
            $resources = new ItpTransifexResources($this->db);
            $resources->load($resourcesIds, $state);
            
            $this->resources = $resources;
        }
        
        return $this->resources;
    }

}
