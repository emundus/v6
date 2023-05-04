<?php
/**
 * @package     Joomla.Site
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;

$config      = JFactory::getConfig();
$site_offset = $config->get('offset');

$dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
$dateTime = $dateTime->setTimezone(new DateTimeZone($site_offset));
$now      = $dateTime->format('Y-m-d H:i:s');

$order_by_session = JFactory::getSession()->get('applications_order_by');

$tmp_applications = $applications;
foreach ($applications as $key => $application) {
	if ($application->published == '1' || ($show_remove_files == 1 && $application->published == '-1') || ($show_archive_files == 1 && $application->published == '0')) {
		continue;
	}
	else {
		unset($tmp_applications[$key]);
	}
}

$applications   = [];
$status_group   = [];
$missing_status = [];

if (!empty($groups) && !empty($tmp_applications)) {
	foreach ($groups as $key => $group) {
		$status_to_check = explode(',', $group->mod_em_application_group_status);
		foreach ($status_to_check as $step) {
			$status_group[] = $step;
		}
	}

	foreach ($status as $step) {
		if (!in_array($step['step'], $status_group)) {
			$missing_status[] = $step['step'];
		}
	}
	if (!empty($missing_status)) {
		$groups->{'mod_em_application_group' . sizeof($groups)}                                      = new stdClass();
		$groups->{'mod_em_application_group' . sizeof($groups)}->{'mod_em_application_group_status'} = implode(',', $missing_status);
		$groups->{'mod_em_application_group' . sizeof($groups)}->{'mod_em_application_group_title'}  = $title_other_section;
	}

	foreach ($groups as $key => $group) {
		$applications[$key]['applications'] = array_filter($tmp_applications, function ($application) use ($group) {
			$status_to_check = explode(',', $group->mod_em_application_group_status);

			return in_array($application->status, $status_to_check) !== false;
		});
		$applications[$key]['label']        = $group->mod_em_application_group_title;
	}
}
elseif (!empty($tmp_applications)) {
	foreach ($tmp_applications as $tmp_application) {
		switch ($order_by_session) {
			case 'status':
				if (!empty($tmp_application->tab_id)) {
					$applications[$tmp_application->tab_id]['all']['applications'][$tmp_application->value][] = $tmp_application;
				}
				$applications[0]['all']['applications'][$tmp_application->value][] = $tmp_application;
				break;
			case 'campaigns':
				if (!empty($tmp_application->tab_id)) {
					$applications[$tmp_application->tab_id]['all']['applications'][$tmp_application->label][] = $tmp_application;
				}
				$applications[0]['all']['applications'][$tmp_application->label][] = $tmp_application;
				break;
			case 'programs':
				if (!empty($tmp_application->tab_id)) {
					$applications[$tmp_application->tab_id]['all']['applications'][$tmp_application->programme][] = $tmp_application;
				}
				$applications[0]['all']['applications'][$tmp_application->programme][] = $tmp_application;
				break;
			case 'years':
				if (!empty($tmp_application->tab_id)) {
					$applications[$tmp_application->tab_id]['all']['applications'][$tmp_application->year][] = $tmp_application;
				}
				$applications[0]['all']['applications'][$tmp_application->year][] = $tmp_application;
				break;
			default:
				if (!empty($tmp_application->tab_id)) {
					$applications[$tmp_application->tab_id]['all']['applications'][0][] = $tmp_application;
				}
				$applications[0]['all']['applications'][0][] = $tmp_application;
				break;
		}
	}
}

array_unshift($tabs, [
	'id'       => 0,
	'name'     => 'MOD_EM_APPLICATION_FILES_ALL',
	'ordering' => 0,
	'no_files' => count($tmp_applications)
]);

ksort($applications);

$current_tab = 0;
?>
<div class="mod_emundus_applications___header mod_emundus_applications___tmp_tchooz">
	<?php if ($mod_em_applications_show_hello_text == 1) : ?>
        <div class="em-flex-row em-flex-space-between em-w-100 em-mb-16">
            <h1 class="em-h3 em-mb-8"><?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_HELLO') . $user->firstname ?></h1>
			<?php if (sizeof($applications) > 0) : ?>
                <div class="em-flex-row em-w-auto">
					<?php if ($show_add_application && ($position_add_application == 3 || $position_add_application == 4) && $applicant_can_renew) : ?>
                        <a id="add-application" class="btn btn-success" href="<?= $cc_list_url; ?>">
                            <span> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span>
                        </a>
					<?php endif; ?>
					<?php if ($show_show_campaigns) : ?>
                        <a id="add-application" class="btn btn-success em-ml-8" href="<?= $campaigns_list_url; ?>">
                            <span> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_SHOW_CAMPAIGNS'); ?></span>
                        </a>
					<?php endif; ?>
                </div>
			<?php endif; ?>
        </div>
	<?php endif; ?>

	<?php if (sizeof($applications) > 0 && $mod_em_applications_show_hello_text != 1) : ?>
        <div class="em-flex-column em-flex-align-start">
			<?php if ($show_add_application && ($position_add_application == 3 || $position_add_application == 4) && $applicant_can_renew) : ?>
                <a id="add-application" class="btn btn-success em-mb-8" style="width: 40%" href="<?= $cc_list_url; ?>">
                    <span> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span>
                </a>
			<?php endif; ?>
			<?php if ($show_show_campaigns) : ?>
                <a id="add-application" class="btn btn-success em-mt-8 em-mb-8" style="width: 40%"
                   href="<?= $campaigns_list_url; ?>">
                    <span> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_SHOW_CAMPAIGNS'); ?></span>
                </a>
			<?php endif; ?>
        </div>
	<?php endif; ?>

	<?php if (sizeof($applications) > 0) : ?>
        <span class="em-text-neutral-500"><?php echo $description; ?></span>

		<?php if ($show_add_application && ($position_add_application == 0 || $position_add_application == 2) && $applicant_can_renew) : ?>
            <a id="add-application" class="btn btn-success em-mt-32" style="width: auto" href="<?= $cc_list_url; ?>">
                <span> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span>
            </a>
            <hr>
		<?php endif; ?>
	<?php endif; ?>
</div>

<?php if ($show_tabs == 1) : ?>
    <div class="em-mt-32 em-flex-row em-border-bottom-neutral-300" style="height: 50px; overflow:hidden; overflow-x: auto;">
		<?php foreach ($tabs as $tab) : ?>
            <div id="tab_link_<?php echo $tab['id'] ?>" onclick="updateTab(<?php echo $tab['id'] ?>)"
                 class="em-mr-16 em-flex-row em-light-tabs em-pointer <?php if ($current_tab == $tab['id']) : ?>em-light-selected-tab<?php endif; ?>">
                <p class="em-font-size-14 em-text-neutral-600"
                   style="white-space: nowrap"><?php echo JText::_($tab['name']) ?></p>
				<?php if ($tab['id'] != 0) : ?>
                    <span class="mod_emundus_applications_badge"><?php echo $tab['no_files'] ?></span>
				<?php endif; ?>
            </div>
		<?php endforeach; ?>
        <div id="tab_adding_link" onclick="createTab()"
             class="em-mr-16 em-light-tabs em-flex-row em-pointer <?php if (count($tabs) > 1) : ?>em-display-none<?php endif; ?>">
            <a class="em-flex-row em-no-hover-underline em-font-size-14 em-pointer" style="white-space: nowrap"><span
                        class="material-icons-outlined em-font-size-14 em-mr-4">add</span><?php echo JText::_('MOD_EM_APPLICATION_TABS_ADD_TAB') ?>
            </a>
        </div>
        <div id="tab_manage_links" onclick="manageTabs()"
             class="em-mr-16 em-light-tabs em-flex-row em-pointer <?php if (count($tabs) == 1) : ?>em-display-none<?php endif; ?>">
            <a class="em-flex-row em-no-hover-underline em-font-size-14 em-pointer"
               style="white-space: nowrap"><?php echo JText::_('MOD_EM_APPLICATION_TABS_MANAGE_TABS') ?></a>
        </div>
    </div>
<?php endif; ?>

<div class="em-flex-row em-flex-space-between em-mt-16">
    <div class="em-flex-row">
        <!-- BUTTONS -->
		<?php if ($mod_em_applications_show_sort == 1) : ?>
            <div id="mod_emundus_application__header_sort"
                 class="mod_emundus_application__header_filter em-border-neutral-400 em-white-bg em-neutral-800-color em-pointer em-mr-8"
                 onclick="displaySort()">
                <span class="material-icons-outlined">swap_vert</span>
                <span class="em-ml-8"><?php echo JText::_('MOD_EM_APPPLICATION_LIST_SORT') ?></span>
            </div>
		<?php endif; ?>

		<?php if ($mod_em_applications_show_filters == 1) : ?>
            <!--            <div id="mod_emundus_application__header_filter" class="mod_emundus_application__header_filter em-border-neutral-400 em-white-bg em-neutral-800-color em-pointer em-mr-8" onclick="displayFilters()">
                <span class="material-icons-outlined">filter_list</span>
                <span class="em-ml-8"><?php /*echo JText::_('MOD_EM_APPPLICATION_LIST_FILTER') */ ?></span>
                <span id="mod_emundus_campaign__header_filter_count" class="mod_emundus_campaign__header_filter_count em-mr-8"></span>
            </div>-->
		<?php endif; ?>

        <!-- CURRENT SORT -->
        <?php if(!empty($order_by_session)) : ?>
            <div id="mod_emundus_application__header_sort"
                 class="mod_emundus_application__header_filter em-border-neutral-400 em-bg-neutral-200 em-neutral-800-color em-mr-8 em-flex-space-between" style="height: 38px">
                <span>
                    <?php if($order_by_session == 'status') : ?>
                        <?php echo JText::_('MOD_EM_APPLICATION_LIST_FILTER_GROUP_BY_STATUS') ?>
                    <?php elseif ($order_by_session == 'campaigns') : ?>
                        <?php echo JText::_('MOD_EM_APPLICATION_LIST_FILTER_GROUP_BY_CAMPAIGN') ?>
                    <?php elseif ($order_by_session == 'programs') : ?>
                        <?php echo JText::_('MOD_EM_APPLICATION_LIST_FILTER_GROUP_BY_PROGRAMS') ?>
                    <?php elseif ($order_by_session == 'last_update') : ?>
                        <?php echo JText::_('MOD_EM_APPLICATION_LIST_FILTER_GROUP_BY_LAST_UPDATE') ?>
                    <?php elseif ($order_by_session == 'years') : ?>
	                    <?php echo JText::_('MOD_EM_APPLICATION_LIST_FILTER_GROUP_BY_YEARS') ?>
                    <?php endif; ?>
                </span>
                <span class="material-icons-outlined em-pointer em-ml-8 em-font-size-16" onclick="filterApplications('applications_order_by','')">close</span>
            </div>
        <?php endif; ?>
    </div>

    <div class="em-flex-row-justify-end" style="gap: 24px">
		<?php if ($mod_em_applications_show_search && sizeof($applications) > 0): ?>
            <div class="em-searchbar em-flex-row-justify-end">
                <label for="searchword" style="display: inline-block;margin-bottom: unset">
                    <input name="searchword" type="text" id="applications_searchbar" class="form-control"
                           placeholder="<?php echo JText::_('MOD_EM_APPLICATIONS_SEARCH') ?>">
                </label>
            </div>
		<?php endif; ?>
        <div class="em-flex-row" style="gap: 8px">
            <div id="button_switch_card"
                 class="em-pointer mod_emundus_application___buttons_switch_view mod_emundus_application___buttons_enable"
                 onclick="updateView('card')">
                <span class="material-icons-outlined mod_emundus_application___buttons_switch_view_enable">grid_view</span>
            </div>
            <div id="button_switch_list" class="em-pointer mod_emundus_application___buttons_switch_view"
                 onclick="updateView('list')">
                <span class="material-icons-outlined mod_emundus_application___buttons_switch_view_disabled">menu</span>
            </div>
        </div>
    </div>
