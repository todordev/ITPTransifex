<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;
$project     = $displayData["project"];
?>
<div class="row">
    <div class="col-md-3">
        <?php if (!$project->getImage()) { ?>
            <img src="<?php echo "media/com_itptransifex/images/no_image.png"; ?>"
                 alt="<?php echo $displayData["clean_title"]; ?>" width="200"
                 height="200" />
        <?php } else { ?>
            <img src="<?php echo $displayData["images_folder"]."/".$project->getImage(); ?>" alt="<?php echo $displayData["clean_title"]; ?>" width="<?php echo $displayData["image_width"]; ?>" height="<?php echo $displayData["image_height"]; ?>"/>
        <?php } ?>
    </div>
    <div class="col-md-9">
        <?php
            echo "<".$displayData["h_tag"].">".$displayData["clean_title"]."</".$displayData["h_tag"].">";
        ?>

        <p><?php echo $this->escape($project->getDescription()); ?></p>

        <a href="<?php echo $project->getLink(); ?>" class="btn btn-default" target="_blank">
            <span class="glyphicon glyphicon-link" aria-hidden="true"></span>
            <?php echo JText::sprintf("COM_ITPTRANSIFEX_TRANSLATE_S", $displayData["clean_title"]); ?>
        </a>
    </div>
</div>
