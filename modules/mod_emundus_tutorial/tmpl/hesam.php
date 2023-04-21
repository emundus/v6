<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_emundus_tutorial
 * @copyright	Copyright (C) 2020 emundus.fr, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

JFactory::getDocument()->addScript('https://cdn.jsdelivr.net/npm/sweetalert2@9');

if (!empty($articles)) :?>

	<script>

		function tip<?= $user_param->name; ?>() {

            Swal.mixin({
                confirmButtonText: '<?= JText::_('MOD_EMUNDUS_TUTORIAL_NEXT'); ?>',
                customClass: {
                    confirmButton: 'button-2 w-button'
                },
                buttonsStyling: false
            }).queue([

				<?php foreach ($articles as $key => $article) :?>
	                {
	                    title: <?= json_encode('<h2 class="heading no-dash">'.$article['title'].'</h2>'); ?>,
	                    html: <?= json_encode('<div class="underline-small"></div>'.$article['introtext']); ?>
	                },
				<?php endforeach; ?>

			]).then((result) => {
			    <?php if ($run) :?>
                    if (result.value) {
                        jQuery.ajax({
                            type: 'POST',
                            url: 'index.php?option=com_ajax&module=emundus_tutorial&method=markRead&format=json',
                            data: {
                                param: "<?= $user_param->name; ?>",
                                paramType: "<?= $user_param->load_once; ?>"
                            }
                        })
                    }
                <?php endif; ?>
            })
        }

        <?php if ($run) :?>
			 tip<?= $user_param->name; ?>();
		<?php endif; ?>

        if (typeof elements === "undefined") {
            let elements = null;
        }
        elements = document.getElementsByClassName('show-<?= $user_param->name; ?>');
        for (var i = 0; i < elements.length; i++) {
            elements[i].addEventListener('click', tip<?= $user_param->name; ?>, false);
        }
	</script>

<?php endif; ?>