</div>

<!-- SORT BLOCK -->
<div class="mod_emundus_application__header_sort__values em-border-neutral-400 em-neutral-800-color" id="sort_block"
     style="display: none">
    <a onclick="filterApplications('applications_order_by','status')" class="em-text-neutral-900 em-pointer">
		<?php echo JText::_('MOD_EM_APPLICATION_LIST_FILTER_GROUP_BY_STATUS') ?>
    </a>
    <a onclick="filterApplications('applications_order_by','campaigns')" class="em-text-neutral-900 em-pointer">
		<?php echo JText::_('MOD_EM_APPLICATION_LIST_FILTER_GROUP_BY_CAMPAIGN') ?>
    </a>
    <a onclick="filterApplications('applications_order_by','programs')" class="em-text-neutral-900 em-pointer">
		<?php echo JText::_('MOD_EM_APPLICATION_LIST_FILTER_GROUP_BY_PROGRAMS') ?>
    </a>
    <a onclick="filterApplications('applications_order_by','years')" class="em-text-neutral-900 em-pointer">
		<?php echo JText::_('MOD_EM_APPLICATION_LIST_FILTER_GROUP_BY_YEARS') ?>
    </a>
    <a onclick="filterApplications('applications_order_by','last_update')" class="em-text-neutral-900 em-pointer">
		<?php echo JText::_('MOD_EM_APPLICATION_LIST_FILTER_GROUP_BY_LAST_UPDATE') ?>
    </a>
</div>



