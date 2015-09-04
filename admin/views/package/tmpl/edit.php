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
<div class="row-fluid">
	<div class="span6 form-horizontal">
        <form  action="<?php echo JRoute::_('index.php?option=com_itptransifex&layout=edit'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" >
            <fieldset>
                
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('alias'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('alias'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('filename'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('filename'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('language'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('language'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('type'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('type'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('version'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('version'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
                </div>
                
            </fieldset>
        
            <?php echo $this->form->getInput('project_id'); ?>
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>

<?php if ($this->item->id) { ?>
<div class="row-fluid">

    <div class="span6">
        <h3>
            <?php echo JText::_("COM_ITPTRANSIFEX_RESOURCES"); ?>
            <img src="../media/com_itptransifex/images/ajax-loader.gif" width="16" height="16" style="display: none;" id="js-ajaxloader"/>
        </h3>

        <button class="btn btn-success mtp-10" id="itptfx-btn-add">
            <i class="icon-plus"></i>
            <?php echo JText::_("COM_ITPTRANSIFEX_ADD"); ?>
        </button>

        <div id="itptfx-add-resource" style="display: none;">
            <form>
                <input type="text" name="resource" value="" class="input-xxlarge" id="itptfx-resource-input" placeholder="<?php echo JText::_("COM_ITPTRANSIFEX_TYPE_RESOURCE_NAME"); ?>" />
            </form>
        </div>

        <?php if(!empty($this->items)) {?>
        <table class="table table-bordered" id="resources-list">
            <thead>
                <tr>
                    <th><?php echo JText::_("COM_ITPTRANSIFEX_RESOURCE"); ?></th>
                    <th><?php echo JText::_("COM_ITPTRANSIFEX_ACTION"); ?></th>
                </tr>
            </thead>
            <tbody id="itptfx-resource-wrapper">
            <?php foreach ($this->items as $resource) { ?>
                <tr id="resource-id<?php echo $resource["id"];?>">
                    <td class="nowrap">
                        <?php echo $this->escape($resource["name"]); ?>
                        <div class="small">
                            <?php echo JText::sprintf("COM_ITPTRANSIFEX_ALIAS_S", $resource["alias"]); ?>
                        </div>
                    </td>
                    <td>
                        <a href="<?php echo JRoute::_("index.php?option=com_itptransifex&task=package.removeResource&format=raw"); ?>"
                           data-rid="<?php echo $resource["id"];?>"
                           data-pid="<?php echo $this->item->id; ?>"
                           class="btn btn-danger itptfx-btn-remove"
                         >
                            <i class="icon-trash"></i>
                            <?php echo JText::_("COM_ITPTRANSIFEX_REMOVE"); ?>
                        </a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php }?>
    </div>
</div>
<?php } ?>