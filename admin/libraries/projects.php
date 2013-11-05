<?php
/**
* @package      ITPTransifex
* @subpackage   Libraries
* @author       Todor Iliev
* @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

defined('JPATH_PLATFORM') or die;

/**
 * This class provieds functionality that manage projects.
 */
class ItptransifexProjects implements Iterator, Countable, ArrayAccess {
    
    public    $projects = array();
    
    /**
     * Database driver
     * 
     * @var JDatabaseMySQLi
     */
    protected $db;
    
    protected $position = 0;
    
    /**
     * Initialize object. 
     */
    public function __construct() {
        $this->db = JFactory::getDbo();
    }
    
    public function load() {
        
        $query = $this->db->getQuery(true);
        
        $query
            ->select("a.id, a.name, a.filename")
            ->from($this->db->quoteName("#__itptfx_projects") . " AS a");
        
        $this->db->setQuery($query);
        $this->projects = $this->db->loadObjectList();
        
        if(!$this->projects) {
            $this->projects = array();
        }
        
        return $this->projects;
    }
    
    public function rewind() {
        $this->position = 0;
    }
    
    public function current() {
        return (!isset($this->projects[$this->position])) ? null : $this->projects[$this->position];
    }
    
    public function key() {
        return $this->position;
    }
    
    public function next() {
        ++$this->position;
    }
    
    public function valid() {
        return isset($this->projects[$this->position]);
    }
    
    public function count() {
        return (int)count($this->projects);
    }
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->projects[] = $value;
        } else {
            $this->projects[$offset] = $value;
        }
    }
    
    public function offsetExists($offset) {
        return isset($this->projects[$offset]);
    }
    
    public function offsetUnset($offset) {
        unset($this->projects[$offset]);
    }
    
    public function offsetGet($offset) {
        return isset($this->projects[$offset]) ? $this->projects[$offset] : null;
    }
}
