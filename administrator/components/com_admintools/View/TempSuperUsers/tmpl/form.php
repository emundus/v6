<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\AdminTools\Admin\View\TempSuperUsers\Html;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var Html $this */

defined('_JEXEC') or die;

?>
<section class="akeeba-panel">
	<form action="index.php" method="post" name="adminForm" id="adminForm" class="akeeba-form--horizontal">
		<?php if (empty($this->item->user)): ?>
			<div class="akeeba-panel--danger">
				<header class="akeeba-block-header">
					<h3>
						<?= Text::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_INVALIDUSER') ?>
					</h3>
				</header>
				<p>
					<?= Text::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_INVALIDUSER_TIP') ?>
				</p>
			</div>
		<?php else: ?>

			<div class="akeeba-container--50-50">
				<div>
					<div class="akeeba-form-group">
						<label for="dummy">
							<?php echo Text::_('COM_ADMINTOOLS_LBL_TEMPSUPERUSER_EDITINGUSER'); ?>
						</label>

						<p>
							<strong><?php echo $this->item->user->username ?></strong><br />
							<?php echo $this->item->user->name ?>
							<em>
								(<?php echo $this->item->user->email ?>)
							</em>
						</p>
					</div>
					<div class="akeeba-form-group">
						<label for="expiration">
							<?php echo Text::_('COM_ADMINTOOLS_TEMPSUPERUSERS_FIELD_EXPIRATION'); ?>
						</label>

						<?php echo HTMLHelper::_('calendar', $this->item->expiration, 'expiration', 'expiration', '%Y-%m-%d %H:%M', [
							'class'    => 'input-small',
							'showTime' => true,
						]); ?>
					</div>

				</div>
			</div>
		<?php endif; ?>

		<div class="akeeba-hidden-fields-container">
			<input type="hidden" name="option" value="com_admintools" />
			<input type="hidden" name="view" value="TempSuperUsers" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="user_id" id="user_id" value="<?php echo (int) $this->item->user_id; ?>" />
			<input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1" />
		</div>
	</form>
</section>
