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

$form  = $this->form;
$model = $this->getModel();
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');

if ($this->params->get('show_page_heading', 1)) : ?>
    <div class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
		<?php echo $this->escape($this->params->get('page_heading')); ?>
    </div>
<?php
endif;

?>

<div id="fabrikDetailsContainer_<?php echo $form->id ?>" class="mb-12">
	<?php if ($this->params->get('show-title', 1)) : ?>
        <div class="page-header em-mb-12 em-flex-row em-flex-space-between">
            <h1><?php echo $form->label; ?></h1>
        </div>
	<?php
	endif;

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

	require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'cache.php');

	$listid = explode('_', $this->form->db_table_name);
	$listid = $listid[sizeof($listid) - 1];

	$cache   = new EmundusHelperCache('com_emundus', '', '5');
	$cacheId = 'gallery_' . $listid;

	$gallery = $cache->get($cacheId);

	$db    = JFactory::getDbo();
	$query = $db->getQuery(true);

	if (empty($gallery)) {
		$query->select('*')
			->from($db->quoteName('#__emundus_setup_gallery'))
			->where($db->quoteName('list_id') . ' = ' . $db->quote($listid));
		$db->setQuery($query);
		$gallery = $db->loadObject();

        if(!empty($gallery)) {
            $query->clear()
                ->select('title,fields')
                ->from($db->quoteName('#__emundus_setup_gallery_detail_tabs'))
                ->where($db->quoteName('parent_id') . ' = ' . $db->quote($gallery->id));
            $db->setQuery($query);
            $gallery->tabs = $db->loadObjectList();
        }

		$result = $cache->set($cacheId, $gallery);
	}
	?>

    <div style="max-width: 60vw">
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
				}
				?>

                <div class="fabrikImageBackground" style="background-image: url('<?php echo $filename; ?>')"></div>
			<?php } ?>
		<?php endif; ?>

		<?php if (!empty($gallery->title)) : ?>
			<?php
			$elt = explode('___', $gallery->title)[1];
			?>
			<?php if (isset($this->elements[$elt])) : ?>
                <h2 class="line-clamp-2 h-14 mt-10">
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
                    <p <?php if($key == 0) : ?>class="active"<?php endif; ?>><?php echo $tab->title ?></p>
				<?php endforeach; ?>
            </div>
			<?php foreach ($gallery->tabs as $key => $tab) : ?>
                <div id="tab_<?php echo $key ?>" <?php if($key > 0) : ?>style="display: none"<?php endif; ?>>
                    <?php
                        $fields = explode(';', $tab->fields);
                    ?>
                    <?php foreach ($fields as $field) : ?>
	                    <?php
	                    $elt = explode('___', $field)[1];
	                    ?>
	                    <?php if (isset($this->elements[$elt])) : ?>
                            <h3 class="mb-3"><?php echo $this->elements[$elt]->label_raw ?></h3>
                            <div class="mb-5">
			                    <?php echo $this->elements[$elt]->element_ro; ?>
                            </div>
	                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
			<?php endforeach; ?>
		<?php endif; ?>

	    <?php if ($gallery->is_voting == 1) : ?>
            <div class="voting-pop em-repeat-card" style="padding: unset">
                <?php if(!empty($gallery->logo)) : ?>
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

                        <div class="p-4"><div class="fabrikImageBackgroundLogo" style="background-image: url('<?php echo $filename; ?>')"></div></div>
	                <?php } ?>
                <?php endif; ?>

                <div class="p-4 voting-details-block">
                    <?php if (!empty($gallery->title)) : ?>
                        <?php
                        $elt = explode('___', $gallery->title)[1];
                        ?>
                        <?php if (isset($this->elements[$elt])) : ?>
                            <h2 class="line-clamp-2">
                                <?php echo $this->elements[$elt]->element_ro; ?>
                            </h2>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($gallery->subtitle)) : ?>
                        <?php
                        $elt = explode('___', $gallery->subtitle)[1];
                        ?>
                        <?php if (isset($this->elements[$elt])) : ?>
                            <p class="em-caption mb-3" style="min-height: 15px">
                                <?php echo $this->elements[$elt]->element_ro; ?>
                            </p>
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
    document.addEventListener("DOMContentLoaded", function() {
        let tabs = document.querySelector('.details-tabs');
        if(tabs) {
            tabs.querySelectorAll('p').forEach((tab, index) => {
                tab.addEventListener('click', () => {
                    tabs.querySelectorAll('p').forEach((tab) => {
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
