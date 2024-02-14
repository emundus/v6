<?php
/**
 * Bootstrap Details Template
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;use Joomla\CMS\Language\Text;

$form  = $this->form;
$model = $this->getModel();
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'gallery.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'vote.php');
$m_gallery = new EmundusModelGallery();
$m_vote    = new EmundusModelVote();

JText::script('COM_FABRIK_VOTE_MODAL_TEXT');
JText::script('COM_FABRIK_ERROR_PLEASE_COMPLETE_EMAIL');
JText::script('COM_FABRIK_VOTE_MODAL_YES');
JText::script('COM_FABRIK_VOTE_MODAL_GO_BACK');
JText::script('COM_FABRIK_VOTE_MODAL_HOME_LINK');
JText::script('COM_FABRIK_VOTE_MODAL_NO');
JText::script('COM_FABRIK_VOTE_MODAL_SUCCESS_TITLE');
JText::script('COM_FABRIK_VOTE_MODAL_SUCCESS_TEXT');
JText::script('COM_FABRIK_VOTE_MODAL_ERROR_TITLE');
JText::script('COM_FABRIK_VOTE_MODAL_ERROR_TEXT');

$listid = explode('_', $this->form->db_table_name);
$listid = $listid[sizeof($listid) - 1];

$gallery = $m_gallery->getGalleryByList($listid);
$votes   = $m_vote->getVotesByUser();

$user = Factory::getApplication()->getIdentity();

$db    = Factory::getDbo();
$query = $db->getQuery(true);

if ($this->params->get('show_page_heading', 1)) : ?>
    <div class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
		<?php echo $this->escape($this->params->get('page_heading')); ?>
    </div>
<?php
endif;

?>

<div id="fabrikDetailsContainer_<?php echo $form->id ?>" class="mb-12">
	<?php
	echo $form->intro;
	if ($this->isMambot) :
		echo '<div class="fabrikForm fabrikDetails fabrikIsMambot" id="' . $form->formid . '">';
	else :
		echo '<div class="fabrikForm fabrikDetails" id="' . $form->formid . '">';
	endif;
	echo $this->plugintop;
	echo $this->loadTemplate('buttons');
	echo $this->loadTemplate('relateddata');
	$this->elements = [];
	foreach ($this->groups as $group) :
		$this->group = $group;
		?>

        <div class="em-mt-16 <?php echo $group->class; ?>" id="group<?php echo $group->id; ?>"
             style="<?php echo $group->css; ?>">

			<?php
			if (!empty($group->intro)) : ?>
                <div class="groupintro"><?php echo $group->intro ?></div>
			<?php
			endif;

			// Load the group template - this can be :
			//  * default_group.php - standard group non-repeating rendered as an unordered list
			//  * default_repeatgroup.php - repeat group rendered as an unordered list
			//  * default_repeatgroup_table.php - repeat group rendered in a table.

			$this->elements = array_merge($group->elements, $this->elements);

			if (!empty($group->outro)) : ?>
                <div class="groupoutro"><?php echo $group->outro ?></div>
			<?php
			endif;
			?>
        </div>
	<?php
	endforeach;

	$voted = false;
	foreach ($votes as $vote) {
		if ($vote->ccid == $this->elements['id']->value) {
			$voted = true;
		}
	}
	?>

    <div class="voting-details-group">
		<?php if (!empty($gallery->banner)) : ?>
			<?php
			$filename = '';
			$fnum     = $this->elements['fnum']->value;

			if (!empty($fnum)) {
				$query->clear()
					->select('ecc.applicant_id,eu.filename')
					->from($db->quoteName('#__emundus_uploads', 'eu'))
					->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'ecc') . ' ON ' . $db->quoteName('ecc.fnum') . ' = ' . $db->quoteName('eu.fnum'))
					->where($db->quoteName('ecc.fnum') . ' = ' . $db->quote($fnum))
					->where($db->quoteName('eu.attachment_id') . ' = ' . $db->quote($gallery->banner));
				$db->setQuery($query);
				$file = $db->loadObject();

				if (!empty($file->filename)) {
					$filename_applicant = JPATH_ROOT . '/images/emundus/files/' . $file->applicant_id . '/' . $file->filename;
					$filename           = JUri::base() . 'images/emundus/gallery/' . $file->applicant_id . '/' . $file->filename;
					if (!file_exists($filename)) {
						if (!is_dir(JPATH_ROOT . '/images/emundus/gallery/' . $file->applicant_id)) {
							mkdir(JPATH_ROOT . '/images/emundus/gallery/' . $file->applicant_id, 0777, true);
						}
						copy($filename_applicant, JPATH_ROOT . '/images/emundus/gallery/' . $file->applicant_id . '/' . $file->filename);
					}
				} else {
                    $filename = JUri::base() . 'media/com_emundus/images/gallery/default_banner.png';
                }
				?>

                <div class="fabrikImageBackground" style="background-image: url('<?php echo $filename; ?>')"></div>
			<?php } ?>
		<?php endif; ?>
        <a class="em-back-button em-pointer" onclick="history.go(-1)"><span class="material-icons em-mr-4" aria-hidden="true">navigate_before</span><?php echo Text::_('COM_FABRIK_VOTE_GO_BACK'); ?></a>

        <div class="p-8">
            <?php if (!empty($gallery->title)) : ?>
                <?php
                $elt = explode('___', $gallery->title)[1];
                ?>
                <?php if (isset($this->elements[$elt])) : ?>
                    <h1 class="mt-2 mb-8 em-font-weight-700">
                        <?php echo $this->elements[$elt]->element_ro; ?>
                    </h1>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($gallery->resume)) : ?>
                <?php
                $elt = explode('___', $gallery->resume)[1];
                ?>
                <?php if (isset($this->elements[$elt])) : ?>
                    <h2 class="mb-6">
                        <?php echo $this->elements[$elt]->element_ro; ?>
                    </h2>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($gallery->tags)) : ?>
                <?php
                $elt = explode('___', $gallery->tags)[1];
                ?>
                <?php if (isset($this->elements[$elt])) : ?>
                    <div class="mb-3 tags" style="min-height: 30px">
                        <?php echo $this->elements[$elt]->element_ro; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($gallery->tabs)) : ?>
                <div class="details-tabs mt-10 flex items-center mb-8">
                    <?php
                    ?>
                    <?php foreach ($gallery->tabs as $key => $tab) : ?>
                    <button type='button' <?php if ($key == 0) : ?>class='active' aria-expanded='true'<?php else: ?> aria-expanded='false'<?php endif; ?>><?php echo $tab->title ?></button>
                    <?php endforeach; ?>
                </div>
                <?php foreach ($gallery->tabs as $key => $tab) : ?>
                    <div id="tab_<?php echo $key ?>" <?php if ($key > 0) : ?>style="display: none"<?php endif; ?>>
                        <?php
                        $fields = explode(';', $tab->fields);
                        ?>
                        <?php foreach ($fields as $field) : ?>
                            <?php
                            $elt = explode('___', $field)[1];
                            ?>
                            <?php if (isset($this->elements[$elt])) : ?>
                                <h3 class="mb-3 em-font-weight-700"><?php echo $this->elements[$elt]->label_raw ?></h3>

                                    <div class="mb-5">
                                        <p class="em-applicant-text-color">
                                        <?php echo $this->elements[$elt]->element_ro; ?>
                                        </p>
                                    </div>

                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

		<?php if ($gallery->is_voting == 1) : ?>
            <div class="voting-pop em-repeat-card" style="padding: unset">
				<?php if (!empty($gallery->logo)) : ?>
					<?php
					$filename = '';
					$fnum     = $this->elements['fnum']->value;

					if (!empty($fnum)) {
						$query->clear()
							->select('ecc.applicant_id,eu.filename')
							->from($db->quoteName('#__emundus_uploads', 'eu'))
							->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'ecc') . ' ON ' . $db->quoteName('ecc.fnum') . ' = ' . $db->quoteName('eu.fnum'))
							->where($db->quoteName('ecc.fnum') . ' = ' . $db->quote($fnum))
							->where($db->quoteName('eu.attachment_id') . ' = ' . $db->quote($gallery->logo));
						$db->setQuery($query);
						$file = $db->loadObject();

						if (!empty($file->filename)) {
							$filename_applicant = JPATH_ROOT . '/images/emundus/files/' . $file->applicant_id . '/' . $file->filename;
							$filename           = JUri::base() . 'images/emundus/gallery/' . $file->applicant_id . '/' . $file->filename;
							if (!file_exists($filename)) {
								if (!is_dir(JPATH_ROOT . '/images/emundus/gallery/' . $file->applicant_id)) {
									mkdir(JPATH_ROOT . '/images/emundus/gallery/' . $file->applicant_id, 0777, true);
								}
								copy($filename_applicant, JPATH_ROOT . '/images/emundus/gallery/' . $file->applicant_id . '/' . $file->filename);
							}
						}
						?>

                        <div class="p-4">
                            <div class="fabrikImageBackgroundLogo"
                                 style="background-image: url('<?php echo $filename; ?>')"></div>
                        </div>
					<?php } ?>
				<?php endif; ?>

                <div class="p-4 voting-details-block">
					<?php if (!empty($gallery->title)) : ?>
						<?php
						$elt = explode('___', $gallery->title)[1];
						?>
						<?php if (isset($this->elements[$elt])) : ?>
                            <h2 class="line-clamp-2 em-font-weight-700">
								<?php echo $this->elements[$elt]->element_ro; ?>
                            </h2>
						<?php endif; ?>
					<?php endif; ?>

					<?php if (!empty($gallery->subtitle)) : ?>
						<?php
						$elt = explode('___', $gallery->subtitle)[1];
						?>
						<?php if (isset($this->elements[$elt])) : ?>
                            <p class="em-caption mb-5 mt-2 flex items-center" style="min-height: 15px">
								<?php if (!empty($gallery->subtitle_icon)) : ?>
                                    <span class="material-icons-outlined mr-2" aria-hidden="true"><?php echo $gallery->subtitle_icon; ?></span>
								<?php endif; ?>
	                            <?php echo $this->elements[$elt]->element_ro; ?>
                            </p>
						<?php endif; ?>
					<?php endif; ?>

					<?php if (empty($votes)) : ?>
                        <button onclick="vote('<?php echo $user->guest ?>','<?php echo $listid ?>','<?php echo $this->elements['id']->value ?>','<?php echo $user->email ?>')"
                                type="button"
                                class="em-applicant-primary-button w-full mt-3 em-white-space-normal"
                                style="text-transform: unset">
							<?php echo JText::_('COM_FABRIK_VOTE') ?>
                        </button>
					<?php else : ?>
						<?php if (!$voted) : ?>
                            <button
								<?php if (count($votes) < $gallery->max) : ?>onclick="vote('<?php echo $user->guest ?>','<?php echo $listid ?>','<?php echo $this->elements['id']->value ?>','<?php echo $user->email ?>')"
								<?php else : ?>disabled<?php endif; ?>
                                type="button"
                                class="em-applicant-primary-button w-full mt-3"
                                style="text-transform: unset">
								<?php if (count($votes) < $gallery->max) : ?>
									<?php echo JText::_('COM_FABRIK_VOTE') ?>
								<?php else: ?>
									<?php echo JText::_('COM_FABRIK_ALREADY_VOTED_FOR_OTHER') ?>
								<?php endif; ?>
                            </button>
						<?php else : ?>
                            <button disabled
                                    type="button"
                                    class="em-applicant-primary-button w-full mt-3"
                                    style="text-transform: unset">
								<?php echo JText::_('COM_FABRIK_ALREADY_VOTED') ?>
                            </button>
						<?php endif; ?>
					<?php endif; ?>
                </div>
            </div>
		<?php endif; ?>
    </div>

	<?php
	echo $this->pluginbottom;
	echo '</div>';
	echo $form->outro;
	echo $this->pluginend; ?>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let tabs = document.querySelector('.details-tabs');
        if (tabs) {
            tabs.querySelectorAll('button').forEach((tab, index) => {
                tab.addEventListener('click', () => {
                    tabs.querySelectorAll('button').forEach((tab) => {
                        tab.classList.remove('active');
                    });
                    tab.classList.add('active');
                    document.querySelectorAll('[id^="tab_"]').forEach((tab) => {
                        tab.style.display = 'none';
                    });
                    document.querySelector('#tab_' + index).style.display = 'block';
                });
            });
        }
    });
</script>
