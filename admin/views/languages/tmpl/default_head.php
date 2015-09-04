<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
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
	<th width="10%" class="nowrap hidden-phone">
	    <?php echo JText::_("COM_ITPTRANSIFEX_LOCALE_CODE"); ?>
	</th>
	<th width="10%" class="nowrap center hidden-phone">
	    <?php echo JText::_("COM_ITPTRANSIFEX_COUNTRY_CODE"); ?>
	</th>
    <th width="1%" class="nowrap center hidden-phone">
         <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
    </th>
</tr>
	  