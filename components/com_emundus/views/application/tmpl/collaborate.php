<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */
use Joomla\CMS\Language\Text;

Text::script('COM_EMUNDUS_APPLICATION_SHARE_EMAILS');
Text::script('COM_EMUNDUS_APPLICATION_SHARE_READ');
Text::script('COM_EMUNDUS_APPLICATION_SHARE_UPDATE');
Text::script('COM_EMUNDUS_APPLICATION_SHARE_VIEW_HISTORY');
Text::script('COM_EMUNDUS_APPLICATION_SHARE_VIEW_OTHERS');
Text::script('COM_EMUNDUS_APPLICATION_SHARE_VIEW_REQUESTS');

?>

<div>
	<div id="collab_emails_block">
		<label for="collab_emails" class="text-black"><?php echo Text::_('COM_EMUNDUS_APPLICATION_SHARE_EMAILS') ?></label>
		<input type="text" name="collab_emails" id="collab_emails" class="mt-2" />
	</div>

	<div class="mt-6">
		<?php if(sizeof($this->collaborators) > 0) : ?>
			<div class="flex items-center justify-between" onclick="toggleRequests()">
				<h3><?php echo Text::_('COM_EMUNDUS_APPLICATION_SHARE_VIEW_REQUESTS') ?></h3>
				<span class="material-icons" id="requests_icon">expand_less</span>
			</div>
			<div class="mt-2 flex flex-col gap-2 hidden" id="collaborators_requests">
				<?php foreach ($this->collaborators as $collaborator) : ?>
					<div class="py-4 px-6 border border-neutral-500 rounded-md shadow-sm" id="collaborator_block_<?php echo $collaborator->id ?>">
						<div class="flex items-center justify-between">
							<div class="flex items-center" style="max-width: 50%">
								<?php if(empty($collaborator->profile_picture)) : ?>
									<span class="material-icons-outlined"
									      style="font-size: 48px"
									      alt="<?php echo JText::_('PROFILE_ICON_ALT') ?>">account_circle</span>
								<?php else : ?>
									<div class="em-profile-picture em-pointer em-user-dropdown-button flex-none"
									     style="background-image:url('<?php echo $collaborator->profile_picture ?>');">
									</div>
								<?php endif; ?>
								<div class="ml-3">
									<span class="text-sm mb-3">Envoyé le <?php echo EmundusHelperDate::displayDate($collaborator->time_date,'DATE_FORMAT_LC2',0)?></span>
									<p><?php echo !empty($collaborator->user_id) ? $collaborator->user_lastname . ' ' . $collaborator->user_firstname : $collaborator->email; ?></p>
								</div>
							</div>

                            <div class="flex items-center gap-3">
                                <div>
                                    <?php if($collaborator->uploaded == 1) : ?>
                                        <span class="label label-green-2 text-white"><?php echo Text::_('COM_EMUNDUS_APPLICATION_SHARE_ACCEPTED_STATUS') ?></span>
                                    <?php else: ?>
                                        <span class="label label-beige"><?php echo Text::_('COM_EMUNDUS_APPLICATION_SHARE_SENT_STATUS') ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center justify-end gap-3" style="min-width: 50px">
                                    <?php if ($collaborator->uploaded == 0) : ?>
                                        <span class="material-icons-outlined cursor-pointer" id="email_icon_<?php echo $collaborator->id ?>" onclick="sendNewEmail('<?php echo $collaborator->id ?>','<?php echo $collaborator->ccid ?>','<?php echo $collaborator->fnum ?>')">send</span>
                                    <?php endif; ?>
                                    <span class="material-icons-outlined cursor-pointer text-red-500" onclick="removeShared('<?php echo $collaborator->id ?>','<?php echo $collaborator->ccid ?>','<?php echo $collaborator->fnum ?>')">person_remove</span>
                                </div>
                            </div>
						</div>

						<hr/>

						<div class="flex items-center justify-between flex-wrap">
							<div class="flex items-center gap-2">
								<input class="!mt-0" type="checkbox" name="rights_<?php echo $collaborator->id; ?>" id="read_<?php echo $collaborator->id; ?>" value="r" onchange="updateRight('<?php echo $collaborator->id ?>','<?php echo $collaborator->ccid ?>','<?php echo $collaborator->fnum ?>',this.value, this.checked)" <?php if($collaborator->r == 1) : ?>checked<?php endif; ?> />
								<label class="!mb-0" for="read_<?php echo $collaborator->id; ?>"><?php echo Text::_('COM_EMUNDUS_APPLICATION_SHARE_READ') ?></label>
							</div>

							<div class="flex items-center gap-2">
								<input class="!mt-0" type="checkbox" name="rights_<?php echo $collaborator->id; ?>" id="update_<?php echo $collaborator->id; ?>" value="u" onchange="updateRight('<?php echo $collaborator->id ?>','<?php echo $collaborator->ccid ?>','<?php echo $collaborator->fnum ?>',this.value, this.checked)" <?php if($collaborator->u == 1) : ?>checked<?php endif; ?> />
								<label class="!mb-0" for="update_<?php echo $collaborator->id; ?>"><?php echo Text::_('COM_EMUNDUS_APPLICATION_SHARE_UPDATE') ?></label>
							</div>

							<div class="flex items-center gap-2">
								<input class="!mt-0" type="checkbox" name="rights_<?php echo $collaborator->id; ?>" id="view_history_<?php echo $collaborator->id; ?>" value="show_history" onchange="updateRight('<?php echo $collaborator->id ?>','<?php echo $collaborator->ccid ?>','<?php echo $collaborator->fnum ?>',this.value, this.checked)" <?php if($collaborator->show_history == 1) : ?>checked<?php endif; ?> />
								<label class="!mb-0" for="view_history_<?php echo $collaborator->id; ?>"><?php echo Text::_('COM_EMUNDUS_APPLICATION_SHARE_VIEW_HISTORY') ?></label>
							</div>

							<div class="flex items-center gap-2">
								<input class="!mt-0" type="checkbox" name="rights_<?php echo $collaborator->id; ?>" id="view_others_<?php echo $collaborator->id; ?>" value="show_shared_users" onchange="updateRight('<?php echo $collaborator->id ?>','<?php echo $collaborator->ccid ?>','<?php echo $collaborator->fnum ?>',this.value, this.checked)" <?php if($collaborator->show_shared_users == 1) : ?>checked<?php endif; ?> />
								<label class="!mb-0" for="view_others_<?php echo $collaborator->id; ?>"><?php echo Text::_('COM_EMUNDUS_APPLICATION_SHARE_VIEW_OTHERS') ?></label>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>