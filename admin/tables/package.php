<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

class ItpTransifexTablePackage extends JTable {
    
	public function __construct($db) {
        parent::__construct('#__itptfx_packages', 'id', $db);
    }
    
}