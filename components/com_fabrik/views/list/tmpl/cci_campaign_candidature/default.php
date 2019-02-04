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
$doc->addScript('media/com_emundus/lib/chosen/chosen.jquery.js');
$doc->addStyleSheet('media/com_emundus/lib/chosen/chosen.css');
$doc->addStyleSheet('templates/g5_helium/custom/css/moteur-de-recherche.css');

require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'formations.php');
$m_formations = new EmundusModelFormations();

// The number of columns to split the list rows into
$pageClass = $this->params->get('pageclass_sfx', '');

function jsonDecode($val) {
    if (empty(json_decode($val))) {
	    return $val;
    } else {
	    return json_decode($val);
    }
}


if ($pageClass !== '') :?>
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
        <h1><?php echo $this->table->label;?></h1>
    </div>
<?php
endif;

// Intro outside of form to allow for other lists/forms to be injected.
echo $this->table->intro;
?>

<div class="main">
    <div class="form">
        <form class="fabrikForm form-search" action="<?php echo $this->table->action; ?>" method="post" id="<?php echo $this->formid; ?>" name="fabrikList">

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
					foreach ($this->rows as $k => $v) {
						foreach ($this->headings as $key => $val) {
                            $raw = $key.'_raw';
							if (array_key_exists($raw, $v[0]->data)) {
							    $data[$i][$key] = $v[0]->data->$key;
                                $data[$i][$raw] = $v[0]->data->$raw;
							}
						}
                        if (array_key_exists('fabrik_view_url', $v[0]->data)) {
                            $data[$i]['fabrik_view_url'] = $v[0]->data->fabrik_view_url;
                        }
						if (array_key_exists('fabrik_edit_url', $v[0]->data)) {
							$data[$i]['fabrik_edit_url'] = $v[0]->data->fabrik_view_url;
						}
						$i = $i + 1;
					}
				}
				?>

                <div class="em-search-engine-data">

                    <?php foreach ($data as $d) :?>
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
                                <div class="g-block size-50 em-formation-title">
                                    <h2><?php echo '<a href="'.$d['fabrik_view_url'].'?rowid='.$d['jos_emundus_setup_programmes___id_raw'].'" >'.$title."</a>"; ?></h2>
                                </div>
                                <div class="g-block size-50 em-status">
                                    <span class="label label-<?php echo $d['jos_emundus_setup_status___class_raw']; ?>">
                                        <?php echo $d['jos_emundus_setup_status___value']; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="em-bottom-details accordion-content">
                                <div class="row-fluid">
                                    <div class="em-day-details g-block size-50">
                                        <?php
                                            setlocale(LC_ALL, 'fr_FR.utf8');

                                            $date_start = $d['jos_emundus_setup_teaching_unity___date_start_raw'];
                                            $date_end = $d['jos_emundus_setup_teaching_unity___date_end_raw'];

                                            $start_day = date('d',strtotime($date_start));
                                            $end_day = date('d',strtotime($date_end));
                                            $start_month = date('m',strtotime($date_start));
                                            $end_month = date('m',strtotime($date_end));
                                            $start_year = date('y',strtotime($date_start));
                                            $end_year = date('y',strtotime($date_end));

                                            if ($start_day == $end_day && $start_month == $end_month && $start_year == $end_year) {
                                                echo 'Date : le '.strftime('%e', strtotime($date_start)) . " " . strftime('%B', strtotime($date_end)) . " " . date('Y', strtotime($date_end));
                                            } elseif ($start_month == $end_month && $start_year == $end_year) {
                                                echo 'Dates : du '.strftime('%e', strtotime($date_start)) . " au " . strftime('%e', strtotime($date_end)) . " " . strftime('%B', strtotime($date_end)) . " " . date('Y', strtotime($date_end));
                                            } elseif ($start_month != $end_month && $start_year == $end_year) {
                                                echo 'Dates : du '.strftime('%e', strtotime($date_start)) . " " . strftime('%B', strtotime($date_start)) . " au " . strftime('%e', strtotime($date_end)) . " " . strftime('%B', strtotime($date_end)) . " " . date('Y', strtotime($date_end));
                                            } elseif (($start_month != $end_month && $start_year != $end_year) || ($start_month == $end_month && $start_year != $end_year)) {
                                                echo 'Dates : du '.strftime('%e',strtotime($date_start)) . " " . strftime('%B',strtotime($date_start)) . " " . date('Y', strtotime($date_start)) . " au " . strftime('%e', strtotime($date_end)) . " " . strftime('%B',strtotime($date_end)) . " " . date('Y', strtotime($date_end));
                                            }
                                        ?>
                                    </div>
                                    <div class="em-length-details g-block size-50">
                                        Durée : <?php echo $d['jos_emundus_setup_teaching_unity___hours_raw']; ?>
                                    </div>
                                </div>
                                <div class="row-fluid">
                                    <div class="em-session-number-details g-block size-50">
                                        n° de session : <?php echo $d['jos_emundus_setup_teaching_unity___session_code_raw']; ?>
                                    </div>
                                    <div class="em-product-code-details g-block size-50">
                                        Code produit : <?php echo str_replace('FOR', '', $d['jos_emundus_setup_programmes___code_raw']); ?>
                                    </div>
                                </div>
                                <div class="row-fluid">
                                    <div class="em-location-details g-block size-50">
                                        <?php
                                            $town = preg_replace('/[0-9]+/', '',  str_replace(" cedex", "", ucfirst(strtolower($d['jos_emundus_setup_teaching_unity___location_city_raw']))));
                                            $town = ucwords(strtolower($town), '\',. ');
                                            $beforeComma = strpos($town, "D'");
                                            if (!empty($beforeComma)) {
                                                $replace = strpbrk($town, "D'");
                                                $town = substr_replace($town, lcfirst($replace), $beforeComma);
                                            }
                                            echo "Lieu de session : ".$town;
                                        ?>
                                    </div>
                                </div>


                                <div class="em-candidate-details">
                                    <div class="em-candidate-title">Collaborateur(s) inscrit(s)</div>
                                    <?php foreach ($m_formations->getApplicantsInSessionForDRH($d['jos_emundus_setup_campaigns___id_raw']) as $applicant) :?>
                                        <div class="row-fluid">
                                            <div class="em-candidate-name"><?php echo ($applicant->civility=='Male'?'M':'Mme').'. '.$applicant->firstname.' '.$applicant->lastname; ?></div>
                                            <?php if ($d['jos_emundus_setup_status___step_raw'] == 0) :?>
                                                <div class="em-delete-application" style="cursor: pointer; float: right;" onclick="deleteApplication(<?php echo $applicant->fnum; ?>)">&times;</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="row-fluid">
                                            <div class="em-candidate-details">Entreprise : <?php echo $applicant->company; ?></div>
                                        </div>
                                        <div class="row-fluid">
                                            <div class="em-candidate-details">Date de naissance : <?php echo date('d/m/Y', strtotime($applicant->birthday)); ?></div>
                                        </div>
                                        <hr>
                                    <?php endforeach; ?>
                                </div>

                                <hr>
                                <div class="em-button-add-candidate">
                                    <a href="<?php echo $d['fabrik_edit_url'].'?session='.$d['jos_emundus_setup_teaching_unity___session_code_raw']; ?>">Ajouter un collaborateur</a>
                                </div>
                                <hr>

                                <!-- TODO: If the status is a certain step, don't show this (cancelled, + maybe other steps) -->
                                <div class="em-payment-details">
                                    <div class="em-payment-title">Facturation à venir</div>
                                    <div class="row-fluid">
                                        <div class="em-price">Prix unitaire : <?php echo $d['jos_emundus_setup_teaching_unity___price_raw']; ?> €</div>
                                        <!-- TODO: Display total facturé (net de taxe) FROM GESCOF? -->
                                        <!-- TODO: Display financeur -->
                                        <!-- TODO: Display dates of billing and echeance FROM GESCOF? -->
                                    </div>
                                </div>
                            </div>
                        </div>

                    <hr class="em-separator">
                    <?php endforeach; ?>

                    <tfoot>
                        <tr class="fabrik___heading">
                            <?php if (!empty($data)) :?>
                                <td colspan="<?php echo count($this->headings);?>">
                                    <?php echo $this->nav;?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    </tfoot>


                    <?php if ($this->hasCalculations) :?>
                        <tfoot>
                        <tr class="fabrik_calculations">

                            <?php foreach ($this->headings as $key => $heading) :
                                $h = $this->headingClass[$key];
                                $style = empty($h['style']) ? '' : 'style="' . $h['style'] . '"'; ?>
                                <td class="<?php echo $h['class']?>" <?php echo $style?>>
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

        <?php print_r($this->hiddenFields);?>
        </div>
    </form>
</div>

    <script>
        // accordion
        jQuery(function() {
            var Accordion = function(el, multiple) {
                this.el = el || {};
                this.multiple = multiple || false;

                var links = this.el.find('.article-title-<?php echo $this->table->renderid; ?>');
                links.on('click', {
                    el: this.el,
                    multiple: this.multiple
                }, this.dropdown)
            };

            Accordion.prototype.dropdown = function(e) {
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

            if (confirm("Êtes vous sûr(e) de vouloir effacer ?") == true) {
                jQuery.ajax({
                   type: 'POST',
                    dataType: 'json',
                    url: 'index.php?option=com_emundus&controller=files&task=removefile',
                    data: ({
                         fnum: fnum
                    }),
                    success: function (result) {
                        if (result.status) {
                            window.location.reload();
                        } else {
                            // TODO: Display error.
                        }
                    },
                    error: function(jqXHR) {
                        console.log(jqXHR.responseText);
                        // TODO: Display error.
                    }
                });
            }
        }
    </script>
</div>
