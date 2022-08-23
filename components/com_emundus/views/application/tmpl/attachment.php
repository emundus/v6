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

$lang = JFactory::getLanguage();
?>

<!-- <div id="em-vue-filter-builder"></div> -->

<div id="em-application-attachment"
    user=<?php echo $this->_user->id ?>
    fnum=<?php echo $this->fnum ?>
    lang=<?php echo $lang->getTag() ?>
    base=<?php echo JURI::base() ?>
>
</div>

<script src="media/com_emundus_vue/app_emundus.js?1"></script>
