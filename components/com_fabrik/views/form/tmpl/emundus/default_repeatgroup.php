<?php
/**
 * Bootstrap Form Template: Repeat group rendered as standard form
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.0
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$input = JFactory::getApplication()->input;
$group = $this->group;
$i = 1;
$w = new FabrikWorker;

foreach ($group->subgroups as $subgroup) :
	$introData = array_merge($input->getArray(), array('i' => $i));
	?>
	<div class="fabrikSubGroup">
		<?php if(!empty($group->maxRepeat) && $group->maxRepeat > 1) : ?>
            <p class="em-text-neutral-600"><?php echo JText::sprintf('COM_FABRIK_REPEAT_GROUP_MAX',$group->maxRepeat) ?></p>
		<?php endif; ?>
		<div data-role="group-repeat-intro">
			<?php echo $w->parseMessageForPlaceHolder($group->repeatIntro, $introData);?>
		</div>
		<div class="fabrikSubGroupElements em-repeat-card mb-4 mt-7">
            <?php if ($group->canDeleteRepeat) : ?>
                <div class="fabrikGroupRepeater pull-right">
                    <?php echo $this->removeRepeatGroupButton; ?>
                </div>
            <?php endif; ?>

			<?php

			// Load each group in a <ul>
			$this->elements = $subgroup;
			echo $this->loadTemplate('group');
			?>
		</div><!-- end fabrikSubGroupElements -->
        <?php
        // Add the add/remove repeat group buttons
        if ($group->editable && ($group->canAddRepeat || $group->canDeleteRepeat)) : ?>
            <div class="fabrikGroupRepeater">
                <?php if ($group->canAddRepeat) :
                    echo $this->addRepeatGroupButton;
                endif; ?>
            </div>
        <?php
        endif;
        ?>
	</div><!-- end fabrikSubGroup -->
	<?php
	$i ++;
endforeach;
