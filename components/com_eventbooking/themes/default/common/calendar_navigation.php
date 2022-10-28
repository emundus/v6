<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$month = $currentDateData['month'];
$year  = $currentDateData['year'];
$day   = $currentDateData['current_date'];

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
?>
<div class="eb-topmenu-calendar">
	<ul class="eb-menu-calendar <?php echo $bootstrapHelper->getClassMapping('nav'); ?> <?php echo $bootstrapHelper->getClassMapping('nav-pills'); ?>">
		<li>
			<a class="eb-calendar-view-link<?php if ($layout == 'default') echo ' active'; ?>"
			   href="<?php echo Route::_("index.php?option=com_eventbooking&view=calendar&layout=default&month=$month&year=$year&Itemid=$Itemid"); ?>"
			   rel="nofollow">
				<?php echo Text::_('EB_MONTHLY_VIEW')?>
			</a>
		</li>
		<?php
		if ($config->activate_weekly_calendar_view)
		{
			$date = $currentDateData['start_week_date'];
		?>
			<li>
				<a class="eb-calendar-view-link<?php if ($layout == 'weekly') echo ' active'; ?>" href="<?php echo Route::_("index.php?option=com_eventbooking&view=calendar&layout=weekly&date=$date&Itemid=$Itemid"); ?>" rel="nofollow">
					<?php echo Text::_('EB_WEEKLY_VIEW')?>
				</a>
			</li>
		<?php
		}

		if ($config->activate_daily_calendar_view)
		{
		?>
			<li>
				<a class="eb-calendar-view-link<?php if ($layout == 'daily') echo ' active'; ?>" href="<?php echo Route::_("index.php?option=com_eventbooking&view=calendar&layout=daily&day=$day&Itemid=$Itemid"); ?>" rel="nofollow">
					<?php echo Text::_('EB_DAILY_VIEW')?>
				</a>
			</li>
		<?php
		}
		?>
	</ul>
</div>