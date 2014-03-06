<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;
?>
<tr>
    <th width="1%" class="nowrap center hidden-phone">
        <?php echo JHtml::_('grid.checkall'); ?>
    </th>
	<th class="title" >
	     <?php echo JHtml::_('grid.sort',  'COM_ITPTRANSIFEX_NAME', 'a.name', $this->listDirn, $this->listOrder); ?>
	</th>
	<th width="20%" class="nowrap hidden-phone">
	    <?php echo JText::_("COM_ITPTRANSIFEX_FILENAME"); ?>
	</th>
	<th width="20%" class="nowrap hidden-phone">
	    <?php echo JHtml::_('grid.sort',  'COM_ITPTRANSIFEX_LANGUAGE', 'c.name', $this->listDirn, $this->listOrder); ?>
	</th>
	<th width="10%" class="nowrap center hidden-phone">
	    <?php echo JText::_("COM_ITPTRANSIFEX_LANG_CODE"); ?>
	</th>
	<th width="10%" class="nowrap center hidden-phone">
	    <?php echo JHtml::_('grid.sort',  'COM_ITPTRANSIFEX_TYPE', 'a.type', $this->listDirn, $this->listOrder); ?>
	</th>
	<th width="10%" class="nowrap center hidden-phone">
	    <?php echo JText::_("COM_ITPTRANSIFEX_VERSION"); ?>
	</th>
	<th width="10%" class="nowrap center hidden-phone">
	    <?php echo JText::_("COM_ITPTRANSIFEX_RESOURCES"); ?>
	</th>
    <th width="1%" class="nowrap center hidden-phone">
         <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
    </th>
</tr>
	  