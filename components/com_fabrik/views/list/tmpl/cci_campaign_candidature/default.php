<?php
/**
 * Fabrik List Template: Div
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access');

$doc = JFactory::getDocument();
$doc->addStyleSheet('media/com_emundus/lib/bootstrap-232/css/bootstrap.min.css');
$doc->addStyleSheet('templates/g5_helium/custom/css/moteur-de-recherche.css');

require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'formations.php');
$m_formations = new EmundusModelFormations();

// The number of columns to split the list rows into
$pageClass = $this->params->get('pageclass_sfx', '');

function jsonDecode($val)
{
    if (empty(json_decode($val))) {
        return $val;
    } else {
        return json_decode($val);
    }
}


if ($pageClass !== '') : ?>
<div class="<?php echo $pageClass; ?>">
    <?php endif;

    if ($this->tablePicker != '') :?>
        <div style="text-align:right"><?php echo FText::_('COM_FABRIK_LIST') ?>: <?php echo $this->tablePicker; ?></div>
    <?php
    endif;

    if ($this->params->get('show_page_heading')) : ?>
        <h1><?php echo $this->params->get('page_heading'); ?></h1>
    <?php endif;

    if ($this->showTitle == 1) :?>
        <div class="page-header">
            <h1><?php echo $this->table->label; ?></h1>
        </div>
    <?php
    endif;

    // Intro outside of form to allow for other lists/forms to be injected.
    echo $this->table->intro;
    ?>

    <div class="main">
        <div class="form">
            <form class="fabrikForm form-search" action="<?php echo $this->table->action; ?>" method="post"
                  id="<?php echo $this->formid; ?>" name="fabrikList">

                <?php
                if ($this->hasButtons) {
                    echo $this->loadTemplate('buttons');
                }
                ?>

                <div class="em-search-engine-filters">
                    <?php
                    if ($this->showFilters && $this->bootShowFilters) {
                        echo $this->layoutFilters();
                    }
                    ?>
                </div>


                <div class="fabrikDataContainer">

                    <?php foreach ($this->pluginBeforeList as $c) {
                        echo $c;
                    }

                    $data = array();
                    $i = 0;

                    if (!empty($this->rows)) {
                        foreach ($this->rows[0] as $k => $v) {
                            if (!in_array($v->data->jos_emundus_setup_campaigns___id_raw, array_column($data, 'jos_emundus_setup_campaigns___id_raw'))) {
                                foreach ($this->headings as $key => $val) {
                                    $raw = $key . '_raw';
                                    if (array_key_exists($raw, $v->data)) {
                                        $data[$i][$key] = $v->data->$key;
                                        $data[$i][$raw] = $v->data->$raw;
                                    }
                                }
                                if (array_key_exists('fabrik_view_url', $v->data)) {
                                    $data[$i]['fabrik_view_url'] = $v->data->fabrik_view_url;
                                }
                                if (array_key_exists('fabrik_edit_url', $v->data)) {
                                    $data[$i]['fabrik_edit_url'] = $v->data->fabrik_view_url;
                                }
                                $i = $i + 1;
                            }
                        }
                    }
                    ?>

                    <div class="em-search-engine-data">

                        <?php foreach ($data as $d) : ?>
                            <?php
                            $days = jsonDecode($d['jos_emundus_setup_teaching_unity___days_raw']);
                            if (is_array($days)) {
                                $days = $days[0];
                            }
                            $title = jsonDecode($d['jos_emundus_setup_teaching_unity___label_raw']);
                            if (is_array($title)) {
                                $title = $title[0];
                            }
                            ?>

                            <div class="em-result accordion-container accordion-container-<?php echo $this->table->renderid; ?>">
                                <div class="em-top-details article-title article-title-<?php echo $this->table->renderid; ?>">
                                    <div class="g-block size-70 em-formation-title">
                                        <div class="overflow">
                                            <h2><?php echo $title; ?></h2>
                                        </div>
                                        <div class="em-formation-details g-block size-100">
                                            <div class="left g-block size-60">
                                                <div class="formation-day">
                                                    <?php
                                                    setlocale(LC_ALL, 'fr_FR.utf8');

                                                    $date_start = $d['jos_emundus_setup_teaching_unity___date_start_raw'];
                                                    $date_end = $d['jos_emundus_setup_teaching_unity___date_end_raw'];

                                                    $start_day = date('d', strtotime($date_start));
                                                    $end_day = date('d', strtotime($date_end));
                                                    $start_month = date('m', strtotime($date_start));
                                                    $end_month = date('m', strtotime($date_end));
                                                    $start_year = date('y', strtotime($date_start));
                                                    $end_year = date('y', strtotime($date_end));

                                                    if ($start_day == $end_day && $start_month == $end_month && $start_year == $end_year) {
                                                        echo 'Date : le ' . strftime('%e', strtotime($date_start)) . " " . strftime('%B', strtotime($date_end)) . " " . date('Y', strtotime($date_end));
                                                    } elseif ($start_month == $end_month && $start_year == $end_year) {
                                                        echo 'Dates : du ' . strftime('%e', strtotime($date_start)) . " au " . strftime('%e', strtotime($date_end)) . " " . strftime('%B', strtotime($date_end)) . " " . date('Y', strtotime($date_end));
                                                    } elseif ($start_month != $end_month && $start_year == $end_year) {
                                                        echo 'Dates : du ' . strftime('%e', strtotime($date_start)) . " " . strftime('%B', strtotime($date_start)) . " au " . strftime('%e', strtotime($date_end)) . " " . strftime('%B', strtotime($date_end)) . " " . date('Y', strtotime($date_end));
                                                    } elseif (($start_month != $end_month && $start_year != $end_year) || ($start_month == $end_month && $start_year != $end_year)) {
                                                        echo 'Dates : du ' . strftime('%e', strtotime($date_start)) . " " . strftime('%B', strtotime($date_start)) . " " . date('Y', strtotime($date_start)) . " au " . strftime('%e', strtotime($date_end)) . " " . strftime('%B', strtotime($date_end)) . " " . date('Y', strtotime($date_end));
                                                    }
                                                    ?>
                                                </div>

                                                <div class="formation-number-details">
                                                    <?php echo JText::_("COM_EMUNDUS_SESSION_NUMBER") . ' : ' . $d['jos_emundus_setup_teaching_unity___session_code_raw']; ?>
                                                </div>

                                                <div class="formation-location">
                                                    <?php
                                                    $town = preg_replace('/[0-9]+/', '', str_replace(" cedex", "", ucfirst(strtolower($d['jos_emundus_setup_teaching_unity___location_city_raw']))));
                                                    $town = ucwords(strtolower($town), '\',. ');
                                                    $beforeComma = strpos($town, "D'");
                                                    if (!empty($beforeComma)) {
                                                        $replace = strpbrk($town, "D'");
                                                        $town = substr_replace($town, lcfirst($replace), $beforeComma);
                                                    }
                                                    echo JText::_("COM_EMUNDUS_SESSION_LOCATION") . ' : ' . $town;
                                                    ?>
                                                </div>
                                            </div>

                                            <div class="right g-block size-35">
                                                <div class="formation-length">
                                                    Durée
                                                    : <?php echo JText::_("DURATION") . ' : ' . ($d['jos_emundus_setup_teaching_unity___hours_raw'] == '1') ? $d['jos_emundus_setup_teaching_unity___hours_raw'] . ' heure' : $d['jos_emundus_setup_teaching_unity___hours_raw'] . ' heures'; ?>
                                                </div>

                                                <div class="fomation-code">
                                                    <?php echo JText::_("CODE") . ' : ' . str_replace('FOR', '', $d['jos_emundus_setup_programmes___code_raw']); ?>
                                                </div>
                                            </div>


                                        </div>

                                    </div>
                                    <div class="g-block size-30 em-status">
                                    <span class="label label-<?php echo $d['jos_emundus_setup_status___class_raw']; ?>">
                                        <?php echo $d['jos_emundus_setup_status___value']; ?>
                                    </span>
                                    </div>
                                </div>

                                <div class="em-bottom-details accordion-content">

                                    <div class="em-candidate-details">
                                        <div class="em-candidate-title">
                                            <?php echo JText::_("COM_EMUNDUS_SIGNED_UP_ASSOCIATES"); ?>
                                        </div>
                                        <?php
                                        $count_applicants = 0;
                                        foreach ($m_formations->getApplicantsInSessionForDRH($d['jos_emundus_setup_campaigns___id_raw']) as $applicant) :?>
                                            <div class="em-candidate" id="<?php echo $applicant->fnum; ?>">
                                                <div class="row-fluid">
                                                    <div class="em-candidate-name"><?php echo $applicant->civility . '. ' . $applicant->firstname . ' ' . $applicant->lastname; ?></div>
                                                    <?php if ($d['jos_emundus_setup_status___step_raw'] == 0) : ?>
                                                        <!-- TODO: Display n°Stagiaire FROM GESCOF? -->
                                                        <div class="em-delete-application"
                                                             onclick="deleteApplication('<?php echo $applicant->fnum; ?>')">
                                                            <i class="fas fa-times fa-2x"></i></div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="row-fluid">
                                                    <div class="em-candidate-details"><?php echo JText::_("COMPANY") . ' : ' . $applicant->company; ?></div>
                                                </div>
                                                <div class="row-fluid">
                                                    <div class="em-candidate-function"><?php echo JText::_("POSITION") . ' : ' . $applicant->position; ?></div>
                                                </div>
                                                <div class="row-fluid">
                                                    <div class="em-candidate-details"><?php echo JText::_("DATE_OF_BIRTH") . ' : ' . date('d/m/Y', strtotime($applicant->birthday)); ?></div>
                                                </div>
                                                <hr class="candidate-breaker">
                                            </div>

                                            <?php $count_applicants++;endforeach; ?>
                                    </div>
                                    <div class="em-button-add-candidate">
                                        <a href="<?php echo '/inscription?session=' . $d['jos_emundus_setup_teaching_unity___session_code_raw']; ?>"><?php echo JText::_("COM_EMUNDUS_ADD_ASSOCIATE"); ?></a>
                                    </div>
                                    <div class="em-button-see-formation">
                                        <a href="<?php echo '/formation?rowid=' . $d['jos_emundus_setup_programmes___id_raw']; ?>"><?php echo JText::_("COM_EMUNDUS_SEE_FORMATION"); ?></a>
                                    </div>
                                    <hr class="add-candidate-breaker">

                                    <!-- TODO: If the status is a certain step, don't show this (cancelled, + maybe other steps) -->
                                    <div class="em-payment-details">
                                        <div class="em-payment-title"><?php echo JText::_("COM_EMUNDUS_BILLS_TO_COME"); ?></div>
                                        <div class="row-fluid">
                                            <div class="em-price"><?php echo JText::_("COM_EMUNDUS_UNIT_PRICE") . ' : ' . (float)$d['jos_emundus_setup_teaching_unity___price_raw']; ?>
                                                €
                                            </div>
                                            <div class="em-total-price"><?php echo JText::_("COM_EMUNDUS_TOTAL_PRICE") . ' : ' . ((float)$count_applicants) * ((float)$d['jos_emundus_setup_teaching_unity___price_raw']); ?>
                                                €
                                            </div>
                                            <!-- TODO: Display total facturé (net de taxe) FROM GESCOF? -->
                                            <!-- TODO: Display financeur -->
                                            <!-- TODO: Display dates of billing and echeance FROM GESCOF? -->
                                        </div>
                                    </div>
                                </div>
                                <hr class="formation-breaker">
                            </div>


                        <?php endforeach; ?>

                        <tfoot>
                        <tr class="fabrik___heading">
                            <?php if (!empty($data)) : ?>
                                <td colspan="<?php echo count($this->headings); ?>">
                                    <?php echo $this->nav; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                        </tfoot>


                        <?php if ($this->hasCalculations) : ?>
                            <tfoot>
                            <tr class="fabrik_calculations">

                                <?php foreach ($this->headings as $key => $heading) :
                                    $h = $this->headingClass[$key];
                                    $style = empty($h['style']) ? '' : 'style="' . $h['style'] . '"'; ?>
                                    <td class="<?php echo $h['class'] ?>" <?php echo $style ?>>
                                        <?php
                                        $cal = $this->calculations[$key];
                                        echo array_key_exists($groupedBy, $cal->grouped) ? $cal->grouped[$groupedBy] : $cal->calc;
                                        ?>
                                    </td>
                                <?php endforeach; ?>

                            </tr>
                            </tfoot>
                        <?php endif; ?>
                    </div>

                    <?php print_r($this->hiddenFields); ?>
                </div>
            </form>
        </div>
    </div>
    <script>

        jQuery(document).ready(function () {
            if (jQuery(this).find('.accordion-container-<?php echo $this->table->renderid; ?>').size() > 0) {
                var first = document.querySelectorAll('.accordion-container-<?php echo $this->table->renderid; ?>')[0];
                jQuery(first.getElementsByClassName('accordion-content')[0]).slideToggle();
                first.classList.add('open');
            }
        });

        jQuery(function () {
            var Accordion = function (el, multiple) {
                this.el = el || {};
                this.multiple = multiple || false;

                var links = this.el.find('.article-title-<?php echo $this->table->renderid; ?>');
                links.on('click', {
                    el: this.el,
                    multiple: this.multiple
                }, this.dropdown)
            };

            Accordion.prototype.dropdown = function (e) {
                var $el = e.data.el;

                $this = jQuery(this);
                $next = $this.next();

                $next.slideToggle();
                $this.parent().toggleClass('open');

                if (!e.data.multiple) {
                    $el.find('.accordion-content').not($next).slideUp().parent().removeClass('open');
                }
            };
            var accordion = new Accordion(jQuery('.accordion-container-<?php echo $this->table->renderid; ?>'), false);
        });

        function deleteApplication(fnum) {
            Swal.fire({
                    title: "<?php echo JText::_('COM_EMUNDUS_REMOVE_ASSOCIATE'); ?>",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#28a745",
                    cancelButtonColor: "#dc3545",
                    confirmButtonText: "<?php echo JText::_('JYES');?>",
                    cancelButtonText: "<?php echo JText::_('JNO');?>"
                }
            ).then(
                function (isConfirm) {
                    if (isConfirm.value == true) {
                        jQuery.ajax({
                            type: 'POST',
                            dataType: 'json',
                            url: 'index.php?option=com_emundus&controller=files&task=removefile',
                            data: ({
                                fnum: fnum
                            }),
                            success: function (result) {
                                if (result.status) {
                                    document.getElementById(fnum).hide();
                                    Swal.fire({
                                        type: 'success',
                                        title: "<?php echo JText::_('COM_EMUNDUS_ASSOCIATE_REMOVED'); ?>"
                                    });
                                } else {
                                    Swal.fire({
                                        type: 'error',
                                        text: "<?php echo JText::_('COM_EMUNDUS_ASSOCIATE__NOT_REMOVED'); ?>"
                                    });
                                }
                            },
                            error: function (jqXHR) {
                                console.log(jqXHR.responseText);
                                Swal.fire({
                                    type: 'error',
                                    text: "<?php echo JText::_('COM_EMUNDUS_ASSOCIATE__NOT_REMOVED'); ?>"
                                });
                            }
                        });
                    }
                }
            );
        }
    </script>

