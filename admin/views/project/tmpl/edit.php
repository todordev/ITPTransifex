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
	<div class="span8 form-horizontal">
        <form  action="<?php echo JRoute::_('index.php?option=com_itptransifex&layout=edit'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
            <fieldset>

                <?php echo $this->form->getControlGroup('name'); ?>
                <?php echo $this->form->getControlGroup('alias'); ?>
                <?php echo $this->form->getControlGroup('filename'); ?>
                <?php echo $this->form->getControlGroup('link'); ?>
                <?php echo $this->form->getControlGroup('id'); ?>
                <?php echo $this->form->getControlGroup('description'); ?>
                <?php echo $this->form->getControlGroup('image'); ?>
                <?php echo $this->form->getControlGroup('published'); ?>

            </fieldset>

            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>

    <div class="span4">
        <?php if (!empty($this->item->image)) { ?>
            <img src="<?php echo $this->imagesUrl . "/" . $this->item->image; ?>"/>

            <div class="clearfix"></div>
            <br/>
            <a href="<?php echo JRoute::_("index.php?option=com_itptransifex&task=project.removeImage&id=" . (int)$this->item->id . "&" . JSession::getFormToken() . "=1"); ?>"
               class="btn btn-danger">
                <i class="icon-trash icon-white"></i>
                <?php echo JText::_("COM_ITPTRANSIFEX_REMOVE_IMAGE"); ?>
            </a>
        <?php } else { ?>
            <img src="../media/com_itptransifex/images/no_image.png"/>
        <?php } ?>
    </div>
</div>