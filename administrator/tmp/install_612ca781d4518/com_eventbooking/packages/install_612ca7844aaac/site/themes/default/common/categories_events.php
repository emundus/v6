<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$clearfixClass   = $bootstrapHelper->getClassMapping('clearfix');
?>
<div id="eb-categories">
	<?php
	foreach ($categories as $category)
	{
		if (!$config->show_empty_cat && !count($category->events))
		{
			continue ;
		}

		if ($itemId = EventbookingHelperRoute::getCategoriesMenuId($category->id))
		{
			$categoryLink = Route::_('index.php?option=com_eventbooking&view=categories&id=' . $category->id . '&Itemid=' . $itemId);
		}
		else
		{
			$categoryLink = Route::_(EventbookingHelperRoute::getCategoryRoute($category->id, $Itemid));
		}
		?>
		<div class="row-fluid <?php echo $clearfixClass; ?>">
			<h3 class="eb-category-title">
				<a href="<?php echo $categoryLink; ?>" class="eb-category-title-link">
					<?php
						echo $category->name;
					?>
				</a>
			</h3>
			<?php
				if($category->description)
				{
				?>
					<div class="<?php echo $clearfixClass; ?>"><?php echo $category->description;?></div>
				<?php
				}

				if (count($category->events))
				{
					$user = Factory::getUser();
					$bootstrapHelper = EventbookingHelperBootstrap::getInstance();

					echo EventbookingHelperHtml::loadCommonLayout('common/events_table.php', array('items' => $category->events, 'config' => $config, 'Itemid' => $Itemid, 'nullDate' => Factory::getDbo()->getNullDate(), 'ssl' => (int) $config->use_https, 'viewLevels' => $user->getAuthorisedViewLevels(), 'categoryId' => $category->id, 'bootstrapHelper' => $bootstrapHelper));
				}
			?>
		</div>
	<?php
	}
	?>
</div>