<div class="em-mt-32" id="applications_card_view">
	<?php if (sizeof($applications) == 0) : ?>
        <hr>
        <div class="mod_emundus_applications__list_content--default">
            <p class="em-text-neutral-900 em-h5 em-applicant-title-font"><?php echo JText::_('MOD_EM_APPLICATIONS_NO_FILE') ?></p>
            <br/>
            <p class="em-text-neutral-900 em-default-font em-font-weight-500 em-mb-4"><?php echo JText::_('MOD_EM_APPLICATIONS_NO_FILE_TEXT') ?></p>
            <p class="em-applicant-text-color em-default-font"><?php echo JText::_('MOD_EM_APPLICATIONS_NO_FILE_TEXT_2') ?></p>
            <br/>
            <div class="em-flex-row-justify-end mod_emundus_campaign__buttons em-mt-32">
				<?php if ($show_show_campaigns) : ?>
                    <a id="add-application"
                       class="em-secondary-button em-w-auto em-default-font em-applicant-border-radius"
                       style="width: auto" href="<?= $campaigns_list_url; ?>">
                        <span> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_SHOW_CAMPAIGNS'); ?></span>
                    </a>
				<?php endif; ?>
				<?php if ($show_add_application && $applicant_can_renew) : ?>
                    <a id="add-application"
                       class="em-applicant-primary-button em-w-auto em-ml-8 em-default-font em-applicant-border-radius"
                       style="width: auto" href="<?= $cc_list_url; ?>">
                        <span> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span>
                    </a>
				<?php endif; ?>
            </div>
        </div>
	<?php else : ?>
		<?php foreach ($applications as $key => $group) : ?>
			<?php foreach ($group as $g_key => $sub_group) : ?>
				<?php if (sizeof($sub_group['applications']) > 0) : ?>
                    <div id="group_application_tab_<?php echo $key ?>"
                         class="em-mb-44 <?php if ($key != $current_tab) : ?>em-display-none<?php endif; ?>">
						<?php foreach ($sub_group['applications'] as $f_key => $files) : ?>
							<?php if (!is_integer($f_key) || $order_by_session == 'years') : ?>
                                <p class="em-h5 em-ml-8"><?php echo $f_key ?></p>
                                <hr/>
							<?php endif; ?>
                            <div class="<?= $moduleclass_sfx ?> mod_emundus_applications___content em-mb-32">
								<?php foreach ($files as $application) : ?>

                            <?php
	                        $is_admission = false;
                            if(!empty($admission_status)) {
	                            $is_admission = in_array($application->status, $admission_status);
                            }
                            $display_app = true;
                            if(!empty($show_status) && !in_array($application->status, $show_status)) {
                                $display_app = false;
                            }

									if ($display_app) {
										$state          = $application->published;
										$confirm_url    = (($absolute_urls === 1) ? '/' : '') . 'index.php?option=com_emundus&task=openfile&fnum=' . $application->fnum . '&confirm=1';
										$first_page_url = (($absolute_urls === 1) ? '/' : '') . 'index.php?option=com_emundus&task=openfile&fnum=' . $application->fnum;
										if ($state == '1' || $show_remove_files == 1 && $state == '-1' || $show_archive_files == 1 && $state == '0') : ?>
											<?php
											if ($file_tags != '') {

												$post = array(
													'APPLICANT_ID'   => $user->id,
													'DEADLINE'       => strftime("%A %d %B %Y %H:%M", strtotime($application->end_date)),
													'CAMPAIGN_LABEL' => $application->label,
													'CAMPAIGN_YEAR'  => $application->year,
													'CAMPAIGN_START' => $application->start_date,
													'CAMPAIGN_END'   => $application->end_date,
													'CAMPAIGN_CODE'  => $application->training,
													'FNUM'           => $application->fnum
												);

												$tags              = $m_email->setTags($user->id, $post, $application->fnum, '', $file_tags);
												$file_tags_display = preg_replace($tags['patterns'], $tags['replacements'], $file_tags);
												$file_tags_display = $m_email->setTagsFabrik($file_tags_display, array($application->fnum));
											}

											$current_phase = $m_campaign->getCurrentCampaignWorkflow($application->fnum);

											?>
                                            <div class="row em-border-neutral-300 mod_emundus_applications___content_app em-pointer"
                                                 id="application_content<?php echo $application->fnum ?>"
                                                 onclick="openFile(event,'<?php echo $first_page_url ?>')">
                                                <div class="em-w-100">
                                                    <div class="em-flex-row em-flex-space-between em-mb-12">
                                                        <div>
															<?php
															if (empty($application->class)) {
																$application->class = 'default';
															}
															?>
															<?php if (empty($visible_status)) : ?>
                                                                <div class="mod_emundus_applications___status_<?= $application->class; ?> em-flex-row"
                                                                     id="application_status_<?php echo $application->fnum ?>">
                                                                    <span class="mod_emundus_applications___circle em-mr-8 label-<?= $application->class; ?>-500"></span>
                                                                    <span class="mod_emundus_applications___status_label em-neutral-800-color em-applicant-default-font em-font-size-14"><?= $application->value; ?></span>
                                                                </div>
															<?php elseif (in_array($application->status, $visible_status)) : ?>
                                                                <div class="mod_emundus_applications___status_<?= $application->class; ?> em-flex-row"
                                                                     id="application_status_<?php echo $application->fnum ?>">
                                                                    <span class="mod_emundus_applications___circle em-mr-8 label-<?= $application->class; ?>-500"></span>
                                                                    <span class="mod_emundus_applications___status_label em-font-size-14"><?= $application->value; ?></span>
                                                                </div>
															<?php endif; ?>
															<?php if (!empty($application->order_status)): ?>
                                                                <br>
                                                                <span class="label"
                                                                      style="background-color: <?= $application->order_color; ?>"><?= JText::_(strtoupper($application->order_status)); ?></span>
															<?php endif; ?>
                                                        </div>
                                                        <div>
                                                            <span class="material-icons em-text-neutral-600 em-font-weight-600"
                                                                  id="actions_button_<?php echo $application->fnum ?>_card_tab<?php echo $key ?>"
                                                                  style="font-size: 16px">more_vert</span>

                                                            <!-- ACTIONS BLOCK -->
                                                            <div class="mod_emundus_applications__actions em-border-neutral-400 em-neutral-800-color"
                                                                 id="actions_block_<?php echo $application->fnum ?>_card_tab<?php echo $key ?>"
                                                                 style="display: none">
                                                                <a class="em-text-neutral-900 em-pointer em-flex-row"
                                                                   href="<?= JRoute::_($first_page_url); ?>"
                                                                   id="actions_block_open_<?php echo $application->fnum ?>_card_tab<?php echo $key ?>">
                                                                    <span class="material-icons-outlined em-font-size-16 em-mr-8">open_in_new</span>
																	<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_OPEN_APPLICATION') ?>
                                                                </a>

																<?php if (in_array('rename', $actions)) : ?>
                                                                    <a class="em-text-neutral-900 em-pointer em-flex-row"
                                                                       onclick="renameApplication('<?php echo $application->fnum ?>','<?php echo $application->name ?>','<?php echo $application->label ?>')"
                                                                       id="actions_button_rename_<?php echo $application->fnum ?>_card_tab<?php echo $key ?>">
                                                                        <span class="material-icons-outlined em-font-size-16 em-mr-8">drive_file_rename_outline</span>
																		<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_RENAME_APPLICATION') ?>
                                                                    </a>
																<?php endif; ?>

																<?php if (!empty($available_campaigns) && in_array('copy', $actions)) : ?>
                                                                    <a class="em-text-neutral-900 em-pointer em-flex-row"
                                                                       onclick="copyApplication('<?php echo $application->fnum ?>')"
                                                                       id="actions_button_copy_<?php echo $application->fnum ?>_card_tab<?php echo $key ?>">
                                                                        <span class="material-icons-outlined em-font-size-16 em-mr-8">file_copy</span>
																		<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_COPY_APPLICATION') ?>
                                                                    </a>
																<?php endif; ?>

																<?php if ($show_tabs == 1) : ?>
                                                                    <a class="em-text-neutral-900 em-pointer em-flex-row"
                                                                       onclick="moveToTab('<?php echo $application->fnum ?>','tab<?php echo $key ?>','card')"
                                                                       id="actions_button_move_<?php echo $application->fnum ?>_card_tab<?php echo $key ?>">
                                                                        <span class="material-icons-outlined em-font-size-16 em-mr-8">drive_file_move</span>
																		<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_MOVE_INTO_TAB') ?>
                                                                    </a>
																<?php endif; ?>

																<?php if (in_array('history', $actions)) : ?>
                                                                    <a class="em-text-neutral-900 em-pointer em-flex-row"
                                                                       href="<?= JRoute::_($first_page_url); ?>"
                                                                       id="actions_button_history_<?php echo $application->fnum ?>_card_tab<?php echo $key ?>">
                                                                        <span class="material-icons-outlined em-font-size-16 em-mr-8">history</span>
																		<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_VIEW_HISTORY') ?>
                                                                    </a>
																<?php endif; ?>

																<?php if (in_array($application->status, $status_for_delete)) : ?>
                                                                    <a class="em-red-500-color em-flex-row em-pointer"
                                                                       onclick="deletefile('<?php echo $application->fnum; ?>');"
                                                                       id="actions_block_delete_<?php echo $application->fnum ?>_card_tab<?php echo $key ?>">
                                                                        <span class="material-icons-outlined em-font-size-16 em-mr-8">delete</span>
																		<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_DELETE_APPLICATION_FILE') ?>
                                                                    </a>
																<?php endif; ?>

																<?php
																foreach ($custom_actions as $custom_action_key => $custom_action) {

																	if (in_array($application->status, $custom_action->mod_em_application_custom_action_status) && !empty($custom_action->mod_em_application_custom_action_link)) {
																		?>
                                                                        <a id="actions_button_custom_<?= $custom_action_key; ?>_card_tab<?php echo $key ?>"
                                                                           class="em-text-neutral-900 em-pointer em-flex-row"
                                                                           href="<?= str_replace('{fnum}', $application->fnum, $custom_action->mod_em_application_custom_action_link) ?>" <?= $custom_action->mod_em_application_custom_action_link_blank ? 'target="_blank"' : '' ?>>
	                                                                        <? if ($custom_action->mod_em_application_custom_action_icon): ?>
                                                                                <span class="material-icons-outlined em-font-size-16 em-mr-8"><?php echo $custom_action->mod_em_application_custom_action_icon ?></span>
	                                                                        <? endif; ?>
                                                                            <?= JText::_($custom_action->mod_em_application_custom_action_label) ?>
                                                                        </a>
																		<?php
																	}
																}
																?>
                                                            </div>
                                                        </div>
                                                    </div>
													<?php if (empty($application->name)) : ?>
                                                        <a href="<?= JRoute::_($first_page_url); ?>"
                                                           class="em-h6 mod_emundus_applications___title"
                                                           id="application_title_<?php echo $application->fnum ?>">
                                                            <span><?= ($is_admission && $add_admission_prefix) ? JText::_('COM_EMUNDUS_INSCRIPTION') . ' - ' . $application->label : $application->label; ?></span>
                                                        </a>
													<?php else : ?>
                                                        <a href="<?= JRoute::_($first_page_url); ?>"
                                                           class="em-h6 mod_emundus_applications___title"
                                                           id="application_title_<?php echo $application->fnum ?>">
                                                            <span><?= $application->name; ?></span>
                                                        </a>
													<?php endif; ?>
													<?php if ($show_fnum) : ?>
                                                        <div class="em-mb-8 em-font-size-14">
                                                            <span class="em-applicant-default-font em-text-neutral-600">N°<?php echo $application->fnum ?></span>
                                                        </div>
													<?php endif; ?>
													<?php if (!empty($file_tags_display)) : ?>
                                                        <div class="em-mt-16">
                                                        <span class="em-tags-display em-applicant-text-color">
                                                            <?= $file_tags_display; ?>
                                                        </span>
                                                        </div>
													<?php endif; ?>
                                                </div>

                                                <div class="em-flex-row">
													<?php if ($mod_emundus_applications_show_end_date == 1) : ?>
														<?php
														$closed          = false;
														$displayInterval = false;
														$end_date        = $application->end_date;
														if (!empty($current_phase)) {
															$end_date = $current_phase->end_date;
														}
														if ($now < $end_date) {
															$interval = date_create($now)->diff(date_create($end_date));
															if ($interval->y == 0 && $interval->m == 0 && $interval->d == 0) {
																$displayInterval = true;
															}
														}
														else {
															$closed = true;
														}
														?>
                                                        <div class="mod_emundus_applications___date em-mt-8">
															<?php if (!$displayInterval && !$closed) : ?>
                                                                <span class="material-icons em-text-neutral-600 em-font-size-16 em-mr-8">schedule</span>
                                                                <p class="em-applicant-text-color em-font-size-16 em-applicant-default-font"> <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_END_DATE'); ?><?php echo JFactory::getDate(new JDate($end_date, $site_offset))->format($date_format); ?></p>
															<?php elseif ($displayInterval && !$closed) : ?>
                                                                <span class="material-icons-outlined em-text-neutral-600 em-font-size-16 em-red-500-color em-mr-8">schedule</span>
                                                                <p class="em-red-500-color"><?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_LAST_DAY'); ?>
																	<?php if ($interval->h > 0) {
																		echo $interval->h . 'h' . $interval->i;
																	}
																	else {
																		echo $interval->i . 'm';
																	} ?>
                                                                </p>
															<?php elseif ($closed) : ?>
                                                                <span class="material-icons em-font-size-16 em-mr-8 em-red-500-color">schedule</span>
                                                                <p class="em-font-size-16 em-applicant-default-font em-red-500-color"> <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_CLOSED'); ?></p>
															<?php endif; ?>
                                                        </div>
													<?php endif; ?>
                                                </div>

                                                <hr/>

                                                <div class="mod_emundus_applications___informations">
                                                    <div>
                                                        <label class="em-applicant-text-color em-applicant-default-font em-font-size-14"><?= JText::_('MOD_EMUNDUS_APPLICATIONS_COMPLETED'); ?>
                                                            :</label>
                                                        <p class="em-applicant-default-font"><?php echo(($progress['forms'][$application->fnum] + $progress['attachments'][$application->fnum]) / 2) ?>
                                                            %</p>
                                                    </div>

													<?php if (!empty($application->updated) || !empty($application->submitted_date)) : ?>
                                                        <div>
                                                            <label class="em-applicant-text-color em-applicant-default-font em-font-size-14"><?= JText::_('MOD_EMUNDUS_APPLICATIONS_LAST_UPDATE'); ?>
                                                                :</label>
                                                            <p class="em-applicant-default-font">
																<?php if (empty($application->updated)) : ?>
																	<?php echo JFactory::getDate(new JDate($application->submitted_date, $site_offset))->format('d/m/Y H:i'); ?>
																<?php else : ?>
																	<?php echo EmundusHelperDate::displayDate($application->updated, 'DATE_FORMAT_LC2', 0); ?>
																<?php endif; ?>
                                                            </p>
                                                        </div>
													<?php endif; ?>
                                                </div>

												<?php if ($show_state_files == 1) : ?>
                                                    <div class="">
                                                        <div class="">
                                                            <strong><?= JText::_('MOD_EMUNDUS_STATE'); ?></strong>
															<?php if ($state == 1) : ?>
                                                                <span class="label alert-success"
                                                                      role="alert"> <?= JText::_('MOD_EMUNDUS_PUBLISH'); ?></span>
															<?php elseif ($state == 0) : ?>
                                                                <span class="label alert-secondary"
                                                                      role="alert"> <?= JText::_('MOD_EMUNDUS_ARCHIVE'); ?></span>
															<?php else : ?>
                                                                <span class="label alert-danger"
                                                                      role="alert"><?= JText::_('MOD_EMUNDUS_DELETE'); ?></span>
															<?php endif; ?>
                                                        </div>
                                                    </div>
												<?php endif; ?>
                                            </div>
										<?php endif; ?>
									<?php } ?>
								<?php endforeach; ?>
                            </div>
						<?php endforeach; ?>
                    </div>
				<?php endif; ?>
			<?php endforeach ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<div class="em-mt-32" id="applications_list_view" style="display: none">
	<?php if (sizeof($applications) == 0) : ?>
        <hr>
        <div class="mod_emundus_applications__list_content--default">
            <p class="em-text-neutral-900 em-h5 em-applicant-title-font"><?php echo JText::_('MOD_EM_APPLICATIONS_NO_FILE') ?></p>
            <br/>
            <p class="em-text-neutral-900 em-default-font em-font-weight-500 em-mb-4"><?php echo JText::_('MOD_EM_APPLICATIONS_NO_FILE_TEXT') ?></p>
            <p class="em-applicant-text-color em-default-font"><?php echo JText::_('MOD_EM_APPLICATIONS_NO_FILE_TEXT_2') ?></p>
            <br/>
            <div class="em-flex-row-justify-end mod_emundus_campaign__buttons em-mt-32">
				<?php if ($show_show_campaigns) : ?>
                    <a id="add-application"
                       class="em-secondary-button em-w-auto em-default-font em-applicant-border-radius"
                       style="width: auto" href="<?= $campaigns_list_url; ?>">
                        <span> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_SHOW_CAMPAIGNS'); ?></span>
                    </a>
				<?php endif; ?>
				<?php if ($show_add_application && $applicant_can_renew) : ?>
                    <a id="add-application"
                       class="em-applicant-primary-button em-w-auto em-ml-8 em-default-font em-applicant-border-radius"
                       style="width: auto" href="<?= $cc_list_url; ?>">
                        <span> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span>
                    </a>
				<?php endif; ?>
            </div>
        </div>
	<?php else : ?>
		<?php foreach ($applications as $key => $group) : ?>
			<?php foreach ($group as $g_key => $sub_group) : ?>
				<?php if (sizeof($sub_group['applications']) > 0) : ?>
                    <div id="group_application_tab_<?php echo $key ?>"
                         class="em-mb-44 <?php if ($key != $current_tab) : ?>em-display-none<?php endif; ?>">

                        <table class="em-mb-12">
                            <thead>
                            <tr>
                                <th></th>
                                <th style="width: 23.75%;"><?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_RENAME_APPLICATION_NAME') ?></th>
                                <th style="width: 23.75%;"><?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_LAST_UPDATE') ?></th>
                                <th style="width: 23.75%;"><?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_COMPLETED') ?></th>
                                <th style="width: 23.75%;"><?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_STATUS') ?></th>
                                <th style="width: 5%;"></th>
                            </tr>
                            </thead>
                        </table>
						<?php foreach ($sub_group['applications'] as $f_key => $files) : ?>
							<?php if (!is_integer($f_key) || $order_by_session == 'years') : ?>
                                <div class="em-mt-12 em-flex-row em-white-bg em-applicant-border-radius em-p-6-12">
                                    <span class="material-icons-outlined em-mr-8">expand_more</span>
                                    <h2 style="margin-top: 0" class="em-h6"><?php echo $f_key ?></h2>
                                </div>
							<?php endif; ?>
                            <table class="em-ml-12">
                                <tbody>
								<?php foreach ($files as $application) : ?>

									<?php
									$is_admission = in_array($application->status, $admission_status);
									$display_app  = true;
									if (!empty($show_status) && !in_array($application->status, $show_status)) {
										$display_app = false;
									}

									if ($display_app) {
										$state          = $application->published;
										$confirm_url    = (($absolute_urls === 1) ? '/' : '') . 'index.php?option=com_emundus&task=openfile&fnum=' . $application->fnum . '&confirm=1';
										$first_page_url = (($absolute_urls === 1) ? '/' : '') . 'index.php?option=com_emundus&task=openfile&fnum=' . $application->fnum;
										if ($state == '1' || $show_remove_files == 1 && $state == '-1' || $show_archive_files == 1 && $state == '0') : ?>
											<?php
											if ($file_tags != '') {

												$post = array(
													'APPLICANT_ID'   => $user->id,
													'DEADLINE'       => strftime("%A %d %B %Y %H:%M", strtotime($application->end_date)),
													'CAMPAIGN_LABEL' => $application->label,
													'CAMPAIGN_YEAR'  => $application->year,
													'CAMPAIGN_START' => $application->start_date,
													'CAMPAIGN_END'   => $application->end_date,
													'CAMPAIGN_CODE'  => $application->training,
													'FNUM'           => $application->fnum
												);

												$tags              = $m_email->setTags($user->id, $post, $application->fnum, '', $file_tags);
												$file_tags_display = preg_replace($tags['patterns'], $tags['replacements'], $file_tags);
												$file_tags_display = $m_email->setTagsFabrik($file_tags_display, array($application->fnum));
											}

											$current_phase = $m_campaign->getCurrentCampaignWorkflow($application->fnum);

											?>
                                            <tr class="em-pointer"
                                                id="application_content<?php echo $application->fnum ?>"
                                                onclick="openFile(event,'<?php echo $first_page_url ?>')">
                                                <td style="width: 23.75%;">
													<?php if (empty($application->name)) : ?>
                                                        <a href="<?= JRoute::_($first_page_url); ?>"
                                                           class="mod_emundus_applications___title em-font-size-14"
                                                           id="application_title_<?php echo $application->fnum ?>">
                                                            <span><?= ($is_admission && $add_admission_prefix) ? JText::_('COM_EMUNDUS_INSCRIPTION') . ' - ' . $application->label : $application->label; ?></span>
                                                        </a>
													<?php else : ?>
                                                        <a href="<?= JRoute::_($first_page_url); ?>"
                                                           class="mod_emundus_applications___title em-font-size-14"
                                                           id="application_title_<?php echo $application->fnum ?>">
                                                            <span><?= $application->name; ?></span>
                                                        </a>
													<?php endif; ?>
                                                </td>
                                                <td style="width: 23.75%;">
													<?php if (!empty($application->updated) || !empty($application->submitted_date)) : ?>
                                                        <div>
                                                            <p class="em-applicant-default-font em-font-size-14">
																<?php if (empty($application->updated)) : ?>
																	<?php echo JFactory::getDate(new JDate($application->submitted_date, $site_offset))->format('d/m/Y H:i'); ?>
																<?php else : ?>
																	<?php echo EmundusHelperDate::displayDate($application->updated, 'd/m/Y H:i', 0); ?>
																<?php endif; ?>
                                                            </p>
                                                        </div>
													<?php endif; ?>
                                                </td>
                                                <td style="width: 23.75%;">
                                                    <p class="em-applicant-default-font em-font-size-14"><?php echo(($progress['forms'][$application->fnum] + $progress['attachments'][$application->fnum]) / 2) ?>
                                                        %</p>
                                                </td>
                                                <td style="width: 23.75%;">
                                                    <div>
														<?php
														if (empty($application->class)) {
															$application->class = 'default';
														}
														?>
														<?php if (empty($visible_status)) : ?>
                                                            <div class="mod_emundus_applications___status_<?= $application->class; ?> em-flex-row"
                                                                 id="application_status_<?php echo $application->fnum ?>">
                                                                <span class="mod_emundus_applications___circle em-mr-8 label-<?= $application->class; ?>-500"></span>
                                                                <span class="mod_emundus_applications___status_label em-neutral-800-color em-applicant-default-font em-font-size-14"><?= $application->value; ?></span>
                                                            </div>
														<?php elseif (in_array($application->status, $visible_status)) : ?>
                                                            <div class="mod_emundus_applications___status_<?= $application->class; ?> em-flex-row"
                                                                 id="application_status_<?php echo $application->fnum ?>">
                                                                <span class="mod_emundus_applications___circle em-mr-8 label-<?= $application->class; ?>-500"></span>
                                                                <span class="mod_emundus_applications___status_label em-font-size-14"><?= $application->value; ?></span>
                                                            </div>
														<?php endif; ?>
														<?php if (!empty($application->order_status)): ?>
                                                            <br>
                                                            <span class="label"
                                                                  style="background-color: <?= $application->order_color; ?>"><?= JText::_(strtoupper($application->order_status)); ?></span>
														<?php endif; ?>
                                                    </div>
                                                </td>
                                                <td style="width: 5%;">
                                                    <div>
                                                            <span class="material-icons em-text-neutral-600 em-font-weight-600"
                                                                  id="actions_button_<?php echo $application->fnum ?>_list_tab<?php echo $key ?>"
                                                                  style="font-size: 16px">more_vert</span>

                                                        <!-- ACTIONS BLOCK -->
                                                        <div class="mod_emundus_applications__actions em-border-neutral-400 em-neutral-800-color"
                                                             id="actions_block_<?php echo $application->fnum ?>_list_tab<?php echo $key ?>"
                                                             style="display: none">
                                                            <a class="em-text-neutral-900 em-pointer em-flex-row"
                                                               href="<?= JRoute::_($first_page_url); ?>"
                                                               id="actions_block_open_<?php echo $application->fnum ?>_list_tab<?php echo $key ?>">
                                                                <span class="material-icons-outlined em-font-size-16 em-mr-8">open_in_new</span>
																<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_OPEN_APPLICATION') ?>
                                                            </a>

															<?php if (in_array('rename', $actions)) : ?>
                                                                <a class="em-text-neutral-900 em-pointer em-flex-row"
                                                                   onclick="renameApplication('<?php echo $application->fnum ?>','<?php echo $application->name ?>','<?php echo $application->label ?>')"
                                                                   id="actions_button_rename_<?php echo $application->fnum ?>_list_tab<?php echo $key ?>">
                                                                    <span class="material-icons-outlined em-font-size-16 em-mr-8">drive_file_rename_outline</span>
																	<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_RENAME_APPLICATION') ?>
                                                                </a>
															<?php endif; ?>

															<?php if (!empty($available_campaigns) && in_array('copy', $actions)) : ?>
                                                                <a class="em-text-neutral-900 em-pointer em-flex-row"
                                                                   onclick="copyApplication('<?php echo $application->fnum ?>')"
                                                                   id="actions_button_copy_<?php echo $application->fnum ?>_list_tab<?php echo $key ?>">
                                                                    <span class="material-icons-outlined em-font-size-16 em-mr-8">file_copy</span>
																	<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_COPY_APPLICATION') ?>
                                                                </a>
															<?php endif; ?>

															<?php if ($show_tabs == 1) : ?>
                                                                <a class="em-text-neutral-900 em-pointer em-flex-row"
                                                                   onclick="moveToTab('<?php echo $application->fnum ?>','tab<?php echo $key ?>','list')"
                                                                   id="actions_button_move_<?php echo $application->fnum ?>_list_tab<?php echo $key ?>">
                                                                    <span class="material-icons-outlined em-font-size-16 em-mr-8">drive_file_move</span>
																	<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_MOVE_INTO_TAB') ?>
                                                                </a>
															<?php endif; ?>

															<?php if (in_array('history', $actions)) : ?>
                                                                <a class="em-text-neutral-900 em-pointer em-flex-row"
                                                                   href="<?= JRoute::_($first_page_url); ?>"
                                                                   id="actions_button_history_<?php echo $application->fnum ?>_list_tab<?php echo $key ?>">
                                                                    <span class="material-icons-outlined em-font-size-16 em-mr-8">history</span>
																	<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_VIEW_HISTORY') ?>
                                                                </a>
															<?php endif; ?>

															<?php if (in_array($application->status, $status_for_delete)) : ?>
                                                                <a class="em-red-500-color em-flex-row em-pointer"
                                                                   onclick="deletefile('<?php echo $application->fnum; ?>');"
                                                                   id="actions_block_delete_<?php echo $application->fnum ?>_list_tab<?php echo $key ?>">
                                                                    <span class="material-icons-outlined em-font-size-16 em-mr-8">delete</span>
																	<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_DELETE_APPLICATION_FILE') ?>
                                                                </a>
															<?php endif; ?>

															<?php
															foreach ($custom_actions as $custom_action_key => $custom_action) {

																if (in_array($application->status, $custom_action->mod_em_application_custom_action_status) && !empty($custom_action->mod_em_application_custom_action_link)) {
																	?>
                                                                    <a id="actions_button_custom_<?= $custom_action_key; ?>_list_tab<?php echo $key ?>"
                                                                       class="em-text-neutral-900 em-pointer em-flex-row"
                                                                       href="<?= str_replace('{fnum}', $application->fnum, $custom_action->mod_em_application_custom_action_link) ?>" <?= $custom_action->mod_em_application_custom_action_link_blank ? 'target="_blank"' : '' ?>>
                                                                        <? if ($custom_action->mod_em_application_custom_action_icon): ?>
                                                                            <span class="material-icons-outlined em-font-size-16 em-mr-8"><?php echo $custom_action->mod_em_application_custom_action_icon ?></span>
                                                                        <? endif; ?>
																		<?= JText::_($custom_action->mod_em_application_custom_action_label) ?>
                                                                    </a>
																	<?php
																}
															}
															?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
										<?php endif; ?>
									<?php } ?>
								<?php endforeach; ?>
                                </tbody>
                            </table>
						<?php endforeach; ?>
                    </div>
				<?php endif; ?>
			<?php endforeach ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<div style="display: none">
    <div id="swal_manage" class="em-w-100">
        <ul id="items">
        </ul>
    </div>
