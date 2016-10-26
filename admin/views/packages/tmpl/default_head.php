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
    <th class="title">
        <?php echo JHtml::_('grid.sort', 'COM_ITPTRANSIFEX_NAME', 'a.name', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="2%" class="center hidden-phone">&nbsp;</th>
    <th width="10%" class="nowrap hidden-phone">
        <?php echo JText::_('COM_ITPTRANSIFEX_FILENAME'); ?>
    </th>
    <th width="10%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_ITPTRANSIFEX_LANGUAGE', 'c.name', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_ITPTRANSIFEX_TYPE', 'a.type', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="20%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_ITPTRANSIFEX_PROJECT', 'b.name', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="6%" class="nowrap center hidden-phone">
        <?php echo JText::_('COM_ITPTRANSIFEX_VERSION'); ?>
    </th>
    <th width="1%" class="nowrap center hidden-phone">
        <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
    </th>
</tr>