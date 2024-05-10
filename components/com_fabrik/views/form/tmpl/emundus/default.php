<?php
/**
 * eMundus Form Template
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$form      = $this->form;
$model     = $this->getModel();
$groupTmpl = $model->editable ? 'group' : 'group_details';
$active    = ($form->error != '') ? '' : ' fabrikHide';

$eMConfig = JComponentHelper::getParams('com_emundus');
$display_required_icon = $eMConfig->get('display_required_icon', 1);

$pageClass = $this->params->get('pageclass_sfx', '');

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
$m_users = new EmundusModelUsers();
$profile_form = $m_users->getProfileForm();

JText::script('COM_EMUNDUS_FABRIK_WANT_EXIT_FORM_TITLE');
JText::script('COM_EMUNDUS_FABRIK_WANT_EXIT_FORM_TEXT');
JText::script('COM_EMUNDUS_FABRIK_WANT_EXIT_FORM_CONFIRM');
JText::script('COM_EMUNDUS_FABRIK_WANT_EXIT_FORM_CANCEL');
JText::script('PLEASE_CHECK_THIS_FIELD');

JText::script('COM_EMUNDUS_FABRIK_NEW_FILE');
JText::script('COM_EMUNDUS_FABRIK_NEW_FILE_DESC');

if ($pageClass !== '') :
	echo '<div class="' . $pageClass . '">';
endif;

if ($this->params->get('show_page_heading', 1)) : ?>
    <div class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
		<?php echo $this->escape($this->params->get('page_heading')); ?>
    </div>
<?php
endif;
?>
<div class="emundus-form p-6">
    <?php  if($form->id == $profile_form) : ?>
        <iframe id="background-shapes" alt="<?= JText::_('MOD_EM_FORM_IFRAME') ?>"></iframe>
    <?php endif; ?>
    <div class="mb-0 fabrikMainError alert alert-error fabrikError<?php echo $active ?>">
        <span class="material-icons">cancel</span>
		<?php echo $form->error; ?>
    </div>
    <div class="mb-8">
        <div class="em-mt-8">
	        <?php if ($this->params->get('show-title', 1)) : ?>
                <?php if($display_required_icon == 0) : ?>
                    <p class="mb-5 text-neutral-600"><?= JText::_('COM_FABRIK_REQUIRED_ICON_NOT_DISPLAYED') ?></p>
                <?php endif; ?>
                <div class="page-header">
			        <?php $title = trim(preg_replace('/^([^-]+ - )/', '', $form->label)); ?>
                    <h2 class="after-em-border after:bg-red-800"><?= JText::_($title) ?></h2>
                </div>
	        <?php endif; ?>
        </div>


	    <?php if(!empty($form->intro)) : ?>
            <div class="em-form-intro mt-4">
                <?php
                echo trim($form->intro);
                ?>
            </div>
        <?php endif; ?>
    </div>
    <form method="post" <?php echo $form->attribs ?>>
		<?php
		echo $this->plugintop;
		?>
        
        <?php
        $buttons_tmpl = $this->loadTemplate('buttons');
        $related_datas_tmpl = $this->loadTemplate('relateddata');
        ?>

        <?php if (!empty($buttons_tmpl) || !empty($related_datas_tmpl)) : ?>
            <div class="row-fluid nav">
                <div class="<?php echo FabrikHelperHTML::getGridSpan(6); ?> pull-right">
                    <?php
                    echo $this->loadTemplate('buttons');
                    ?>
                </div>
                <div class="<?php echo FabrikHelperHTML::getGridSpan(6); ?>">
                    <?php
                    echo $this->loadTemplate('relateddata');
                    ?>
                </div>
            </div>
        <?php endif; ?>

		<?php
        $this->index_element_id = 0;
        foreach ($this->groups as $group) :
			$this->group = $group;
			?>

            <div class="mb-6 <?php echo $group->class; ?> <?php if ($group->columns > 1) {
				echo 'fabrikGroupColumns-' . $group->columns . ' fabrikGroupColumns';
			} ?>" id="group<?php echo $group->id; ?>" style="<?php echo $group->css; ?>">
                <?php if(($group->showLegend && !empty($group->title)) || !empty($group->intro)) : ?>
                <div class="mb-7">
                    <?php
                    if ($group->showLegend) :?>
                        <h3 class="after-em-border after:bg-neutral-500"><?php echo $group->title; ?></h3>
                    <?php
                    endif;

                    if (!empty($group->intro)) : ?>
                        <div class="groupintro mt-4"><?php echo $group->intro ?></div>
                    <?php endif; ?>

	                <?php if(!empty($group->maxRepeat) && $group->maxRepeat > 1) : ?>
                        <p class="em-text-neutral-600 mt-2"><?php echo JText::sprintf('COM_FABRIK_REPEAT_GROUP_MAX',$group->maxRepeat) ?></p>
	                <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php

				/* Load the group template - this can be :
				 *  * default_group.php - standard group non-repeating rendered as an unordered list
				 *  * default_repeatgroup.php - repeat group rendered as an unordered list
				 *  * default_repeatgroup_table.php - repeat group rendered in a table.
				 */
				$this->elements = $group->elements;
                echo $this->loadTemplate($group->tmpl);

				if (!empty($group->outro)) : ?>
                    <div class="groupoutro"><?php echo $group->outro ?></div>
				<?php
				endif;
				?>
            </div>
		<?php
		endforeach;
		if ($model->editable) : ?>
            <div class="fabrikHiddenFields">
				<?php echo $this->hiddenFields; ?>
            </div>
		<?php
		endif;

		echo $this->pluginbottom;
		echo $this->loadTemplate('actions');
		?>
    </form>
	<?php
	echo $form->outro;
	echo $this->pluginend;
	echo FabrikHelperHTML::keepalive();

	if ($pageClass !== '') :
		echo '</div>';
	endif; ?>
