<?php
/**
 * Fabrik List Template: Div
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$doc = JFactory::getDocument();
$doc->addStyleSheet('media/com_emundus/lib/bootstrap-232/css/bootstrap.min.css');
$doc->addScript('media/com_emundus/lib/chosen/chosen.jquery.js');
$doc->addStyleSheet('media/com_emundus/lib/chosen/chosen.css');


function jsonDecode($val) {
	return (!empty(json_decode($val)))?json_decode($val):$val;
}


// The number of columns to split the list rows into
$pageClass = $this->params->get('pageclass_sfx', '');

if ($pageClass !== '') :
	echo '<div class="' . $pageClass . '">';
endif;

if ($this->tablePicker != '') : ?>
    <div style="text-align:right"><?php echo FText::_('COM_FABRIK_LIST') ?>: <?php echo $this->tablePicker; ?></div>
<?php endif;

if ($this->params->get('show_page_heading')) :?>
    <h1><?php echo $this->params->get('page_heading');?> </h1>
<?php endif; ?>

<?php if ($this->showTitle == 1) : ?>
    <div class="page-header">
        <h1><?php echo $this->table->label;?></h1>
    </div>
<?php endif;

// Intro outside of form to allow for other lists/forms to be injected.
echo $this->table->intro;

?>

<div class="main">
    <div class="form">
        <form class="fabrikForm form-search" action="<?php echo $this->table->action;?>" method="post" id="<?php echo $this->formid;?>" name="fabrikList">

			<?php
			if ($this->hasButtons)
				echo $this->loadTemplate('buttons');
			?>

            <div class="fabrikDataContainer">

				<?php foreach ($this->pluginBeforeList as $c) {
					echo $c;
				}


				$data = array();
				$i = 0;
				if (!empty($this->rows[0])) {
					foreach ($this->rows[0] as $k => $v) {
						foreach ($this->headings as $key => $val) {
							$raw = $key.'_raw';
							if (array_key_exists($key, $v->data)) {
								if (strcasecmp($v->data->$key, "1") == 0)
									$data[$i][$val] = $v->data->$key;
								else {
									$data[$i][$key] = $v->data->$key;
									$data[$i][$raw] = $v->data->$raw;
								}
							}
						}
						if (array_key_exists('fabrik_view_url', $v->data)) {
							$data[$i]['fabrik_view_url'] = $v->data->fabrik_view_url;
						}
						$i = $i + 1;
					}
				} ?>

                <div class="em-search-engine-filters">
					<?php if ($this->showFilters && $this->bootShowFilters)
						echo $this->layoutFilters();
					?>
                </div>

                <div class="em-search-engine-data">

                    <table>
						<?php if (!empty($data)) :?>
                            <thead>
                            <tr>
                                <td><h3>RÉSULTAT DE LA RECHERCHE</h3></td>
                            </tr>
                            </thead>
                            <tfoot>
                                <tr class="fabrik___heading">
                                    <td colspan="<?php echo count($this->headings);?>">
                                        <?php echo $this->nav;?>
                                    </td>
                                </tr>
                            </tfoot>
						<?php endif; ?>

                        <tbody>
						<?php if (empty($data)) :?>
                            <div>
                                <p>Vous n'avez pas trouvé ce que vous cherchiez ? Déposez l'annonce qui vous correspond.</p>
                                <p><a href="/?option=com_fabrik&view=form&formid=102">Proposez une offre</a></p>
                            </div>
						<?php endif;

						$gCounter = 0;
						foreach ($data as $d) {

							$cherches = [];
							if ($d['jos_emundus_recherche___futur_doctorant_yesno'] == 'oui')
								$cherches[] = $this->headings['jos_emundus_recherche___futur_doctorant_yesno'];
							if ($d['jos_emundus_recherche___acteur_public_yesno'] == 'oui')
								$cherches[] = $this->headings['jos_emundus_recherche___acteur_public_yesno'];
							if ($d['jos_emundus_recherche___equipe_de_recherche_direction_yesno'] == 'oui')
								$cherches[] = $this->headings['jos_emundus_recherche___equipe_de_recherche_direction_yesno'];
							if ($d['jos_emundus_recherche___equipe_de_recherche_codirection_yesno'] == 'oui')
								$cherches[] = $this->headings['jos_emundus_recherche___equipe_de_recherche_codirection_yesno'];

							$themes = jsonDecode($d['data_thematics___thematic_raw']);
							$departments = jsonDecode($d['data_departements___departement_nom_raw']);

							?>
                            <tr>
                                <td>
                                    <div class="em-search-engine-div-data">
                                        <div class="em-search-engine-result-title"><?php echo $d['jos_emundus_projet___titre']; ?></div>
                                        <div class="em-search-engine-deposant">
                                            <i class="fa fa-user"></i> <strong>Déposant : </strong> <?php echo strtolower($d['jos_emundus_setup_profiles___label']); ?>
                                        </div>
                                        <div class="em-search-engine-addressed">
                                            <i class="fa fa-users"></i> <strong>Projet adressé à : &nbsp;</strong><?php echo strtolower(implode( '&#32;-&#32;', $cherches)); ?>
                                        </div>
                                        <div class="em-search-engine-thematics">
                                            <strong>Thématique(s)</strong> : <div class="em-highlight"><?php echo $themes?is_array($themes)?implode('</div> - <div class="em-highlight">', $themes):$themes:'Aucune thématique'; ?></div>
                                        </div>
                                        <div class="em-search-engine-departments">
                                            <strong>Département(s)</strong> : <div class="em-highlight"><?php echo $departments?is_array($departments)?implode('</div> - <div class="em-highlight">', $departments):$departments:'Aucun département'; ?></div>
                                        </div>
										<?php if (JFactory::getUser()->guest) :?>
                                            <div class="em-search-engine-learn-more"><a href="<?php echo 'index.php?option=com_users&view=login&return=' . base64_encode(JFactory::getURI())?>"> Connectez-vous pour en savoir plus </a></div>
										<?php else :?>
                                            <div class='em-search-engine-details'><a href="<?php echo $d['fabrik_view_url']; ?>">Consultez l'offre</a></div>
										<?php endif; ?>
                                    </div>
                                </td>
                            </tr>
							<?php
							unset($cherches);
							unset($themes);
							$gCounter++;
						}
						?>

                        </tbody>

						<?php if ($this->hasCalculations) : ?>
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
								<?php
								endforeach;
								?>

                            </tr>
                            </tfoot>
						<?php endif ?>
                    </table>
					<?php if (!empty($data)) :?>
                        <div>
                            <p>Vous n'avez pas trouvé ce que vous cherchiez ? Déposez l'annonce qui vous correspond.</p>
                            <p><a href="/?option=com_fabrik&view=form&formid=102">Proposez une offre</a></p>
                        </div>
					<?php endif; ?>
                </div>

				<?php print_r($this->hiddenFields);?>
            </div>
        </form>
    </div>
</div>

<script>
    jQuery(document).ready(function(){
        jQuery('select.fabrik_filter[multiple]').chosen({
            placeholder_text_single: "<?php echo JText::_('CHOSEN_SELECT_ONE'); ?>",
            placeholder_text_multiple: "<?php echo JText::_('CHOSEN_SELECT_MANY'); ?>",
            no_results_text: "<?php echo JText::_('CHOSEN_NO_RESULTS'); ?>"
        });
    });
</script>
