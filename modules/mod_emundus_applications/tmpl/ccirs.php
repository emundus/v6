<?php
/**
 * @package   Joomla.Site
 * @subpackage  eMundus
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
//var_dump($user->fnums); echo "<hr>"; var_dump($applications);
echo $description;
?>

<?php if (!empty($applications)) : ?>
    <div class="<?php echo $moduleclass_sfx ?>">

        <div class="row" id="em-applications">
            <div class="col-md-6">
            </div>

            <div class="col-md-4">
                <strong>Session réservée</strong>
            </div>

            <div class="col-md-2">
                <strong><?php echo JText::_('STATUS'); ?></strong>
            </div>
        </div>

        <?php foreach($applications as $application) : ?>
            <div class="row application" id="row<?php echo $application->fnum; ?>">
                <div class="col-md-6 main-page-application-title">
                        <a href="<?php echo JRoute::_(JURI::base().'index.php?option=com_fabrik&view=details&formid=296&rowid='. $application->pid.'&Itemid=2844'); ?>" ><?php echo $application->label; ?></a>
                </div>

                <div class="col-md-4 main-page-file-progress">
                    <div class="main-page-file-progress-label">
                            <?php
                                setlocale(LC_ALL, 'fr_FR.utf8');
                                $start_day = date('d',strtotime($application->date_start));
                                $end_day = date('d',strtotime($application->date_end));
                                $start_month = date('m',strtotime($application->date_start));
                                $end_month = date('m',strtotime($application->date_end));
                                $start_year = date('y',strtotime($application->date_start));
                                $end_year = date('y',strtotime($application->date_end));


                                if ($start_day == $end_day && $start_month == $end_month && $start_year == $end_year)
                                    echo strftime('%e',strtotime($application->date_start)) . " " . strftime('%B',strtotime($application->date_end)) . " " . date('Y',strtotime($application->date_end));
                                elseif ($start_month == $end_month && $start_year == $end_year)
                                    echo strftime('%e',strtotime($application->date_start)) . " au " . strftime('%e',strtotime($application->date_end)) . " " . strftime('%B',strtotime($application->date_end)) . " " . date('Y',strtotime($application->date_end));
                                elseif ($start_month != $end_month && $start_year == $end_year)
                                    echo strftime('%e',strtotime($application->date_start)) . " " . strftime('%B',strtotime($application->date_start)) . " au " . strftime('%e',strtotime($application->date_end)) . " " . strftime('%B',strtotime($application->date_end)) . " " . date('Y',strtotime($application->date_end));
                                elseif (($start_month != $end_month && $start_year != $end_year) || ($start_month == $end_month && $start_year != $end_year))
                                    echo strftime('%e',strtotime($application->date_start)) . " " . strftime('%B',strtotime($application->date_start)) . " " . date('Y',strtotime($application->date_start)) . " au " . strftime('%e',strtotime($application->date_end)) . " " . strftime('%B',strtotime($application->date_end)) . " " . date('Y',strtotime($application->date_end));
                            ?>
                    </div>
                </div>

                <div class="col-md-2 main-page-file-progress">
                    <div class="main-page-file-progress-label">
                        <span class="label label-<?php echo $application->class; ?>">
                            <?php echo $application->value; ?>
                        </span>
                    </div>
                </div>


            </div>
        <?php endforeach;  ?>
        <ul id="pagin"></ul>
    </div>
<?php else :
    echo JText::_('NO_FILE');
    ?>
<?php endif; ?>

<script type="text/javascript">


    var applications = <?php echo json_encode($applications); ?>;

    //Pagination
    pageSize = 3;

    var pageCount =  Object.keys(applications).length / pageSize;

    if (pageCount > 1) {
        for (var i = 0 ; i<pageCount;i++) {
            jQuery("#pagin").append('<li><p>'+(i+1)+'</p></li> ');
        }
    }

    jQuery("#pagin li").first().find("p").addClass("current");
    showPage = function(page) {
        jQuery(".application").hide();
        jQuery(".application").each(function(n) {
            if (n >= pageSize * (page - 1) && n < pageSize * page)
                jQuery(this).show();
        });
    };

    showPage(1);

    jQuery("#pagin li p").click(function() {
        jQuery("#pagin li p").removeClass("current");
        jQuery(this).addClass("current");
        showPage(parseInt(jQuery(this).text()))
    });



    function deletefile(fnum){
        if (confirm("<?php echo JText::_('CONFIRM_DELETE_FILE'); ?>")) {
            document.location.href="<?php echo JRoute::_(JURI::base().'index.php?option=com_emundus&task=deletefile&fnum='); ?>"+fnum;
        }
    }

</script>
