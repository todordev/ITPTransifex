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
<?php foreach ($this->items as $i => $item) {?>
	<tr class="row<?php echo $i % 2; ?>">
        <td class="nowrap center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td class="nowrap">
            <a href="<?php echo JRoute::_("index.php?option=com_itptransifex&view=package&layout=edit&id=".(int)$item->id);?>"><?php echo $this->escape($item->name); ?></a>
            <div class="small">
                <?php echo JText::sprintf("COM_ITPTRANSIFEX_PROJECT_S", $item->title);?>
            </div>
        </td>
		<td class="nowrap hidden-phone">
		    <?php echo $this->escape($item->filename); ?>
		</td>
		<td class="nowrap hidden-phone">
		    <?php echo $this->escape($item->language_name); ?>
		</td>
		<td class="nowrap center hidden-phone">
		    <?php echo $this->escape($item->language); ?>
		</td>
		<td class="nowrap center hidden-phone">
		    <?php echo $this->escape($item->type); ?>
		</td>
		<td class="nowrap center hidden-phone">
		    <?php echo $this->escape($item->version); ?>
		</td>
		<td class="nowrap center hidden-phone">
		    <a href="<?php echo JRoute::_("index.php?option=com_itptransifex&view=resources&format=raw&package_id=".(int)$item->id);?>" class="btn js-btn-resources-list">
                <i class="icon-eye-open"></i> 
                ( <?php echo $this->numberOfResources[$item->id]->number; ?> )
            </a>
		</td>
        <td class="nowrap center hidden-phone"><?php echo $item->id;?></td>
	</tr>
<?php }?>
	  