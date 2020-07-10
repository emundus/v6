<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_emundus_tutorial
 * @copyright	Copyright (C) 2020 emundus.fr, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
$document = JFactory::getDocument();
$document->addScript('https://cdn.jsdelivr.net/npm/sweetalert2@9');
$document->addStyleSheet("modules/mod_emundus_tutorial/style/mod_emundus_tutorial.css" );

if (!empty($articles)) :?>

    <script>

        function tip<?= $user_param->name; ?>() {

            let path = window.location.href.split('/');
            let route = path[path.length - 1];
            let url = route.split('&');
            let parameters = [];
            let view = '';
            let layout = '';
            let cid = '';
            if(url.length > 1){
                url.forEach(parameter => {
                    parameters.push({
                        parameter: parameter.split('=')[0],
                        value: parameter.split('=')[1]
                    });
                });
            }
            Object.values(parameters).forEach((parameter) => {
                if(parameter.parameter ==  'view'){
                    view = parameter.value;
                } else if(parameter.parameter ==  'layout'){
                    layout = parameter.value;
                } else if(parameter.parameter ==  'cid'){
                    cid = parameter.value;
                }
            });

            let queue = []

            <?php foreach ($articles as $key => $article) :?>
                <?php
                    $article_params = json_decode($article['note'],true);

                    if (!empty($article_params['view'])) {
	                    $view = json_encode($article_params['view']);
                    } else {
                        $view = "null";
                    }

                    if (!empty($article_params['layout'])) {
	                    $layout = json_encode($article_params['layout']);
                    } else {
                        $layout = "null";
                    }

                    if (!empty($article_params['link'])) {
	                    $link = json_encode($article_params['link']);
                    } else {
	                    $link = "null";
                    }
                ?>


                <?php if ($view != 'null') :?>
                    if(view == <?= $view ?>){
                        <?php if ($layout != 'null') :?>
                        if(layout == <?= $layout ?>){
                            <?php if ($link != 'null') :?>
                                let link = <?= $link; ?>;
                            <?php if (strpos($link,'cid') != false) :?>
                                link = link + cid;
                            <?php endif; ?>
                                queue.push({
                                    title: <?= json_encode($article['title']); ?>,
                                    html: <?= json_encode($article['introtext']); ?>,
                                    confirmButtonText: '<a href="' + link + '" class="tutorial-link">' + '<?= JText::_(json_decode($article['note'], true)['confirm_text']) ?>' + '</a>',
                                    customClass: {
                                        confirmButton: 'swal-button-link',
                                    }
                                })
                            <?php else :?>
                                queue.push({
                                    title: <?= json_encode($article['title']); ?>,
                                    html: <?= json_encode($article['introtext']); ?>,
                                    confirmButtonText: '<?= JText::_(json_decode($article['note'], true)['confirm_text']) ?>',
                                })
                            <?php endif; ?>
                        }
                        <?php else :?>
                        <?php if ($link != 'null') :?>
                            let link = <?= $link; ?>;
                            queue.push({
                                title: <?= json_encode($article['title']); ?>,
                                html: <?= json_encode($article['introtext']); ?>,
                                confirmButtonText: '<a href="' + link + '" class="tutorial-link">' + '<?= JText::_(json_decode($article['note'], true)['confirm_text']) ?>' + '</a>',
                                customClass: {
                                    confirmButton: 'swal-button-link',
                                }
                            })
                        <?php else :?>
                            queue.push({
                                title: <?= json_encode($article['title']); ?>,
                                html: <?= json_encode($article['introtext']); ?>,
                                confirmButtonText: '<?= JText::_(json_decode($article['note'], true)['confirm_text']) ?>',
                            })
                        <?php endif; ?>
                        <?php endif; ?>
                    }
                    <?php else :?>
                        <?php if ($link != 'null') :?>
                            let link = <?= $link; ?>;
                            queue.push({
                                title: <?= json_encode($article['title']); ?>,
                                html: <?= json_encode($article['introtext']); ?>,
                                confirmButtonText: '<a href="' + link + '" class="tutorial-link">' + '<?= JText::_(json_decode($article['note'], true)['confirm_text']) ?>' + '</a>',
                                customClass: {
                                    confirmButton: 'swal-button-link',
                                }
                            })
                        <?php else :?>
                            queue.push({
                                title: <?= json_encode($article['title']); ?>,
                                html: <?= json_encode($article['introtext']); ?>,
                                confirmButtonText: '<?= JText::_(json_decode($article['note'], true)['confirm_text']) ?>',
                            })
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>

            Swal.mixin({
                confirmButtonColor: '#de6339',
                showCloseButton: true,
                allowOutsideClick: false
            }).queue(queue).then((result) => {
                <?php if ($run) :?>
                if(result.value) {
                    if (result.value.length > 0) {
                        jQuery.ajax({
                            type: 'POST',
                            url: 'index.php?option=com_ajax&module=emundus_tutorial&method=markRead&format=json',
                            data: {
                                param: "<?= $user_param->name; ?>",
                                paramType: "<?= $user_param->load_once; ?>"
                            }
                        });
                    }
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
