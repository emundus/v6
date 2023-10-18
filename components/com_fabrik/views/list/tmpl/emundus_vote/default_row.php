<?php
/**
 * Fabrik List Template: Admin Row
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'gallery.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'vote.php');
$m_gallery = new EmundusModelGallery();
$m_vote    = new EmundusModelVote();

$listid        = $this->table->id;
$db_table_name = $this->table->db_table_name;

$gallery = $m_gallery->getGalleryByList($listid);
$votes   = $m_vote->getVotesByUser();

$user = Factory::getApplication()->getIdentity();

$db    = Factory::getDbo();
$query = $db->getQuery(true);

?>
<div id="<?php echo $this->_row->id; ?>" class="<?php echo $this->_row->class; ?> em-repeat-card-no-padding em-pb-24 relative">
	<?php
	$voted = false;
	foreach ($votes as $vote) {
		if ($vote->ccid == $this->_row->data->{$db_table_name . '___id'}) {
			$voted = true;
		}
	}
	?>

	<?php if (!empty($gallery->image)) : ?>
		<?php
		$filename = '';
		$fnum_elt = $db_table_name . '___fnum';
		$fnum     = $this->_row->data->{$fnum_elt};

		if (!empty($fnum)) {
			$query->clear()
				->select('ecc.applicant_id,eu.filename')
				->from($db->quoteName('#__emundus_uploads', 'eu'))
				->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'ecc') . ' ON ' . $db->quoteName('ecc.fnum') . ' = ' . $db->quoteName('eu.fnum'))
				->where($db->quoteName('ecc.fnum') . ' = ' . $db->quote($fnum))
				->where($db->quoteName('eu.attachment_id') . ' = ' . $db->quote($gallery->image));
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
		}
		?>

        <?php if($voted) : ?>
            <div class="heart-voted-icon">
                <span class="material-icons-outlined">favorite</span>
            </div>
        <?php endif; ?>
        <div class="fabrikImageBackground" style="background-image: url('<?php echo $filename; ?>')"></div>
	<?php endif; ?>
    <div class="p-4">
		<?php if (!empty($gallery->title)) : ?>
            <h2 class="line-clamp-2 h-14">
				<?php echo isset($this->_row->data) ? $this->_row->data->{$gallery->title} : ''; ?>
            </h2>
		<?php endif; ?>
		<?php if (!empty($gallery->subtitle)) : ?>
            <p class="em-caption mb-3" style="min-height: 15px">
				<?php echo isset($this->_row->data) ? $this->_row->data->{$gallery->subtitle} : ''; ?>
            </p>
		<?php endif; ?>
		<?php if (!empty($gallery->tags)) : ?>
            <div class="mb-3 tags" style="min-height: 30px">
				<?php echo isset($this->_row->data) ? $this->_row->data->{$gallery->tags} : ''; ?>
            </div>
		<?php endif; ?>
		<?php if (!empty($gallery->resume)) : ?>
            <p class="mb-3 line-clamp-4 h-20">
				<?php echo isset($this->_row->data) ? $this->_row->data->{$gallery->resume} : ''; ?>
            </p>
		<?php endif; ?>

        <a href="<?php echo $this->_row->data->fabrik_view_url ?>" class="em-applicant-secondary-button w-full"
           style="text-transform: unset"><?php echo JText::_('COM_FABRIK_VOTING_GO_DETAILS') ?></a>

		<?php if ($gallery->is_voting == 1) : ?>
			<?php if (empty($votes)) : ?>
                <button onclick="vote('<?php echo $user->guest ?>','<?php echo $listid ?>','<?php echo $this->_row->data->{$db_table_name . '___id'} ?>','<?php echo $this->user->email ?>')"
                        type="button"
                        class="em-applicant-primary-button w-full mt-3"
                        style="text-transform: unset">
                    <?php echo JText::_('COM_FABRIK_VOTE') ?>
                </button>
            <?php else : ?>
                <?php if (!$voted) : ?>
                    <button <?php if(count($votes) < $gallery->max) : ?>onclick="vote('<?php echo $user->guest ?>','<?php echo $listid ?>','<?php echo $this->_row->data->{$db_table_name . '___id'} ?>','<?php echo $this->user->email ?>')"<?php else : ?>disabled<?php endif; ?>
                            type="button"
                            class="em-applicant-primary-button w-full mt-3"
                            style="text-transform: unset">
					<?php if(count($votes) < $gallery->max) : ?>
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
		<?php endif; ?>
    </div>
</div>