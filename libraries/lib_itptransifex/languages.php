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
 * This class provieds functionality that manage languages.
 */
class ItpTransifexLanguages implements Iterator, Countable, ArrayAccess {
    
    protected $items  = array();
    
    /**
     * Database driver.
     * 
     * @var JDatabaseMySQLi
     */
    protected $db;
    
    protected $position = 0;
    
    /**
     * Initialize the object.
     * 
     * @param JDatabase Database object.
     */
    public function __construct(JDatabase $db) {
        $this->db = $db;
    }

    public function load($ids = array()) {
        
        // Load project data
        $query = $this->getQuery();
        
        if(!empty($ids)) {
            JArrayHelper::toInteger($ids);
            $query->where("a.id IN ( " . implode(",", $ids) ." )");
        }
        
        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();
        
        if(!$results) {
            $results = array();
        }
        
        $this->items = $results;
    }
    
    public function loadByCode($ids = array()) {
    
        $query = $this->getQuery();
    
        if(!empty($ids)) {
            foreach($ids as $key => $value) {
                $ids[$key] = $this->db->quote($value);
            }
            $query->where("a.code IN ( " . implode(",", $ids) ." )");
        }
    
        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();
    
        if(!$results) {
            $results = array();
        }
    
        $this->items = $results;
    }
    
    protected function getQuery() {
        
        // Load project data
        $query = $this->db->getQuery(true);
        
        $query
            ->select('a.id, a.name, a.code, a.short_code')
            ->from($this->db->quoteName('#__itptfx_languages', 'a'))
            ->order("a.name ASC");
            
        return $query;
    }
    
    public function rewind() {
        $this->position = 0;
    }
    
    public function current() {
        return (!isset($this->items[$this->position])) ? null : $this->items[$this->position];
    }
    
    public function key() {
        return $this->position;
    }
    
    public function next() {
        ++$this->position;
    }
    
    public function valid() {
        return isset($this->items[$this->position]);
    }
    
    public function count() {
        return (int)count($this->items);
    }
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }
    
    public function offsetExists($offset) {
        return isset($this->items[$offset]);
    }
    
    public function offsetUnset($offset) {
        unset($this->items[$offset]);
    }
    
    public function offsetGet($offset) {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    public function toOptions() {
        
        $options = array();
        
        foreach($this->items as $item) {
            $options[] = array(
                "text"  => $item["name"],
                "value" => $item["code"]
            );
        }
        
        return $options;
    }
    
}
