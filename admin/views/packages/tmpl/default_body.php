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
    $numberOfResources = (!isset($this->numberOfResources[$item->id])) ? 0 : $this->numberOfResources[$item->id]["number"];
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="nowrap center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td class="nowrap has-context">
            <a href="<?php echo JRoute::_("index.php?option=com_itptransifex&view=package&layout=edit&id=" . (int)$item->id); ?>">
                <?php echo $this->escape($item->name); ?>
            </a>

            <div class="small">
                <a href="<?php echo JRoute::_("index.php?option=com_itptransifex&view=resources&id=".(int)$item->project_id."&filter_search=pid:" . (int)$item->id); ?>">
                    <?php echo JText::sprintf("COM_ITPTRANSIFEX_RESOURCES_D", $numberOfResources); ?>
                </a>
            </div>
            <div class="small">
                <?php echo JText::sprintf("COM_ITPTRANSIFEX_ALIAS_S", $this->escape($item->alias)); ?>
            </div>
        </td>
        <td class="nowrap hidden-phone">
            <a href="<?php echo JRoute::_("index.php?option=com_itptransifex&view=resources&format=raw&package_id=" . (int)$item->id); ?>" class="btn js-btn-resources-list">
                <i class="icon-eye-open"></i>
                ( <?php echo $numberOfResources; ?> )
            </a>
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
        <td class="nowrap hidden-phone">
            <?php echo $this->escape($item->type); ?>
        </td>
        <td class="nowrap hidden-phone">
            <a href="<?php echo JRoute::_("index.php?option=com_itptransifex&view=projects&filter_search=id:" . (int)$item->project_id); ?>">
                <?php echo $this->escape($item->title); ?>
            </a>
        </td>
        <td class="nowrap center hidden-phone">
            <?php echo $this->escape($item->version); ?>
        </td>
        <td class="nowrap center hidden-phone"><?php echo $item->id; ?></td>
    </tr>
<?php } ?>
	  