</div>


<?php if ($show_add_application && ($position_add_application == 1 || $position_add_application == 2 || $position_add_application == 4) && $applicant_can_renew) : ?>
    <div class="mod_emundus_applications___footer">
        <a class="btn btn-success" href="<?= $cc_list_url; ?>"><span
                    class="icon-plus-sign"> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span></a>
    </div>
<?php endif; ?>

<?php if (!empty($filled_poll_id) && !empty($poll_url) && $filled_poll_id == 0 && $poll_url != "") : ?>
    <div class="modal fade" id="em-modal-form" style="z-index:99999" tabindex="-1" role="dialog"
         aria-labelledby="em-modal-form" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-title" id="em-modal-form-title"><?= JText::_('LOADING'); ?></h4>
                    <img src="media/com_emundus/images/icones/loader-line.gif">
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var poll_url = "<?= $poll_url; ?>";
        if ($poll_url !== "") {
            jQuery(".modal-body").html('<iframe src="' + poll_url + '" style="width:' + window.getWidth() * 0.8 + 'px; height:' + window.getHeight() * 0.8 + 'px; border:none"></iframe>');
            setTimeout(function () {
                jQuery('#em-modal-form').modal({backdrop: true, keyboard: true}, 'toggle');
            }, 1000);
        }
    </script>

