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
<?php if(!empty( $this->sidebar)): ?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
<?php else : ?>
<div id="j-main-container">
<?php endif;?>
    <div class="span8">
        
	</div>
	<div class="span4">
		<a href="http://itprism.com/free-joomla-extensions/others/language-packages-manager" target="_blank"><img src="../media/com_itptransifex/images/logo.png" alt="<?php echo JText::_("COM_ITPTRANSIFEX");?>" /></a>
        <a href="http://itprism.com" target="_blank" title="<?php echo JText::_("COM_ITPTRANSIFEX_PRODUCT");?>"><img src="../media/com_itptransifex/images/product_of_itprism.png" alt="<?php echo JText::_("COM_ITPTRANSIFEX_PRODUCT");?>" /></a>
        <p><?php echo JText::_("COM_ITPTRANSIFEX_YOUR_VOTE"); ?></p>
        <p><?php echo JText::_("COM_ITPTRANSIFEX_SUBSCRIPTION"); ?></p>
        
        <table class="table table-striped">
            <tbody>
                <tr>
                    <td><?php echo JText::_("COM_ITPTRANSIFEX_INSTALLED_VERSION");?></td>
                    <td><?php echo $this->version->getShortVersion();?></td>
                </tr>
                <tr>
                    <td><?php echo JText::_("COM_ITPTRANSIFEX_RELEASE_DATE");?></td>
                    <td><?php echo $this->version->releaseDate?></td>
                </tr>
                <tr>
                    <td><?php echo JText::_("COM_ITPTRANSIFEX_PRISM_LIBRARY_VERSION");?></td>
                    <td><?php echo $this->prismVersion;?></td>
                </tr>
                <tr>
                    <td><?php echo JText::_("COM_ITPTRANSIFEX_COPYRIGHT");?></td>
                    <td><?php echo $this->version->copyright;?></td>
                </tr>
                <tr>
                    <td><?php echo JText::_("COM_ITPTRANSIFEX_LICENSE");?></td>
                    <td><?php echo $this->version->license;?></td>
                </tr>
            </tbody>
        </table>

        <?php if (!empty($this->prismVersionLowerMessage)) {?>
            <p class="alert alert-warning cf-upgrade-info"><i class="icon-warning"></i> <?php echo $this->prismVersionLowerMessage; ?></p>
        <?php } ?>
        <p class="alert alert-info cf-upgrade-info"><i class="icon-info"></i> <?php echo JText::_("COM_ITPTRANSIFEX_HOW_TO_UPGRADE"); ?></p>
	</div>
</div>
