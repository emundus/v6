<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

?>
<div id="eb-registration-complete-page" class="eb-container">
	<h1 class="eb-page-heading"><?php echo Text::_('EB_REGISTRATION_COMPLETE'); ?>
		<?php
		if ($this->showPrintButton === '1' &&  !$this->print)
		{
			$uri = Uri::getInstance();
			$uri->setVar('tmpl', 'component');
			$uri->setVar('print', '1');
		?>
			<div id="pop-print" class="btn hidden-print">
				<a href="<?php echo $uri->toString(); ?>" target="_blank" title="<?php echo Text::_('EB_PRINT_THIS_PAGE'); ?>" rel="nofollow">
					<span class="<?php echo $this->bootstrapHelper->getClassMapping('icon-print'); ?>"></span>
				</a>
			</div>
		<?php
		}
		?>
	</h1>
	<div id="eb-message" class="eb-message"><?php echo HTMLHelper::_('content.prepare', $this->message); ?></div>
</div>
<?php
	if ($this->print)
	{
	?>
		<script type="text/javascript">
			window.print();
		</script>
	<?php
	}
	echo $this->conversionTrackingCode;
?>