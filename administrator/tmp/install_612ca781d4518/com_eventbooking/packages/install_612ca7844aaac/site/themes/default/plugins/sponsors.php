<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

use Joomla\CMS\Uri\Uri;

$rootUri          = Uri::root(true);
$config           = EventbookingHelper::getConfig();
$bootstrapHelper  = EventbookingHelperBootstrap::getInstance();
$numberColumns    = $config->get('number_sponsors_per_row', 4);
$numberSponsors   = count($sponsors);
$count            = 0;
$span             = 'span' . intval(12 / $numberColumns);
$span             = $bootstrapHelper->getClassMapping($span);
$rowFluidClass    = $bootstrapHelper->getClassMapping('row-fluid');
$imageCircleClass = $bootstrapHelper->getClassMapping('img-circle');
?>
<div id="eb-sponsors-list" class="<?php echo $rowFluidClass; ?> clearfix">
	<?php
	for ($i = 0 , $n = count($sponsors) ;  $i < $n ; $i++)
	{
        $count++;
        $sponsor = $sponsors[$i] ;
	?>
	<div class="<?php echo $span; ?> eb-sponor-container">
		<?php
		if ($sponsor->name)
		{
			if ($sponsor->website)
			{
			?>
				<h4 class="eb-speaker-name">
					<a href="<?php echo $sponsor->website; ?>" class="eb-speaker-url">
						<?php echo $sponsor->name; ?>
					</a>
				</h4>
			<?php
			}
			else
			{
			?>
				<h4 class="eb-speaker-name"><?php echo $sponsor->name; ?></h4>
			<?php
			}
		}

		if ($sponsor->logo)
		{
		?>
        <div class="eb-sponsor-logo">
            <?php
            if ($sponsor->website)
            {
            ?>
                <a href="<?php echo $sponsor->website; ?>" class="eb-sponsor-url">
                    <img src="<?php echo $rootUri.'/'.$sponsor->logo; ?>" class="<?php echo $imageCircleClass; ?>" />
                </a>
            <?php
            }
            else
            {
            ?>
                <img src="<?php echo $rootUri.'/'.$sponsor->logo; ?>" class="<?php echo $imageCircleClass; ?>" />
            <?php
            }
            ?>
        </div>
		<?php
		}
		?>
	</div>
	<?php
        if ($count % $numberColumns == 0 && $count < $numberSponsors)
        {
        ?>
            </div>
            <div class="clearfix <?php echo $rowFluidClass; ?>">
        <?php
        }
	}
	?>
</div>