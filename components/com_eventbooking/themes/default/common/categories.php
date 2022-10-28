<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$clearfixClass   = $bootstrapHelper->getClassMapping('clearfix');

$rootUri = Uri::root(true);

if ($categoryId)
{
?>
	<h2 class="eb-heading"><?php echo Text::_('EB_SUB_CATEGORIES'); ?></h2>
<?php
}
?>
<div id="eb-categories">
	<?php
	foreach ($categories as $category)
	{
		if (!$config->show_empty_cat && !$category->total_events)
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
		<div class="eb-category">
			<div class="eb-box-heading">
				<h3 class="eb-category-title">
					<a href="<?php echo $categoryLink; ?>" class="eb-category-title-link">
						<?php
							echo $category->name;
						?>
					</a>
                    <?php
                    if ($config->show_number_events)
                    {
	                ?>
                        <span class="<?php echo $bootstrapHelper->getClassMapping('badge badge-info'); ?>"><?php echo $category->total_events ;?> <?php echo $category->total_events == 1 ? Text::_('EB_EVENT') :  Text::_('EB_EVENTS') ; ?></span>
	                <?php
                    }
                    ?>
				</h3>
			</div>
			<?php
				if($category->description || $category->image)
				{
				?>
					<div class="eb-description <?php echo $clearfixClass; ?>">
						<?php
							if ($category->image && file_exists(JPATH_ROOT . '/images/com_eventbooking/categories/thumb/' . basename($category->image)))
							{
							?>
								<a href="<?php echo $categoryLink ?>"><img src="<?php echo $rootUri . '/images/com_eventbooking/categories/thumb/' . basename($category->image); ?>" class="eb-thumb-left" /></a>
							<?php
							}

							echo $category->description;
						?>
					</div>
				<?php
				}
			?>
		</div>
	<?php
	}
	?>
</div>