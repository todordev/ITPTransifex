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

$itemSpan = !empty($this->numberInRow) ? round(12 / $this->numberInRow) : 4;
?>
<?php if (!empty($this->items)) { ?>
<div class="row">
    <?php foreach ($this->items as $item) { if (!empty($item->image)) {
        $image = $this->imageFolder.'/'.basename($item->image);
    }?>
    <div class="col-sm-6 col-md-<?php echo $itemSpan; ?>">
        <div class="thumbnail itptfx-project-grid">
            <a href="<?php echo JRoute::_(ItpTransifexHelperRoute::getProjectRoute($item->slug)); ?>">
                <?php if (!empty($item->image)) { ?>
                    <img src="<?php echo $image; ?>" alt="<?php echo $this->escape($item->name); ?>" width="<?php echo $this->params->get('image_width', '200'); ?>" height="<?php echo $this->params->get('image_height', '200'); ?>" class="center-block"/>
                <?php } else { ?>
                    <img src="<?php echo 'media/com_itptransifex/images/no_image.png'; ?>" alt="<?php echo $this->escape($item->name); ?>" width="200" height="200" class="center-block"/>
                <?php } ?>
            </a>

            <div class="caption height-200px absolute-bottom">
                <h3>
                    <a href="<?php echo JRoute::_(ItpTransifexHelperRoute::getProjectRoute($item->slug)); ?>">
                        <?php echo $this->escape($item->name); ?>
                    </a>
                </h3>
                <?php if ($this->params->get('display_description', 1)) { ?>
                    <p><?php echo JHtmlString::truncate($item->description, $this->params->get('description_length', 255), true, false); ?></p>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
<?php } ?>