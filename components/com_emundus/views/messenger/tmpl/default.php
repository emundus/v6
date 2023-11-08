<?php
/**
 * @package     Joomla
 * @subpackage  com_emundus
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

JText::script('COM_EMUNDUS_MESSENGER_TITLE');
JText::script('COM_EMUNDUS_MESSENGER_SEND_DOCUMENT');
JText::script('COM_EMUNDUS_MESSENGER_ASK_DOCUMENT');
JText::script('COM_EMUNDUS_MESSENGER_DROP_HERE');
JText::script('COM_EMUNDUS_MESSENGER_SEND');
JText::script('COM_EMUNDUS_MESSENGER_WRITE_MESSAGE');

$user = JFactory::getUser()->id;

require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();
?>
<div id="em-component-vue" component="messages" user="<?= $user ?>" modal="false"></div>

<script src="media/com_emundus_vue/app_emundus.js?<?php echo $hash ?>"></script>
