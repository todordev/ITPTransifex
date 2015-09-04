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

// Set default value of the language field.
$packageLanguage = JFactory::getApplication()->getUserState("package.language");
if (!empty($packageLanguage)) {
    $this->form->setValue("language", null, $packageLanguage);
}
?>
<div class="modal hide fade" id="js-cp-modal">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3><?php echo JText::_("COM_ITPTRANSIFEX_PACKAGE_OPTIONS");?></h3>

    </div>
    <div class="modal-body">

        <form action="<?php echo JRoute::_('index.php?option=com_itptransifex'); ?>" method="post" name="packageForm" id="packageForm">

            <div class="row-fluid">
                <div class="span12">
                    <?php echo $this->form->getControlGroup('name'); ?>
                    <?php echo $this->form->getControlGroup('alias'); ?>
                    <?php echo $this->form->getControlGroup('filename'); ?>
                    <?php echo $this->form->getControlGroup('language'); ?>
                    <?php echo $this->form->getControlGroup('type'); ?>
                    <?php echo $this->form->getControlGroup('version'); ?>
                    <?php echo $this->form->getControlGroup('description'); ?>
                </div>
            </div>

            <input type="hidden" name="task" value="package.create" />
            <input type="hidden" name="format" value="raw" />
            <input type="hidden" name="project_id" value="<?php echo $this->projectId; ?>" />
        </form>
    </div>
    <div class="modal-footer">
        <img src="../media/com_itptransifex/images/ajax-loader.gif" width="16" height="16" style="display: none;" id="js-ajaxloader" />
        <a href="#" class="btn btn-primary" id="js-btn-sp"><?php echo JText::_("COM_ITPTRANSIFEX_SUBMIT");?></a>
        <a href="#" class="btn" id="js-btn-cp-cancel"><?php echo JText::_("COM_ITPTRANSIFEX_CANCEL");?></a>
    </div>
</div>