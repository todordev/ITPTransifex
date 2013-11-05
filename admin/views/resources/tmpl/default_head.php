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
    <th width="1%" style="min-width:55px" class="nowrap center">
		<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.published', $this->listDirn, $this->listOrder); ?>
	</th>
	<th class="title" >
	     <?php echo JHtml::_('grid.sort',  'COM_ITPTRANSIFEX_NAME', 'a.name', $this->listDirn, $this->listOrder); ?>
	</th>
	<th width="20%" class="nowrap center hidden-phone">
	    <?php echo JText::_("COM_ITPTRANSIFEX_SLUG"); ?>
	</th>
	<th width="20%" class="nowrap center hidden-phone">
	    <?php echo JText::_("COM_ITPTRANSIFEX_FILENAME"); ?>
	</th>
	<th width="10%" class="nowrap center hidden-phone">
	    <?php echo JText::_("COM_ITPTRANSIFEX_TYPE"); ?>
	</th>
	<th width="20%" class="nowrap center hidden-phone">
	    <?php echo JText::_("COM_ITPTRANSIFEX_CATEGORY"); ?>
	</th>
	<th width="10%" class="nowrap center hidden-phone">
	    <?php echo JText::_("COM_ITPTRANSIFEX_SOURCE_LANGUAGE_CODE"); ?>
	</th>
    <th width="1%" class="nowrap center hidden-phone">
         <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
    </th>
</tr>
	  