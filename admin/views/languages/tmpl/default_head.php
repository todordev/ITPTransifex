<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
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
	<th width="20%" class="nowrap center hidden-phone">
	    <?php echo JText::_("COM_ITPTRANSIFEX_LOCALE_CODE"); ?>
	</th>
	<th width="20%" class="nowrap center hidden-phone">
	    <?php echo JText::_("COM_ITPTRANSIFEX_SHORT_CODE"); ?>
	</th>
    <th width="1%" class="nowrap center hidden-phone">
         <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
    </th>
</tr>
	  