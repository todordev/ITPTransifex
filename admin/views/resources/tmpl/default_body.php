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
        <td class="center">
			<?php echo JHtml::_('jgrid.published', $item->published, $i, 'resources.'); ?>
		</td>
        <td class="nowrap">
            <a href="<?php echo JRoute::_("index.php?option=com_itptransifex&view=resource&layout=edit&id=".(int)$item->id);?>">
                <?php echo $this->escape($item->name); ?>
            </a>
        </td>
        <td class="nowrap hidden-phone">
		    <?php echo $this->escape($item->alias); ?>
		</td>
		<td class="nowrap hidden-phone">
		    <?php echo $this->escape($item->filename); ?>
		</td>
		<td class="nowrap center hidden-phone">
		    <?php echo $this->escape($item->type); ?>
		</td>
		<td class="nowrap center hidden-phone">
		  <?php echo $this->escape($item->source_language_code); ?>
		</td>
        <td class="nowrap center hidden-phone">
            <?php echo $item->id;?>
        </td>
	</tr>
<?php }?>
	  