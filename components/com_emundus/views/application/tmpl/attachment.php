<?php
/**
 * @package       Joomla
 * @subpackage    eMundus
 * @link          http://www.emundus.fr
 * @copyright     Copyright (C) 2018 eMundus SAS. All rights reserved.
 * @license       GNU/GPL
 * @author        eMundus SAS
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$offset = JFactory::getConfig()->get('offset');
JFactory::getSession()->set('application_layout', 'attachment');

$can_export          = EmundusHelperAccess::asAccessAction(8, 'c', $this->_user->id, $this->fnum);
$can_see_attachments = EmundusHelperAccess::getUserAllowedAttachmentIDs($this->_user->id);
$lang                = JFactory::getLanguage();

$xmlDoc = new DOMDocument();
if ($xmlDoc->load(JPATH_SITE . '/administrator/components/com_emundus/emundus.xml')) {
	$release_version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
}
?>


<div class="row">
    <div class="panel panel-default widget em-container-attachment em-container-form">
        <div class="panel-heading em-container-form-heading">
            <h3 class="panel-title">
                <span class="material-icons">file_present</span>
				<?= JText::_('COM_EMUNDUS_ONBOARD_DOCUMENTS') . ' - ' . $this->attachmentsProgress . ' % ' . JText::_('COM_EMUNDUS_APPLICATION_SENT'); ?>
            </h3>
            <div class="btn-group pull-right">
                <button id="em-prev-file" class="btn btn-info btn-xxl"><span class="material-icons">arrow_back</span>
                </button>
                <button id="em-next-file" class="btn btn-info btn-xxl"><span class="material-icons">arrow_forward</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="em-application-attachment"
     user=<?= $this->_user->id ?>
     fnum=<?= $this->fnum ?>
     currentLanguage=<?= $lang->getTag() ?>
     base=<?= JURI::base() ?>
     attachments="<?= base64_encode(json_encode($this->userAttachments)) ?>"
     rights="<?= base64_encode(json_encode(['can_export' => $can_export, 'can_see' => $can_see_attachments])) ?>"
>
</div>

<script src="media/com_emundus_vue/app_emundus.js?<?php echo $release_version ?>"></script>
