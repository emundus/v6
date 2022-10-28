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

$rootUri         = Uri::root(true);
$config          = EventbookingHelper::getConfig();
$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$numberColumns   = $config->get('number_speakers_per_row', 4);
$numberSpeakers  = count($speakers);
$count           = 0;
$span            = 'span' . intval(12 / $numberColumns);
$span            = $bootstrapHelper->getClassMapping($span);
$rowFluidClass   = $bootstrapHelper->getClassMapping('row-fluid');
$imageCircleClass = $bootstrapHelper->getClassMapping('img-circle');
?>
<div id="eb-speakers-list" class="<?php echo $rowFluidClass; ?> clearfix">
	<?php

		for ($i = 0 , $n = count($speakers) ;  $i < $n ; $i++)
		{
			$count++;
			$speaker = $speakers[$i] ;
		?>
			<div class="<?php echo $span; ?> eb-speaker-container">
                <?php
                    if ($speaker->avatar)
                    {
                    ?>
                        <div class="eb-speaker-avatar">
                            <?php
                                if ($speaker->url)
                                {
                                ?>
                                    <a href="<?php echo $speaker->url; ?>" class="eb-speaker-url">
                                        <img src="<?php echo $rootUri.'/'.$speaker->avatar; ?>" class="<?php echo $imageCircleClass; ?>" />
                                    </a>
                                <?php
                                }
                                else
                                {
                                ?>
                                    <img src="<?php echo $rootUri.'/'.$speaker->avatar; ?>" class="<?php echo $imageCircleClass; ?>" />
                                <?php
                                }
                            ?>
                        </div>
                    <?php
                    }

                    if ($speaker->url)
                    {
                    ?>
                        <h4 class="eb-speaker-name">
                            <a href="<?php echo $speaker->url; ?>" class="eb-speaker-url">
                                <?php echo $speaker->name; ?>
                            </a>
                        </h4>
                    <?php
                    }
                    else
                    {
                    ?>
                        <h4 class="eb-speaker-name"><?php echo $speaker->name; ?></h4>
                    <?php
                    }

                    if ($speaker->title)
                    {
                    ?>
                        <h5 class="eb-speaker-title"><?php echo $speaker->title; ?></h5>
                    <?php
                    }
                ?>
                <p class="eb-speaker-description">
                    <?php echo $speaker->description; ?>
                </p>
			</div>
		<?php
			if ($count % $numberColumns == 0 && $count < $numberSpeakers)
			{
			?>
				</div>
				<div class="clearfix <?php echo $rowFluidClass; ?>">
			<?php
			}
		}
	?>
</div>