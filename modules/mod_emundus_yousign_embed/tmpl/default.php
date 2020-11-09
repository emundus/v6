<?php

/**
 * @package   Joomla.Site
 * @subpackage  eMundus
 * @copyright Copyright (C) 2020 emundus.fr. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<div class="em-yousign-embed">
    <iframe src="https://staging-app.yousign.com/procedure/sign?members=<?= $yousign_member_id.(!empty($signature_ui)?'&signatureUi='.$signature_ui:''); ?>"></iframe>
</div>
