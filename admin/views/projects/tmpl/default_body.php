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
<?php foreach ($this->items as $i => $item) {
    $ordering = ($this->listOrder == 'a.ordering');

    $disableClassName = '';
    $disabledLabel    = '';
    if (!$this->saveOrder) {
        $disabledLabel    = JText::_('JORDERINGDISABLED');
        $disableClassName = 'inactive tip-top';
    }

    $numberOfResources = (!isset($this->numberOfResources[$item->id])) ? 0 : $this->numberOfResources[$item->id]["number"];
    $numberOfPackages = (!isset($this->numberOfPackages[$item->id])) ? 0 : $this->numberOfPackages[$item->id]["number"];
?>
	<tr class="row<?php echo $i % 2; ?>">
        <td class="order nowrap center hidden-phone">
    		<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>" title="<?php echo $disabledLabel ?>">
    			<i class="icon-menu"></i>
    		</span>
            <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
        </td>
        <td class="nowrap center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td class="center">
            <?php echo JHtml::_('jgrid.published', $item->published, $i, "projects."); ?>
        </td>
        <td class="nowrap has-context">
            <a href="<?php echo JRoute::_("index.php?option=com_itptransifex&view=project&layout=edit&id=".(int)$item->id);?>">
                <?php echo $this->escape($item->name); ?>
            </a>
            <?php if ($item->link) {?>
            <a href="<?php echo $item->link;?>" class="btn btn-mini" target="_blank">
                <i class="icon-link"></i>
            </a>
            <?php } ?>
            <div class="small">
                <a href="<?php echo JRoute::_("index.php?option=com_itptransifex&view=resources&id=".(int)$item->id);?>">
                    <?php echo JText::sprintf("COM_ITPTRANSIFEX_RESOURCES_D", $numberOfResources)?>
                </a>
            </div>

            <div class="small">
                <a href="<?php echo JRoute::_("index.php?option=com_itptransifex&view=packages&filter_project=".(int)$item->id);?>">
                    <?php echo JText::sprintf("COM_ITPTRANSIFEX_PACKAGES_D", $numberOfPackages)?>
                </a>
            </div>
        </td>
		<td class="nowrap hidden-phone">
		    <?php echo $this->escape($item->alias); ?>
		</td>
		<td class="nowrap hidden-phone">
		    <?php echo $this->escape($item->filename); ?>
		</td>
        <td class="nowrap center hidden-phone"><?php echo $item->id;?></td>
	</tr>
<?php }?>
	  