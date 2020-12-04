<?php

/**
 * @package   Joomla.Site
 * @subpackage  eMundus
 * @copyright Copyright (C) 2020 emundus.fr. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$eMConfig = JComponentHelper::getParams('com_emundus');

if ($eMConfig->get('yousign_prod', 'https://staging-api.yousign.com') === 'https://staging-api.yousign.com') {
	$host = 'https://staging-app.yousign.com';
} else {
	$host = 'https://webapp.yousign.com';
}

?>
<div class="em-yousign-embed">
    <iframe src="<?= $host; ?>/procedure/sign?members=<?= $yousign_member_id.(!empty($signature_ui)?'&signatureUi='.$signature_ui:''); ?>"></iframe>
</div>
