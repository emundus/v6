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

// No direct access
defined('_JEXEC') or die('Restricted access');

$form  = $this->form;
$model = $this->getModel();
$notes = $this->params->get('note', '');


require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

?>

<div class="mod_emundus_campaign__grid em-mb-64<?php if (!preg_match('/show_details_content/', $notes, $matches)) : echo ' mod_emundus_campaign__flex'; endif;?>" style="grid-gap: 64px;">
    <div>
        <div class="em-flex-row em-flex-space-between em-w-100 em-mb-12 em-pointer">
            <button type="button" class="em-flex-row" onclick="window.location.href = document.referrer"><span class="material-icons em-neutral-700-color">arrow_back</span><span class="em-ml-8 em-text-neutral-900"><?php echo JText::_('MOD_EM_CAMPAIGN_BACK'); ?></span></button>
            <div class="flex">
                <?php echo $this->loadTemplate('buttons');?>
            </div>
        </div>

        <div class="em-grid-small em-mt-8 em-mt-8">
            <?php foreach ($this->groups as $group) :
	            $this->group = $group;

	            $this->elements = $group->elements;
	            echo $this->loadTemplate($group->tmpl);

              endforeach;
            ?>

        </div>
    </div>

  <?php if (preg_match('/show_details_content/', $notes, $matches)) :?>
    <div>
        <div class="mod_emundus_campaign__details_content em-border-neutral-300 em-mb-24">
            <div id="background-shapes" alt="<?= JText::_('MOD_EM_CAMPAIGN_IFRAME') ?>"></div>
            <h4 class="em-mb-24"><?php echo JText::_('MOD_EM_CAMPAIGN_DETAILS_APPLY') ?></h4>
            <button class="btn btn-primary em-w-100 em-applicant-default-font" onclick=window.location="<?php if (preg_match('/apply_url="(.*)"/', $notes, $matches)) :  echo $matches[1]; endif;?>" data-toggle="sc-modal"><?php echo JText::_('COM_FABRIK_EDITLABEL'); ?></button>
        </div>
    </div>
    <?php endif; ?>
</div>

<div id="fabrikDetailsContainer_<?php echo $form->id ?>">

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
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.title = "<?php echo $form->label; ?>";
    });
</script>
