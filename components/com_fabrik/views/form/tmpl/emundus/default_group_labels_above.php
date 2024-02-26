<?php
/**
 * Bootstrap Form Template: Labels Above
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.0
 */

// No direct access
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

$element      = $this->element;
$hidden_class = '';
?>


<div class="tw-relative">
	<?php if (!$this->collaborator && !empty($this->collaborators)) : ?>
		<?php if (in_array($element->id, $this->locked_elements)) :
			$hidden_class = '!tw-hidden';
			?>
		<?php endif; ?>

        <span class="material-icons-outlined !tw-text-sm tw-absolute tw-cursor-pointer"
              title="<?php echo Text::_('COM_EMUNDUS_FABRIK_UNLOCK_FOR_OTHERS') ?>"
              onclick="lockElement('<?php echo $element->id; ?>', 0)" id="lock_<?php echo $element->id ?>"
              style="left: -15px;top: 1px;<?php if (!in_array($element->id, $this->locked_elements)) : ?>display:none<?php endif; ?>">lock</span>

        <span class="material-icons-outlined !tw-text-sm tw-absolute tw-cursor-pointer !tw-text-neutral-600 <?php echo $hidden_class; ?>"
              title="<?php echo Text::_('COM_EMUNDUS_FABRIK_LOCK_FOR_OTHERS') ?>"
              onclick="lockElement('<?php echo $element->id; ?>')" id="open_lock_<?php echo $element->id ?>"
              style="left: -15px;top: 1px;display: none">lock_open</span>
	<?php endif; ?>

    <div>
		<?php echo $element->label; ?>

		<?php if ($this->tipLocation == 'above') : ?>
            <span class="fabrikElementTip fabrikElementTipAbove"><?php echo $element->tipAbove ?></span>
		<?php endif ?>

        <div class="fabrikElement">
			<?php echo $element->element; ?>
        </div>

        <div class="<?php echo $this->class ?>">
			<?php echo $element->error ?>
        </div>

		<?php if ($this->tipLocation == 'side') : ?>
            <span class="fabrikElementTip"><?php echo $element->tipSide ?></span>
		<?php endif ?>

		<?php if ($this->tipLocation == 'below') : ?>
            <span class="fabrikElementTip fabrikElementTipBelow"><?php echo $element->tipBelow ?></span>
		<?php endif ?>

    </div>
</div>
