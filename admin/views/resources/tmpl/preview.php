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
<?php if(!empty($this->items)) {?>
<table class="table table-striped" id="resourcesList">
    <tbody>
        <?php foreach ($this->items as $i => $item) { ?>
        <tr>
            <td class="nowrap">
                <a href="<?php echo JRoute::_("index.php?option=com_itptransifex&view=resources&id=".(int)$this->packageId."&filter_search=id:".(int)$item["id"]);?>" >
                    <?php echo $this->escape($item["name"]); ?>
                </a>
                <div class="small"><?php echo $this->escape($item["alias"]); ?></div>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<?php }?>