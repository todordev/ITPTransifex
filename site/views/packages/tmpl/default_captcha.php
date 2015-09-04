<?php
/**
 * @package      ItpTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$this->document->addScript('media/' . $this->option . '/js/site/packages_captcha.js');
$this->document->addScript('//www.google.com/recaptcha/api.js?onload=transifexOnloadCallback&render=explicit');

$js = '
    var transifexCaptcha;
    var transifexOnloadCallback = function() {
        transifexCaptcha = grecaptcha.render("js-transifex-captcha", {
          "sitekey" : "'.$this->params->get("public_key").'"
        });
    };
';

$this->document->addScriptDeclaration($js);
?>
<div class="modal fade" id="js-modal-package">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo JText::_("COM_ITPTRANSIFEX_CLOSE"); ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?php echo JText::_("COM_ITPTRANSIFEX_DOWNLOAD_LANGUAGE_PACKAGE"); ?></h4>
            </div>
            <div class="modal-body">
                <form method="post" action="<?php echo JRoute::_("index.php?option=com_itptransifex"); ?>" target="_blank" id="js-form-download-project">
                    <p class="lead"><?php echo JText::_("COM_ITPTRANSIFEX_CONFIRM_NOT_ROBOT"); ?></p>
                    <div id="js-transifex-captcha"></div>

                    <input type="hidden" name="id" value="<?php echo $this->project->getId(); ?>" />
                    <input type="hidden" name="package_id" value="" id="js-form-package-id" />
                    <input type="hidden" name="task" value="packages.download" />
                    <?php echo JHtml::_('form.token'); ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="js-modal-btn-download">
                    <span class="glyphicon glyphicon-download" aria-hidden="true"></span>
                    <?php echo JText::_("COM_ITPTRANSIFEX_DOWNLOAD"); ?>
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal" id="js-modal-btn-close">
                    <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span>
                    <?php echo JText::_("COM_ITPTRANSIFEX_CLOSE"); ?>
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->