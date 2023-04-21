<?php
/**
 * @package     Joomla.Site
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
?>

<div class="mod_emundus_banner__banner" style="display: none">
	<?php if(!empty($image_link)) : ?>
        <img src="<?php echo $image_link ?>" style="opacity: 0">
    <?php endif; ?>
</div>

<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        <?php if(!empty($image_link)) : ?>
            document.getElementsByClassName('mod_emundus_banner__banner')[0].style.backgroundImage = "url('<?php echo $image_link ?>')"
            document.getElementsByClassName('mod_emundus_banner__banner')[0].style.display = 'block';
        <?php endif; ?>
    });
</script>
