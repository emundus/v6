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

$can_export = EmundusHelperAccess::asAccessAction(8,'c', $this->_user->id, $this->fnum);
$can_see_attachments = EmundusHelperAccess::getUserAllowedAttachmentIDs($this->_user->id);

// echo JHtml::_('content.prepare', '{loadposition filter-builder}');
?>

<div id="em-application-attachment"
    user=<?php echo $this->_user->id ?>
    fnum=<?php echo $this->fnum ?>
>
</div>

<script src="media/com_emundus_vue/app_emundus.js"></script>
<script src="media/com_emundus_vue/chunk-vendors_emundus.js"></script>