<?php endif; ?>

<!-- jsDelivr :: Sortable :: Latest (https://www.jsdelivr.com/package/npm/sortablejs) -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script type="text/javascript">
    window.addEventListener('DOMContentLoaded', (event) => {
        let selected_tab_session = sessionStorage.getItem('mod_emundus_applications___selected_tab');
        let selected_view = sessionStorage.getItem('mod_emundus_applications___selected_view');
        if (selected_tab_session !== null) {
            this.updateTab(selected_tab_session);
        }
        if (selected_view !== null) {
            this.updateView(selected_view);
        }
    });

    function deletefile(fnum) {
        Swal.fire({
            title: "<?= JText::_('MOD_EMUNDUS_APPLICATIONS_CONFIRM_DELETE_FILE'); ?>",
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#dc3545",
            reverseButtons: true,
            confirmButtonText: "<?php echo JText::_('JYES');?>",
            cancelButtonText: "<?php echo JText::_('JNO');?>"
        }).then((confirm) => {
            if (confirm.value) {
                document.location.href = "index.php?option=com_emundus&task=deletefile&fnum=" + fnum + "&redirect=<?php echo base64_encode(JUri::getInstance()->getPath()); ?>";
            }
        });
    }

    function delay(callback, ms) {
        var timer = 0;
        return function () {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                callback.apply(context, args);
            }, ms || 0);
        };
    }

    jQuery(function () {
        jQuery('[data-toggle="tooltip"]').tooltip()
    })

    document.addEventListener('click', function (e) {
        let target = e.target.id;
        let actions = document.querySelectorAll("[id^='actions_block_']");
        let modal = document.querySelector('.swal2-container');

        if (typeof actions !== 'undefined') {
            actions.forEach((action) => {
                if (action.style.display === 'flex') {
                    action.style.display = 'none';
                }
            });

            if (target.indexOf('actions_button_') !== -1) {
                let url = target.split('_');
                let fnum = url[url.length - 3];
                let tab = url[url.length - 1];
                let view = url[url.length - 2];

                actions = document.getElementById('actions_block_' + fnum + '_' + view + '_' + tab);
                if (modal !== null) {
                    actions.style.display = 'none';
                } else if (actions.style.display === 'none') {
                    actions.style.display = 'flex';
                } else {
                    actions.style.display = 'none';
                }
            }
        }
    });

    function openFile(e, url) {
        let target = e.target.id;

        if (target.indexOf('actions_button_') !== -1 || target.indexOf('actions_block_delete_') !== -1) {
            //do nothing
        } else {
            window.location.href = url;
        }
    }

    $('#applications_searchbar').keyup(delay(function (e) {
        let search = e.target.value;

        if (search !== '') {
            let campaigns = document.querySelectorAll('.mod_emundus_applications___title span');
            let status = document.querySelectorAll('.mod_emundus_applications___status_label');
            let fnums_to_hide = [];
            let fnums_to_show = [];

            for (let campaign of campaigns) {
                let fnum = campaign.parentElement.id.split('_');
                fnum = fnum[fnum.length - 1];

                if (campaign.textContent.normalize('NFD').replace(/\p{Diacritic}/gu, "").toLowerCase().includes(search.normalize('NFD').replace(/\p{Diacritic}/gu, "").toLowerCase()) === false) {
                    fnums_to_hide.push(fnum);
                } else {
                    fnums_to_show.push(fnum);
                }
            }

            for (let step of status) {
                let fnum = step.parentElement.id.split('_');
                fnum = fnum[fnum.length - 1];

                if (step.textContent.normalize('NFD').replace(/\p{Diacritic}/gu, "").toLowerCase().includes(search.normalize('NFD').replace(/\p{Diacritic}/gu, "").toLowerCase()) === false) {
                    if (fnums_to_show.indexOf(fnum) !== -1) {
                        fnums_to_hide.push(fnum);
                    }
                } else {
                    fnums_to_show.push(fnum);
                    if (fnums_to_hide.indexOf(fnum) !== -1) {
                        fnums_to_hide.splice(fnums_to_hide.indexOf(fnum), 1);
                    }
                }
            }

            fnums_to_hide.forEach((fnum) => {
                document.querySelectorAll('#application_content' + fnum).forEach((block) => {
                    block.style.display = 'none';
                })
            })
            fnums_to_show.forEach((fnum) => {
                document.querySelectorAll('#application_content' + fnum).forEach((block) => {
                    if(block.nodeName === 'TR'){
                        block.style.display = 'flex';
                    } else {
                        block.style.display = 'block';
                    }
                })
            })
        } else {
            for (let application of document.querySelectorAll("div[id^='application_content']")) {
                if(application.nodeName === 'TR'){
                    application.style.display = 'flex';
                } else {
                    application.style.display = 'block';
                }
            }

        }

    }, 500));


    /** VIEWS **/
    function updateView(view) {
        sessionStorage.setItem("mod_emundus_applications___selected_view", view);
        if (view === 'list') {
            document.querySelector('#applications_card_view').style.display = 'none';
            document.querySelector('#applications_list_view').style.display = 'block';
            document.querySelector('#button_switch_card').classList.remove('mod_emundus_application___buttons_enable');
            document.querySelector('#button_switch_card span').classList.remove('mod_emundus_application___buttons_switch_view_enable');
            document.querySelector('#button_switch_card span').classList.add('mod_emundus_application___buttons_switch_view_disabled');
            document.querySelector('#button_switch_list').classList.add('mod_emundus_application___buttons_enable');
            document.querySelector('#button_switch_list span').classList.remove('mod_emundus_application___buttons_switch_view_disabled');
            document.querySelector('#button_switch_list span').classList.add('mod_emundus_application___buttons_switch_view_enable');
        } else {
            document.querySelector('#applications_list_view').style.display = 'none';
            document.querySelector('#applications_card_view').style.display = 'block';
            document.querySelector('#button_switch_list').classList.remove('mod_emundus_application___buttons_enable');
            document.querySelector('#button_switch_list span').classList.remove('mod_emundus_application___buttons_switch_view_enable');
            document.querySelector('#button_switch_list span').classList.add('mod_emundus_application___buttons_switch_view_disabled');
            document.querySelector('#button_switch_card').classList.add('mod_emundus_application___buttons_enable');
            document.querySelector('#button_switch_card span').classList.remove('mod_emundus_application___buttons_switch_view_disabled');
            document.querySelector('#button_switch_card span').classList.add('mod_emundus_application___buttons_switch_view_enable');
        }

    }

    /** TABS **/
    function updateTab(tab) {
        sessionStorage.setItem("mod_emundus_applications___selected_tab", tab);
        document.querySelectorAll('div[id*="tab_link_"]').forEach((elt) => {
            if (elt.id !== 'tab_link_' + tab) {
                elt.classList.remove('em-light-selected-tab');
            } else {
                elt.classList.add('em-light-selected-tab');
            }
        })

        document.querySelectorAll('div[id*="group_application_tab_"]').forEach((elt) => {
            if (elt.id !== 'group_application_tab_' + tab) {
                elt.classList.add('em-display-none');
            } else {
                elt.classList.remove('em-display-none');
            }
        })
    }

    async function createTab() {
        const {value: tabName} = await Swal.fire({
            title: "<?= JText::_('MOD_EM_APPLICATION_TABS_CREATE_TAB_SWAL'); ?>",
            input: 'text',
            inputAttributes: {
                maxlength: 30,
            },
            text: "<?= JText::_('MOD_EMUNDUS_APPLICATIONS_TAB_NAME'); ?>",
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonText: "<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_TAB_CREATE_BUTTON');?>",
            cancelButtonText: "<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_TAB_CANCEL_BUTTON');?>",
            customClass: {
                container: 'mod_emundus_application_swal_manage_tabs_container',
                popup: 'mod_emundus_application_swal_manage_tabs_popup',
                header: 'mod_emundus_application_swal_manage_tabs_header',
                htmlContainer: 'mod_emundus_application_swal_manage_tabs_content',
                confirmButton: 'mod_emundus_application_swal_manage_tabs_confirm',
                cancelButton: 'mod_emundus_application_swal_manage_tabs_cancel',
                actions: 'mod_emundus_application_swal_manage_tabs_actions',
            },
            inputValidator: (value) => {
                if (!value) {
                    return "<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_TAB_PLEASE_FILL_NAME');?>";
                }
            }
        });

        if (tabName) {
            let formData = new FormData();
            formData.append('name', tabName);

            fetch('index.php?option=com_emundus&controller=application&task=createtab', {
                body: formData,
                method: 'post',
            }).then((response) => {
                if (response.ok) {
                    return response.json();
                }
            }).then((res) => {
                if (res.tab != 0) {
                    let html = '<div id="tab_link_' + res.tab + '" onclick="updateTab(' + res.tab + ')" class="em-mr-16 em-flex-row em-light-tabs em-pointer"><p class="em-font-size-14 em-text-neutral-600" style="white-space: nowrap">' + tabName + '</p><span class="mod_emundus_applications_badge">0</span></div>';
                    document.getElementById('tab_adding_link').insertAdjacentHTML('beforebegin', html);
                    document.getElementById('tab_adding_link').style.display = 'none';
                    document.getElementById('tab_manage_links').style.display = 'flex';
                } else {
                    Swal.fire({
                        title: "Une erreur est survenue",
                        text: "",
                        type: "error",
                        reverseButtons: true,
                        confirmButtonText: "<?php echo JText::_('JYES');?>",
                        timer: 3000
                    });
                }
            })
        }
    }

    async function manageTabs() {
        document.getElementById('items').innerHTML = '';
        if (document.getElementById('add_link_manage') != null) {
            document.getElementById('add_link_manage').remove();
        }
        fetch('index.php?option=com_emundus&controller=application&task=gettabs', {
            method: 'get',
        }).then((response) => {
            if (response.ok) {
                return response.json();
            }
        }).then((res) => {
            res.tabs.forEach((tab) => {
                let item = document.createElement('li');
                item.classList.add('em-flex-row', 'em-mb-12', 'em-grab', 'em-flex-space-between');
                item.id = 'tab_li_' + tab.id;
                item.innerHTML = '<div class="em-flex-row"><span class="material-icons-outlined em-font-size-14 em-mr-4">drag_indicator</span><span contenteditable="true" class="em-cursor-text" id="' + tab.id + '">' + tab.name + '</span></div><span class="material-icons-outlined em-mr-4 em-pointer em-red-500-color" onclick="deleteTab(' + tab.id + ',\'' + tab.name + '\')">close</span>';
                document.getElementById('items').appendChild(item);
            });
            let link_to_add = document.createElement('a');
            link_to_add.classList.add('em-flex-row', 'em-no-hover-underline', 'em-font-size-14', 'em-pointer');
            link_to_add.setAttribute('onclick', 'createTab()');
            link_to_add.id = 'add_link_manage'
            link_to_add.innerHTML = '<span class="material-icons-outlined em-font-size-14 em-mr-4">add</span><?php echo JText::_('MOD_EM_APPLICATION_TABS_ADD_TAB') ?>';
            document.getElementById('swal_manage').appendChild(link_to_add);

            let el = document.getElementById('swal_manage').cloneNode(true);
            Sortable.create(el.childNodes[1]);

            Swal.fire({
                title: "<?= JText::_('MOD_EM_APPLICATION_TABS_MANAGE_TABS_SWAL'); ?>",
                html: el,
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: "<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_TAB_SAVE');?>",
                cancelButtonText: "<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_TAB_CANCEL_BUTTON');?>",
                customClass: {
                    container: 'mod_emundus_application_swal_manage_tabs_container',
                    popup: 'mod_emundus_application_swal_manage_tabs_popup',
                    header: 'mod_emundus_application_swal_manage_tabs_header',
                    htmlContainer: 'mod_emundus_application_swal_manage_tabs_content',
                    confirmButton: 'mod_emundus_application_swal_manage_tabs_confirm',
                    cancelButton: 'mod_emundus_application_swal_manage_tabs_cancel',
                    actions: 'mod_emundus_application_swal_manage_tabs_actions',
                }
            }).then((confirm) => {
                if (confirm.value) {
                    let new_tabs = document.querySelectorAll('#items li');
                    let tabs_to_post = []
                    new_tabs.forEach((tab, index) => {
                        console.log(tab);
                        const tab_to_post = {
                            name: tab.firstChild.lastChild.innerText,
                            ordering: (index + 1),
                            id: tab.firstChild.lastChild.id
                        }
                        tabs_to_post.push(tab_to_post);
                    });

                    let formData = new FormData();
                    formData.append('tabs', JSON.stringify(tabs_to_post));

                    fetch('index.php?option=com_emundus&controller=application&task=updatetabs', {
                        body: formData,
                        method: 'post',
                    }).then((updating_response) => {
                        if (updating_response.ok) {
                            return updating_response.json();
                        }
                    }).then((updating_res) => {
                        if (updating_res.updated) {
                            window.location.reload();
                        } else {
                            Swal.fire({
                                title: "Une erreur est survenue",
                                text: "",
                                type: "error",
                                reverseButtons: true,
                                confirmButtonText: "<?php echo JText::_('JYES');?>",
                                timer: 3000
                            });
                        }
                    })
                }
            });
        })
    }

    function deleteTab(tab, name) {
        Swal.fire({
            title: "<?= JText::_('MOD_EM_APPLICATION_TABS_MANAGE_TABS_DELETE_SWAL'); ?>",
            text: "<?= JText::_('MOD_EM_APPLICATION_TABS_MANAGE_TABS_CONFIRM_DELETE_SWAL'); ?> " + name + " ?",
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonText: "<?php echo JText::_('MOD_EM_APPLICATION_TABS_MANAGE_TABS_DELETE_SWAL_BUTTON');?>",
            cancelButtonText: "<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_TAB_CANCEL_BUTTON');?>",
            customClass: {
                container: 'mod_emundus_application_swal_manage_tabs_container',
                popup: 'mod_emundus_application_swal_manage_tabs_popup',
                header: 'mod_emundus_application_swal_manage_tabs_header',
                htmlContainer: 'mod_emundus_application_swal_manage_tabs_content',
                confirmButton: 'mod_emundus_application_swal_manage_tabs_confirm',
                cancelButton: 'mod_emundus_application_swal_manage_tabs_cancel',
                actions: 'mod_emundus_application_swal_manage_tabs_actions',
            }
        }).then((confirm) => {
            if (confirm.value) {
                fetch('index.php?option=com_emundus&controller=application&task=deletetab&tab=' + tab, {
                    method: 'get'
                }).then((response) => {
                    if (response.ok) {
                        return response.json();
                    }
                }).then((res) => {
                    if (res.deleted == true) {
                        document.querySelector('#tab_link_' + tab).remove();
                        let selected_tab_session = sessionStorage.getItem('mod_emundus_applications___selected_tab');
                        if (selected_tab_session !== null && selected_tab_session == tab) {
                            sessionStorage.removeItem('mod_emundus_applications___selected_tab');
                            this.updateTab(0);
                        }
                    }
                });
            }
        });
    }

    async function moveToTab(fnum, tab, view) {
        let tabs = {};

        fetch('index.php?option=com_emundus&controller=application&task=gettabs', {
            method: 'get',
        }).then((response) => {
            if (response.ok) {
                return response.json();
            }
        }).then(async (res) => {
            document.querySelector('#actions_block_' + fnum + '_' + view + '_' + tab).style.display = 'none';
            if (res.tabs.length === 0) {
                await this.createTab();
            } else {
                tabs[0] = "<?= JText::_('MOD_EMUNDUS_APPLICATIONS_PLEASE_SELECT'); ?>"
                res.tabs.forEach((tab) => {
                    tabs[tab.id] = tab.name;
                });

                const {value: tab} = await Swal.fire({
                    title: "<?= JText::_('MOD_EMUNDUS_APPLICATIONS_MOVE_TO_TAB_SWAL'); ?>",
                    text: "<?= JText::_('MOD_EMUNDUS_APPLICATIONS_TAB_SELECT'); ?>",
                    input: 'select',
                    inputOptions: tabs,
                    showCancelButton: true,
                    reverseButtons: true,
                    confirmButtonText: "<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_TAB_MOVE');?>",
                    cancelButtonText: "<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_TAB_CANCEL_BUTTON');?>",
                    customClass: {
                        container: 'mod_emundus_application_swal_manage_tabs_container',
                        popup: 'mod_emundus_application_swal_manage_tabs_popup',
                        header: 'mod_emundus_application_swal_manage_tabs_header',
                        htmlContainer: 'mod_emundus_application_swal_manage_tabs_content',
                        confirmButton: 'mod_emundus_application_swal_manage_tabs_confirm',
                        cancelButton: 'mod_emundus_application_swal_manage_tabs_cancel',
                        actions: 'mod_emundus_application_swal_manage_tabs_actions',
                    },
                    inputValidator: (value) => {
                        if (value == 0) {
                            return "<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_TAB_PLEASE_SELECT_A_TAB');?>";
                        }
                    }
                });

                if (tab) {
                    let formData = new FormData();
                    formData.append('fnum', fnum);
                    formData.append('tab', tab);

                    fetch('index.php?option=com_emundus&controller=application&task=movetotab', {
                        body: formData,
                        method: 'post',
                    }).then((response) => {
                        if (response.ok) {
                            return response.json();
                        }
                    }).then((res) => {
                        if (res.status == true) {
                            window.location.reload();
                        } else {
                            Swal.fire({
                                title: "Une erreur est survenue",
                                text: res.msg,
                                type: "error",
                                reverseButtons: true,
                                confirmButtonText: "<?php echo JText::_('JYES');?>",
                                timer: 3000
                            });
                        }
                    });
                }
            }
        });
    }

    /** END **/

    async function copyApplication(fnum) {
        fetch('index.php?option=com_emundus&controller=application&task=getcampaignsavailableforcopy&' + new URLSearchParams({
            fnum: fnum,
        }), {
            method: 'get',
        }).then((response) => {
            if (response.ok) {
                return response.json();
            }
        }).then(async (res) => {
            document.querySelector('.mod_emundus_applications__actions').style.display = 'none';

            const {value: campaign} = await Swal.fire({
                title: "<?= JText::_('MOD_EMUNDUS_APPLICATIONS_COPY_FILE'); ?>",
                text: "<?= JText::_('MOD_EMUNDUS_APPLICATIONS_COPY_FILE_CAMPAIGN'); ?>",
                input: 'select',
                inputOptions: res.campaigns,
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: "<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_COPY_FILE_ACTION');?>",
                cancelButtonText: "<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_TAB_CANCEL_BUTTON');?>",
                customClass: {
                    container: 'mod_emundus_application_swal_manage_tabs_container',
                    popup: 'mod_emundus_application_swal_manage_tabs_popup',
                    header: 'mod_emundus_application_swal_manage_tabs_header',
                    htmlContainer: 'mod_emundus_application_swal_manage_tabs_content',
                    confirmButton: 'mod_emundus_application_swal_manage_tabs_confirm',
                    cancelButton: 'mod_emundus_application_swal_manage_tabs_cancel',
                    actions: 'mod_emundus_application_swal_manage_tabs_actions',
                },
                inputValidator: (value) => {
                    if (!value) {
                        return "<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_TAB_PLEASE_SELECT_A_CAMPAIGN');?>";
                    }
                }
            });

            if (campaign) {
                let formData = new FormData();
                formData.append('fnum', fnum);
                formData.append('campaign', campaign);

                fetch('index.php?option=com_emundus&controller=application&task=copyfile', {
                    body: formData,
                    method: 'post',
                }).then((response) => {
                    if (response.ok) {
                        return response.json();
                    }
                }).then((res) => {
                    if (res.status == true) {
                        window.location.href = res.first_page;
                    } else {
                        Swal.fire({
                            title: "Une erreur est survenue",
                            text: res.msg,
                            type: "error",
                            reverseButtons: true,
                            confirmButtonText: "<?php echo JText::_('JYES');?>",
                            timer: 3000
                        });
                    }
                });
            }
        });
    }

    async function renameApplication(fnum, name, campaign_label) {
        if (name === '') {
            name = campaign_label;
        }
        await Swal.fire({
            title: "<?= JText::_('MOD_EMUNDUS_APPLICATIONS_RENAME_APPLICATION'); ?>",
            text: "<?= JText::_('MOD_EMUNDUS_APPLICATIONS_RENAME_APPLICATION_NAME'); ?>",
            input: 'text',
            inputValue: name,
            inputAttributes: {
                maxlength: 80,
            },
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonText: "<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_RENAME_FILE_ACTION');?>",
            cancelButtonText: "<?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_TAB_CANCEL_BUTTON');?>",
            customClass: {
                container: 'mod_emundus_application_swal_manage_tabs_container',
                popup: 'mod_emundus_application_swal_manage_tabs_popup',
                header: 'mod_emundus_application_swal_manage_tabs_header',
                htmlContainer: 'mod_emundus_application_swal_manage_tabs_content',
                confirmButton: 'mod_emundus_application_swal_manage_tabs_confirm',
                cancelButton: 'mod_emundus_application_swal_manage_tabs_cancel',
                actions: 'mod_emundus_application_swal_manage_tabs_actions',
            }
        }).then((result) => {
            if (result.value) {
                let formData = new FormData();
                formData.append('fnum', fnum);
                formData.append('new_name', result.value);

                fetch('index.php?option=com_emundus&controller=application&task=renamefile', {
                    body: formData,
                    method: 'post',
                }).then((response) => {
                    if (response.ok) {
                        return response.json();
                    }
                }).then((res) => {
                    if (res.status == true) {
                        window.location.reload();
                    } else {
                        Swal.fire({
                            title: "Une erreur est survenue",
                            text: res.msg,
                            type: "error",
                            reverseButtons: true,
                            confirmButtonText: "<?php echo JText::_('JYES');?>",
                            timer: 3000
                        });
                    }
                });
            }
        });
    }

    function displaySort() {
        let sort = document.getElementById('sort_block');
        if (sort.style.display === 'none') {
            sort.style.display = 'flex';
        } else {
            sort.style.display = 'none';
        }
    }

    function filterApplications(type, value) {
        let formData = new FormData();
        formData.append('type', type);
        formData.append('value', value);

        fetch('index.php?option=com_emundus&controller=application&task=filterapplications', {
            body: formData,
            method: 'post',
        }).then((response) => {
            if (response.ok) {
                return response.json();
            }
        }).then((res) => {
            if (res.status == true) {
                window.location.reload();
            } else {
                Swal.fire({
                    title: "Une erreur est survenue",
                    text: res.msg,
                    type: "error",
                    reverseButtons: true,
                    confirmButtonText: "<?php echo JText::_('JYES');?>",
                    timer: 3000
                });
            }
        });
    }
</script>
