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

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'cache.php');

$listid = $this->table->id;
$db_table_name = $this->table->db_table_name;

$cache = new EmundusHelperCache('com_emundus');
$cacheId = 'gallery_' . $listid;

$gallery = $cache->get($cacheId);

$db    = JFactory::getDbo();
$query = $db->getQuery(true);

if(empty($gallery)) {
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

	$cache->set($cacheId, $gallery);
}

?>
<div id="<?php echo $this->_row->id;?>" class="<?php echo $this->_row->class;?> em-repeat-card-no-padding em-pb-24">
    <?php if(!empty($gallery->image)) : ?>
        <?php
	        $filename = '';
            $fnum_elt = $db_table_name. '___fnum';
            $fnum = $this->_row->data->{$fnum_elt};

            if(!empty($fnum)) {
                $query->clear()
                    ->select('ecc.applicant_id,eu.filename')
                    ->from($db->quoteName('#__emundus_uploads','eu'))
                    ->leftJoin($db->quoteName('#__emundus_campaign_candidature','ecc').' ON '.$db->quoteName('ecc.fnum').' = '.$db->quoteName('eu.fnum'))
                    ->where($db->quoteName('ecc.fnum') . ' = ' . $db->quote($fnum))
                    ->where($db->quoteName('eu.attachment_id') . ' = ' . $db->quote($gallery->image));
                $db->setQuery($query);
                $file = $db->loadObject();
                
                if(!empty($file->filename)) {
	                $filename_applicant = JPATH_ROOT . '/images/emundus/files/' . $file->applicant_id . '/' . $file->filename;
	                $filename = JUri::base() . 'images/emundus/gallery/' . $file->applicant_id . '/' . $file->filename;
                    if(!file_exists($filename)) {
                        if(!is_dir(JPATH_ROOT . '/images/emundus/gallery/' . $file->applicant_id)) {
                            mkdir(JPATH_ROOT . '/images/emundus/gallery/' . $file->applicant_id, 0777, true);
                        }
                        copy($filename_applicant, JPATH_ROOT . '/images/emundus/gallery/' . $file->applicant_id . '/' . $file->filename);
                    }
                }
            }
        ?>

        <div class="fabrikImageBackground" style="background-image: url('<?php echo $filename; ?>')"></div>
    <?php endif; ?>
    <div class="p-4">
        <?php if(!empty($gallery->title)) : ?>
            <h2 class="line-clamp-2 h-14">
                <?php echo isset($this->_row->data) ? $this->_row->data->{$gallery->title} : '';?>
            </h2>
        <?php endif; ?>
        <?php if(!empty($gallery->subtitle)) : ?>
            <p class="em-caption mb-3" style="min-height: 15px">
                <?php echo isset($this->_row->data) ? $this->_row->data->{$gallery->subtitle} : '';?>
            </p>
        <?php endif; ?>
        <?php if(!empty($gallery->tags)) : ?>
            <div class="mb-3 tags" style="min-height: 30px">
                <?php echo isset($this->_row->data) ? $this->_row->data->{$gallery->tags} : '';?>
            </div>
        <?php endif; ?>
        <?php if(!empty($gallery->resume)) : ?>
            <p class="mb-3 line-clamp-4 h-20">
                <?php echo isset($this->_row->data) ? $this->_row->data->{$gallery->resume} : '';?>
            </p>
        <?php endif; ?>

        <a href="<?php echo $this->_row->data->fabrik_view_url ?>" class="btn btn-primary w-full" style="text-transform: unset"><?php echo JText::_('COM_FABRIK_VOTING_GO_DETAILS') ?></a>
    </div>
</div>
