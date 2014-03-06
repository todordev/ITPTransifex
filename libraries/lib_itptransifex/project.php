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
class ItpTransifexProject {
    
    protected $id;
    protected $name;
    protected $alias;
    protected $description;
    protected $source_language_code;
    protected $filename;
    
    protected $db;
    
    public function __construct(JDatabase $db) {
        $this->db = $db;
    }
    
    public function load($id) {
        
        $query = $this->db->getQuery(true);
        
        $query
            ->select("a.id, a.name, a.alias, a.description, a.source_language_code, a.filename")
            ->from($this->db->quoteName("#__itptfx_projects", "a"))
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
    
    public function getAlias() {
        return $this->alias;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
	public function getLanguage() {
        return $this->source_language_code;
    }
    
    public function getFilename() {
        return $this->filename;
    }
    
}
