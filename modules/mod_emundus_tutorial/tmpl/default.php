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
            }).queue([

				<?php foreach ($articles as $key => $article) :?>
	                {
	                    title: <?= json_encode($article['title']); ?>,
	                    html: <?= json_encode($article['introtext']); ?>
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

        document.getElementsByClassName('show-<?= $user_param->name; ?>').forEach(e => {
            e.onclick = tip<?= $user_param->name; ?>();
        })
	</script>

<?php endif; ?>
