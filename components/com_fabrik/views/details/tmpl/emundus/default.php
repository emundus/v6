<?php
/**
 * Bootstrap Details Template
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */
use Joomla\CMS\Factory;

// No direct access
defined('_JEXEC') or die('Restricted access');

$form  = $this->form;
$model = $this->getModel();
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
require_once JPATH_SITE . '/components/com_emundus/models/application.php';
$m_application = new EmundusModelApplication();

$fnum = Factory::getApplication()->input->getString('fnum','');
$this->collaborators = $m_application->getSharedFileUsers(null, $fnum);
$this->collaborator = false;
$e_user = Factory::getSession()->get('emundusUser', null);
if(!empty($e_user->fnums)) {
    $fnumInfos = $e_user->fnums[$fnum];
    $this->collaborator = $fnumInfos->applicant_id != $e_user->id;
}

if ($this->params->get('show_page_heading', 1)) : ?>
    <div class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
		<?php echo $this->escape($this->params->get('page_heading')); ?>
    </div>
<?php
endif;

?>

<div id="fabrikDetailsContainer_<?php echo $form->id ?>" <?php if ($form->db_table_name == 'jos_emundus_users') : ?>class="p-4"<?php endif; ?>>

    <?php if ($form->db_table_name == 'jos_emundus_users' && !empty($model->data['jos_emundus_users___user_id_raw']) && !EmundusHelperAccess::isDataAnonymized(JFactory::getUser()->id)) : ?>
        <?php
	    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
	    $m_user = new EmundusModelUsers;
        $applicant = $m_user->getUserById($model->data['jos_emundus_users___user_id_raw'])[0];
        ?>
        <div class="em-flex-row em-mt-16">
                <div class="em-flex-row em-small-flex-column em-small-align-items-start">
                    <div class="em-profile-picture-big no-hover"
					    <?php if(empty($applicant->profile_picture)) :?>
                            style="background-image:url(<?php echo JURI::base() ?>/media/com_emundus/images/profile/default-profile.jpg)"
					    <?php else : ?>
                            style="background-image:url(<?php echo JURI::base() ?>/<?php echo $applicant->profile_picture ?>)"
					    <?php endif; ?>
                    >
                    </div>
                </div>
                <div class="em-ml-24 ">
                    <p class="em-font-weight-500">
					    <?php echo $applicant->lastname . ' ' . $applicant->firstname; ?>
                    </p>
                </div>
            </div>
    <?php endif; ?>

	<?php if ($this->params->get('show-title', 1)) : ?>
        <div class="page-header em-mb-12 em-flex-row em-flex-space-between">
            <h1><?php echo $form->label; ?></h1>
        </div>
	<?php
	endif;

	echo $form->intro;
	if ($this->isMambot) :
		echo '<div class="fabrikForm fabrikDetails fabrikIsMambot" id="' . $form->formid . '">';
	else :
		echo '<div class="fabrikForm fabrikDetails" id="' . $form->formid . '">';
	endif;
	echo $this->plugintop;
	echo $this->loadTemplate('buttons');
	echo $this->loadTemplate('relateddata');
	foreach ($this->groups as $group) :
		$this->group = $group;
		?>

        <div class="em-mt-16 <?php echo $group->class; ?>" id="group<?php echo $group->id; ?>"
             style="<?php echo $group->css; ?>">

			<?php
			if ($group->showLegend) :?>
                <h3 class="legend em-mb-8">
                    <span><?php echo $group->title; ?></span>
                </h3>
			<?php endif;

			if (!empty($group->intro)) : ?>
                <div class="groupintro"><?php echo $group->intro ?></div>
			<?php
			endif;

			// Load the group template - this can be :
			//  * default_group.php - standard group non-repeating rendered as an unordered list
			//  * default_repeatgroup.php - repeat group rendered as an unordered list
			//  * default_repeatgroup_table.php - repeat group rendered in a table.

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

	echo $this->pluginbottom;
	echo $this->loadTemplate('actions');
	echo '</div>';
	echo $form->outro;
	echo $this->pluginend; ?>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.title = "<?php echo $form->label; ?>";
    });
</script>
