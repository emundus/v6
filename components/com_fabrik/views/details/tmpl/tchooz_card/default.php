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

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

?>

<div class="mod_emundus_campaign__grid em-mt-24 em-mb-64" style="grid-gap: 64px;">
    <div>
        <div class="em-flex-row em-flex-space-between em-w-100 em-mb-12 em-pointer" onclick="history.go(-1)">
            <div class="em-flex-row"><span class="material-icons em-neutral-700-color">arrow_back</span><span class="em-ml-8 em-text-neutral-900"><?php echo JText::_('MOD_EM_CAMPAIGN_BACK'); ?></span></div>
          <?php  if ($this->showPrint):
	            echo $this->printLink;
             endif;?>
        </div>

            <p class="em-programme-tag catalogue_tag" title="<?php echo $form->label; ?>">
				<?php  echo $form->label; ?>
            </p>
        <h1 class="mod_emundus_campaign__campaign_title em-mt-16" style="max-height: unset"><?php echo $form->label; ?></h1>
        <div class="em-grid-small em-mt-8 em-mt-8">
            <?php foreach ($this->groups as $group) :

	            $this->elements = $group->elements;
                  foreach ($this->elements as $element) :

                       $this->element = $element;
                       $style = $element->hidden ? 'style="display:none"' : '';
            ?>
                <div class="em-flex-row" <?php echo $style;?>>
                   <p class="em-text-neutral-600 em-flex-row em-applicant-default-font em-mr-4"><span class="material-icons em-mr-8">alarm</span><?php echo $element->label; ?>&nbsp;:</p>
                    <?php echo $element->element; ?>
                </div>

            <?php endforeach;  endforeach; ?>

        </div>


        <div class="mod_emundus_campaign__tabs em-flex-row">
            <a class="em-applicant-text-color current-tab em-mr-24" onclick="displayTab('campaign')" id="campaign_tab">
                <span><?php echo $form->label; ?></span>
            </a>
        </div>

        <div class="g-block size-100 tchooz-single-campaign">
            <div class="single-campaign" id="campaign">
                <div class="em-mt-16 em-w-100">
                    <span><?php echo $form->label; ?></span>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="mod_emundus_campaign__details_content em-border-neutral-300 em-mb-24">
            <div id="background-shapes" alt="<?= JText::_('MOD_EM_CAMPAIGN_IFRAME') ?>"></div>
            <h4 class="em-mb-24"><?php echo JText::_('MOD_EM_CAMPAIGN_DETAILS_APPLY') ?></h4>
            <a class="btn btn-primary em-w-100 em-applicant-default-font" role="button" href='<?php echo $form->label; ?>' data-toggle="sc-modal"><?php echo JText::_('MOD_EM_CAMPAIGN_CAMPAIGN_APPLY_NOW'); ?></a>
        </div>
    </div>
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

	<?php if ($this->params->get('show-title', 1)) : ?>

        <div class="page-header em-mb-12 em-flex-row em-flex-space-between">
            <h1><?php echo $form->label; ?></h1>
        </div>
	<?php
	endif; ?>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.title = "<?php echo $form->label; ?>";
    });
</script>
