<?php

/**
 * @package     Joomla.Site
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2020 emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$eMConfig = JComponentHelper::getParams('com_emundus');

?>
<div class="em-w-100 em-flex-row" style="justify-content: flex-end;">
    <a class="em-mt-16 em-pointer" href="<?= $yousignSession['iframe_url']; ?>" target="_blank">
        <button class="em-primary-button">Signer l'engagement de confidentialité.</button>
    </a>
</div>
