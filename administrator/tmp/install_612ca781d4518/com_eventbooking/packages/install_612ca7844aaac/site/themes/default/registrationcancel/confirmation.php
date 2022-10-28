<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$btnPrimary      = $bootstrapHelper->getClassMapping('btn btn-primary');

if ($this->rowRegistrant->published == 4)
{
	$heading = Text::_('EB_WAITING_LIST_CANCELLATION_CONFIRMATION');
}
else
{
	$heading = Text::_('EB_REGISTRATION_CANCELLATION_CONFIRMATION');
}
?>
<h1 class="eb_title"><?php echo $this->escape($heading); ?></h1>
<?php echo $this->message; ?>
<form method="post" action="<?php echo Route::_('index.php?option=com_eventbooking&task=registrant.cancel&cancel_code='.$this->registrationCode.'&Itemid='.$this->Itemid, false) ?>" name="adminForm" id="adminForm" class="form form-horizontal">
	<input type="submit" value="<?php echo Text::_('EB_PROCESS');; ?>" id="btn-submit" name="btn-submit" class="<?php echo $btnPrimary; ?>">
	<input type="hidden" value="0" name="id" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>