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
<?php foreach ($this->items as $i => $item) {?>
	<tr class="row<?php echo $i % 2; ?>">
        <td class="nowrap center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td class="nowrap">
            <a href="<?php echo JRoute::_('index.php?option=com_itptransifex&view=language&layout=edit&id='.(int)$item->id);?>"><?php echo $this->escape($item->name); ?></a>
        </td>
		<td class="nowrap hidden-phone">
		    <?php echo $this->escape($item->locale); ?>
		</td>
		<td class="nowrap center hidden-phone">
		    <?php echo $this->escape($item->code); ?>
		</td>
        <td class="nowrap center hidden-phone"><?php echo $item->id;?></td>
	</tr>
<?php }?>