<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this Akeeba\AdminTools\Admin\View\SEOAndLinkTools\Html */

defined('_JEXEC') || die;

$lang = \Joomla\CMS\Factory::getApplication()->getLanguage();
?>
<form name="adminForm" id="adminForm" action="index.php" method="post" class="akeeba-form--horizontal">
	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
			<h3>@lang('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPTGROUP_MIGRATION')</h3>
		</header>

		<div class="akeeba-form-group">
			<label for="linkmigration">@lang('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPT_LINKMIGRATION')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'linkmigration', $this->salconfig['linkmigration'])
		</div>
		<div class="akeeba-form-group">
			<label for="migratelist"
				   title="@lang('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPT_LINKMIGRATIONLIST_TIP')">
				@lang('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPT_LINKMIGRATIONLIST')
			</label>

			<textarea rows="5" cols="55" name="migratelist"
					  id="migratelist">{{{ $this->salconfig['migratelist'] }}}</textarea>
		</div>
	</div>

	<input type="hidden" name="option" value="com_admintools" />
	<input type="hidden" name="view" value="SEOAndLinkTools" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="@token(true)" value="1" />
</form>
