<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
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
                    <div class="control-label"><?php echo $this->form->getLabel('type'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('type'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('category'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('category'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('i18n_type'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('i18n_type'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('source_language_code'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('source_language_code'); ?></div>
                </div>
                
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('published'); ?></div>
    				<div class="controls"><?php echo $this->form->getInput('published'); ?></div>
                </div>
                
            </fieldset>
        
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>