</div>




<?php

$user = JFactory::getUser();
$fnum = JFactory::getSession()->get('emundusUser')->fnum;
if (EmundusHelperAccess::asAccessAction(10, 'r', $user->id, $fnum)) {

    JText::script('COM_EMUNDUS_COMMENTS_ADD_COMMENT');
    JText::script('COM_EMUNDUS_COMMENTS_ERROR_PLEASE_COMPLETE');
    JText::script('COM_EMUNDUS_COMMENTS_ENTER_COMMENT');
    JText::script('COM_EMUNDUS_COMMENTS_SENT');
    JText::script('COM_EMUNDUS_FILES_ADD_COMMENT');
    JText::script('COM_EMUNDUS_FILES_CANNOT_ACCESS_COMMENTS');
    JText::script('COM_EMUNDUS_FILES_CANNOT_ACCESS_COMMENTS_DESC');
    JText::script('COM_EMUNDUS_FILES_COMMENT_TITLE');
    JText::script('COM_EMUNDUS_FILES_COMMENT_BODY');
    JText::script('COM_EMUNDUS_FILES_VALIDATE_COMMENT');
    JText::script('COM_EMUNDUS_FILES_COMMENT_DELETE');
    JText::script('COM_EMUNDUS_COMMENTS_VISIBLE_PARTNERS');
    JText::script('COM_EMUNDUS_COMMENTS_VISIBLE_ALL');
    JText::script('COM_EMUNDUS_COMMENTS_ANSWERS');
    JText::script('COM_EMUNDUS_COMMENTS_ANSWER');
    JText::script('COM_EMUNDUS_COMMENTS_ADD_COMMENT_ON');
    JText::script('COM_EMUNDUS_COMMENTS_CANCEL');

    $ccid = EmundusHelperFiles::getIdFromFnum($fnum);
    $coordinator_access = EmundusHelperAccess::asCoordinatorAccessLevel($user->id);
    $sysadmin_access = EmundusHelperAccess::isAdministrator($user->id);
    $current_lang = JFactory::getLanguage();
    $short_lang = substr($current_lang->getTag(), 0 , 2);
    $languages = JLanguageHelper::getLanguages();
    if (count($languages) > 1) {
        $many_languages = '1';
        require_once JPATH_SITE . '/components/com_emundus/models/translations.php';
        $m_translations = new EmundusModelTranslations();
        $default_lang = $m_translations->getDefaultLanguage()->lang_code;
    } else {
        $many_languages = '0';
        $default_lang = $current_lang;
    }

    $xmlDoc = new DOMDocument();
    if ($xmlDoc->load(JPATH_SITE.'/administrator/components/com_emundus/emundus.xml')) {
        $release_version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
    }

    ?>
    <aside id="aside-comment-section" class="fixed right-0 em-white-bg shadow ease-out closed">
        <!-- Comments -->
        <div class="flex flex-row relative">
                        <span class="open-comment material-icons-outlined cursor-pointer absolute top-14 em-bg-main-500 rounded-l-lg em-text-neutral-300" onclick="openCommentAside()">
                            comment
                        </span>
            <span class="close-comment material-icons-outlined cursor-pointer absolute top-14 em-bg-main-500 rounded-l-lg em-text-neutral-300" onclick="openCommentAside()">
                            close
                        </span>
            <div id="em-component-vue"
                 component="comments"
                 user="<?= $user->id ?>"
                 ccid="<?= $ccid ?>"
                 is_applicant="1"
                 currentLanguage="<?= $current_lang->getTag() ?>"
                 shortLang="<?= $short_lang ?>"
                 coordinatorAccess="<?= $coordinator_access ?>"
                 sysadminAccess="<?= $sysadmin_access ?>"
                 manyLanguages="<?= $many_languages ?>"
            >
            </div>
        </div>
    </aside>
    <script src="/media/com_emundus_vue/app_emundus.js?<?php echo $release_version ?>"></script>
    <script src="/media/com_emundus_vue/chunk-vendors_emundus.js?<?php echo $release_version ?>"></script>

    <script>
        function openCommentAside() {
            const aside = document.getElementById('aside-comment-section');
            if (aside.classList.contains('closed')) {
                aside.classList.remove('closed');
            } else {
                aside.classList.add('closed');
            }
        }

        function openModalAddComment(element)
        {
            const event = new CustomEvent('openModalAddComment', {
                detail: {
                    targetType: element.dataset.targetType,
                    targetId: element.dataset.targetId,
                }
            });

            document.dispatchEvent(event);
        }

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('comment-icon')) {
                openModalAddComment(e.target);
            }
        });
    </script>
    <?php
}
?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Set sidebar sticky depends on height of header
        const headerNav = document.getElementById('g-navigation');
        const sidebar = document.querySelector('.view-form #g-sidebar');
        if (headerNav && sidebar) {
            document.querySelector('.view-form #g-sidebar').style.top = headerNav.offsetHeight + 'px';
        }

        // Remove applicant-form class if needed
        const applicantFormClass = document.querySelector('div.applicant-form');
        if (applicantFormClass) {
            applicantFormClass.classList.remove('applicant-form');
        }

        // Load skeleton
        let header = document.querySelector('.page-header');
        if (header) {
            document.querySelector('.page-header h2').style.opacity = 0;
            header.classList.add('skeleton');
        }
        let intro = document.querySelector('.em-form-intro');
        if (intro) {
            let content = document.querySelector('.em-form-intro').children;
            if (content.length > 0) {
                for (const child of content) {
                    child.style.opacity = 0;
                }
            }
            intro.classList.add('skeleton');
        }
        let grouptitle = document.querySelectorAll('.fabrikGroup .legend');
        for (title of grouptitle) {
            title.style.opacity = 0;
        }
        grouptitle = document.querySelectorAll('.fabrikGroup h2, .fabrikGroup h3');
        for (title of grouptitle){
            title.style.opacity = 0;
        }
        let groupintros = document.querySelectorAll('.groupintro');
        if (groupintros) {
            groupintros.forEach((groupintro) => {
                groupintro.style.opacity = 0;
            });
        }

        let elements = document.querySelectorAll('.fabrikGroup .row-fluid');
        let elements_fields = document.querySelectorAll('.fabrikElementContainer');
        for (field of elements_fields) {
            field.style.opacity = 0;
        }
        for (elt of elements) {
            let elt_container = elt.querySelector('.fabrikElementContainer');
            if (elt_container !== null && !elt_container.classList.contains('fabrikHide')) {
                elt.style.marginTop = '24px';
            }
            elt.classList.add('skeleton');
        }
    });

    let displayTchoozy = getComputedStyle(document.documentElement).getPropertyValue('--display-profiles');
    if (displayTchoozy !== 'block') {
        document.querySelector('#background-shapes').style.display = 'none';
    }
</script>
