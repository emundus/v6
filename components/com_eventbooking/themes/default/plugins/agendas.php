<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;
?>
<table id="eb-event-agandas" class="table table-bordered table-striped">
    <tbody>
    <?php
    $i = 0 ;

    foreach ($agendas as $agenda)
    {
    ?>
        <tr>
            <td class="eb-agenda-time">
               <?php echo $agenda->time; ?>
            </td>
            <td class="eb-agenda-activity">
                <?php
                    if ($agenda->title)
                    {
                    ?>
                        <h4 class="eb-agenda-title"><?php echo $agenda->title; ?></h4>
                    <?php
                    }

                    if ($agenda->description)
                    {
                    ?>
                        <p class="eb-agenda-description"><?php echo $agenda->description; ?></p>
                    <?php
                    }
                ?>
            </td>
        </tr>
    <?php
    }
    ?>
    </tbody>
</table>
