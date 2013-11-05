<?php
/**
* @package      ITPTransifex
* @subpackage   Libraries
* @author       Todor Iliev
* @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

defined('JPATH_PLATFORM') or die;

JLoader::register("ItpTransifexTableProject", JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_itptransifex".DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."project.php");

/**
 * This class provieds functionality that manage projects.
 */
class ItpTransifexProject {
    
    /**
     * This is CrowdFunding Currency table object.
     * 
     * @var ItpTransifexTableProject
     */
    protected $table;
    protected static $instances = array();
    
    public function __construct($id = 0) {

        $this->table = new ItpTransifexTableProject(JFactory::getDbo());
        
        if(!empty($id)) {
            $this->table->load($id);
        }
    }
    
    public static function getInstance($id = 0)  {
    
        if (empty(self::$instances[$id])){
            $table = new ItpTransifexProject($id);
            self::$instances[$id] = $table;
        }
    
        return self::$instances[$id];
    }
    
    public function load($keys, $reset = true) {
        $this->table->load($keys, $reset);
    }
    
    public function bind($src, $ignore = array()) {
        $this->table->bind($src, $ignore);
    }
    
    public function store($updateNulls = false) {
        $this->table->store($updateNulls);
    }
    
    public function getFileName() {
        return $this->table->filename;
    }
    
    public function getName() {
        return $this->table->name;
    }
}
