<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\AdminTools\Admin\View\ControlPanel\Html;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var  Html $this For type hinting in the IDE */

defined('_JEXEC') or die;

$uriBase = rtrim(Uri::base(), '/');

?>

<div class="akeeba-panel--default">
	<header class="akeeba-block-header">
		<h3><?php echo Text::_('COM_ADMINTOOLS_CONTROLPANEL_HEADER_QUICKSETUP'); ?></h3>
	</header>

	<p class="akeeba-block--warning small">
		<?php echo Text::_('COM_ADMINTOOLS_CONTROLPANEL_HEADER_QUICKSETUP_HELP'); ?>
	</p>

	<div class="akeeba-grid--small">
		<div>
			<a href="index.php?option=com_admintools&view=QuickStart" class="akeeba-action--orange">
				<span class="akion-flash"></span>
				<?php echo Text::_('COM_ADMINTOOLS_TITLE_QUICKSTART'); ?>
			</a>
		</div>
	</div>
</div>
