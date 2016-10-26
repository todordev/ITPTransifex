<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&#215;</button>
		<h3><?php echo JText::_('COM_ITPTRANSIFEX_BATCH_OPTIONS'); ?></h3>
	</div>
	<div class="modal-body modal-batch">
        <p class="sticky">
            <?php echo JText::_('COM_ITPTRANSIFEX_BATCH_NOTE'); ?>
        </p>
        <form action="<?php echo JRoute::_("index.php?option=com_itptransifex"); ?>" method="post" id="js-itptfx-batch-form">

            <div class="well well-small">
                <label class="radio">
                    <input type="radio" name="action" value="copy" checked />
                    <strong><?php echo JText::_('COM_ITPTRANSIFEX_COPY_PACKAGES'); ?></strong>
                </label>
                <label><?php echo JText::_('COM_ITPTRANSIFEX_LANGUAGE'); ?></label>
                <?php echo JHtml::_('select.genericlist', $this->languages, 'language'); ?>
            </div>

            <div class="well well-small">
                <label class="radio">
                    <input type="radio" name="action" value="change_version" id="new_version_radio" />
                    <strong><?php echo JText::_('COM_ITPTRANSIFEX_CHANGE_VERSION'); ?></strong>
                </label>

                <label for="new_version"><?php echo JText::_('COM_ITPTRANSIFEX_NEW_VERSION'); ?></label>
                <input type="text" name="version" id="new_version" value="" class="input-xxlarge" />
            </div>

            <div class="well well-small">
                <label class="radio">
                    <input type="radio" name="action" value="replace_string" id="replace_text_radio"/>
                    <strong><?php echo JText::_('COM_ITPTRANSIFEX_REPLACE_TEXT'); ?></strong>
                </label>

                <label for="search_string"><?php echo JText::_('COM_ITPTRANSIFEX_SEARCH'); ?></label>
                <input type="text" name="search_string" id="search_string" value="" class="input-xxlarge" />

                <label for="replace_string"><?php echo JText::_('COM_ITPTRANSIFEX_REPLACE'); ?></label>
                <input type="text" name="replace_string" id="replace_string" value="" class="input-xxlarge" />
            </div>

            <input type="hidden" name="task" value="package.batch" />
            <input type="hidden" name="format" value="raw" />
            <?php echo JHtml::_('form.token'); ?>

        </form>
	</div>
	<div class="modal-footer">
        <img src="../media/com_itptransifex/images/ajax-loader.gif" width="16" height="16" style="display: none;" id="js-batch-ajaxloader" />
        <button class="btn btn-primary" type="submit" id="js-itptfx-btn-batch">
            <?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
        </button>
		<button class="btn" type="button" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
	</div>
</div>
