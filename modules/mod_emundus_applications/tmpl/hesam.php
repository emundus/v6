<?php

/**
 * @package     Joomla.Site
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'cifre.php');
$m_cifre = new EmundusModelCifre();

echo $description;
$uri = JUri::getInstance();

$confirm_form_url = $m_application->getConfirmUrl($fnums);
$first_page       = $m_application->getFirstPage('index.php', $fnums);
$contacts         = modemundusApplicationsHelper::getContactOffers($fnums);
$chat_requests    = modemundusApplicationsHelper::getChatRequests(JFactory::getUser()->id);


// Include Iconate in order to animate de favorite icon.
$document = JFactory::getDocument();
$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/iconate/0.3.1/iconate.js');
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/iconate/0.3.1/iconate.min.css');
?>

<div class="content">
    <div class="w-container">
		<?php if ($show_add_application && ($position_add_application == 0 || $position_add_application == 2) && $applicant_can_renew) : ?>
            <a class="big-card-add w-inline-block" href="<?= JURI::base(); ?>component/fabrik/form/102">
                <div class="ajouter-sujet"><?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></div>
            </a>
		<?php endif; ?>

		<?php if (!empty($applications)) : ?>

            <div class="em-hesam-applications w-container">
				<?php foreach ($applications as $application) : ?>

					<?php
					// If two favorites are present, display chat icon.
					$favorites = $m_cifre->checkForTwoFavorites($application->fnum, $user->id);
					$nb_faves  = count($favorites);
					if ($nb_faves === 2) {

						$m_messages = new EmundusModelMessages();

						$favorite_users = [];
						// Here we get the two users from our favorites who are not us.
						foreach ($favorites as $fav) {
							if ($fav->user_to == $user->id) {
								$favorite_users[] = $fav->user_from;
							}
							else {
								$favorite_users[] = $fav->user_to;
							}
						}

						// Look up the chatroom id using our three users.
						$chatroom_id = $m_messages->getChatroomByUsers($user->id, $favorite_users[0], $favorite_users[1]);
					}
					?>

                    <div class="wrapper-big-car" id="row<?= $application->fnum; ?>">

                        <div class="headerbig-card">
                            <div class="div-block-3">
                                <span class="fa fa-user"></span>
                                <div>
                                    <div class="small-explanation">
										<?= JText::_('YOUR_OFFER') . ' - <em>' . $application->value . '</em>'; ?>
                                    </div>
                                    <div class="text-block-2">
										<?= (!empty($application->titre)) ? $application->titre : JText::_('NO_TITLE'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="wrapper-edit">

								<?php if ($nb_faves === 2 && ($application->status === '1' || $application->status === '3')) : ?>
                                    <div class="link w-inline-block"
                                         onclick="completefile('<?= $application->fnum; ?>', false)">
                                        <span class="fa fa-check"></span>
                                    </div>
								<?php endif; ?>

								<?php if ($application->status === '1' || $application->status === '2') : ?>
                                    <span class="fa fa-share-alt"
                                          onclick="share('<?= addslashes(preg_replace("/\r|\n/", "", $application->titre)); ?>', '<?= addslashes(preg_replace("/\r|\n/", "", (strlen($application->question) >= 150) ? substr($application->question, 0, 147) . '...' : $application->question)); ?>')"></span>
								<?php endif; ?>

								<?php if ($application->status === '0') : ?>
                                    <!-- Edit button -->
                                    <a id="edit"
                                       href="<?= JRoute::_(JURI::base() . 'index.php?option=com_emundus&task=openfile&fnum=' . $application->fnum . '&redirect=' . base64_encode($first_page[$application->fnum]['link'])); ?>"
                                       title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_OPEN_APPLICATION'); ?>">
                                        <span class="fa fa-edit"></span>
                                    </a>
								<?php endif; ?>


								<?php if ($application->status === '6') : ?>
                                    <!-- Automatically unpublished offers have a republish button that appears. -->
                                    <a class="cta-republish w-button" href="#"
                                       onclick="publishfile('<?= $application->fnum; ?>')"><?= JText::_('MOD_EMUNDUS_APPLICATIONS_REPUBLISH_APPLICATION'); ?></a>
								<?php endif; ?>

                                <!-- Trash button -->
								<?php if ($application->status === '0') : ?>
                                    <a id="trash" onClick="deletefile('<?= $application->fnum; ?>')"
                                       href="#row<?= $application->fnum; ?>"
                                       title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_DELETE_APPLICATION_FILE'); ?>">
                                        <i class="icon-trash"></i>
                                    </a>
								<?php elseif ($application->status === '1') : ?>
                                    <a id="trash" onClick="completefile('<?= $application->fnum; ?>', true)"
                                       href="#row<?= $application->fnum; ?>"
                                       title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_DELETE_APPLICATION_FILE'); ?>">
                                        <i class="icon-trash"></i>
                                    </a>
								<?php else : ?>
                                    <a id="trash" onClick="unpublishfile('<?= $application->fnum; ?>')"
                                       href="#row<?= $application->fnum; ?>"
                                       title="<?= JText::_('UNPUBLISH_APPLICATION'); ?>">
                                        <i class="icon-trash"></i>
                                    </a>
								<?php endif; ?>
                            </div>
                        </div>

                        <div class="big-card<?= ($application->status === '0') ? '-brouillon' : '' ?>">
							<?php if ($application->status === '0') : ?>
                                <p class="paragraph-infos">
                                    <strong><?= JText::_('OFFER_IS_DRAFT'); ?><br>‍</strong>
                                    <span class="text-span-2"><?= JText::_('OFFER_IS_DRAFT_DESCRIPTION'); ?><br></span>
                                </p>
                                <a class="cta-brouillon w-button"
                                   href="<?= JRoute::_(JURI::base() . 'index.php?option=com_emundus&task=openfile&fnum=' . $application->fnum . '&redirect=' . base64_encode($first_page[$application->fnum]['link'])); ?>"><?= JText::_('MOD_EMUNDUS_APPLICATIONS_OPEN_APPLICATION'); ?></a>
							<?php else : ?>
                                <div class="column-card-container w-row">

									<?php if ($nb_faves === 2 && !empty($chatroom_id)) : ?>
                                        <a href="/index.php?option=com_emundus&view=messages&layout=hesamchatroom&chatroom=<?= $chatroom_id; ?>"
                                           class="em-join-icon">
                                            <i class="image-chatroom fa fa-chatroom"></i>
                                        </a>
                                        <a href="/index.php?option=com_emundus&view=messages&layout=hesamchatroom&chatroom=<?= $chatroom_id; ?>"
                                           class="em-join-icon em-mobile-join-icon">
                                            <i class="image-chatroom fa fa-chatroom"></i>
                                        </a>
									<?php endif; ?>

									<?php if ($application->profile_id !== '1006') : ?>
                                        <div class="w-col w-col-6">

                                            <a href="consultez-les-offres?q=doctorant&fnum=<?= $application->fnum; ?>"
                                               class="card-ajouter-these w-inline-block">
                                                <div class="ajouter-sujet"><?= JText::_('SEARCH_DOCTORANT'); ?></div>
                                            </a>

											<?php foreach ($contacts[$application->fnum]['1006'] as $contact) : ?>


												<?php
												// Here we are checking if a double favorite has been made in the other direction.
												if ($contact['linked_fnum'] === $application->fnum) {
													$other_fnum = $contact['fnum'];
												}
												else {
													$other_fnum = $contact['linked_fnum'];
												}

												// If two favorites are present, display chat icon.
												$contact_favorites[$contact['link_id']] = $m_cifre->checkForTwoFavorites($other_fnum, (int) substr($other_fnum, -7));
												$contact_nb_faves[$contact['link_id']]  = count($contact_favorites[$contact['link_id']]);
												if ($contact_nb_faves[$contact['link_id']] === 2) {

													$m_messages = new EmundusModelMessages();

													$favorite_users = [];
													// Here we get the two users from our favorites who are not us.
													foreach ($contact_favorites[$contact['link_id']] as $fav) {
														$favorite_users[] = $fav->user_from;
														$favorite_users[] = $fav->user_to;
													}

													$favorite_users = array_values(array_unique($favorite_users));

													// Look up the chatroom id using our three users.
													if (in_array($user->id, $favorite_users)) {
														$contact_chat[$contact['link_id']] = $m_messages->getChatroomByUsers($favorite_users[0], $favorite_users[1], $favorite_users[2]);
													}
												}
												?>

                                                <!-- Futur doc -->
                                                <div class="card w-clearfix" id="card-<?= $contact['link_id']; ?>">

                                                    <!-- Favorite system. -->
													<?php if ($contact['state'] === '2') : ?>
														<?php if ($contact['favorite']) : ?>
                                                            <i class="fa fa-star em-star-button link-block-3 w-inline-block"
                                                               id="favorite-<?= $contact['link_id']; ?>" rel="tooltip"
                                                               title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_FAVORITE_CLICK_HERE_UNFAV'); ?>"
                                                               onclick="unfavorite(<?= $contact['link_id']; ?>)"></i>
														<?php else : ?>
                                                            <i class="fa fa-star-o em-star-button link-block-3 w-inline-block"
                                                               rel="tooltip"
                                                               title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_FAVORITE_CLICK_HERE_FAV'); ?>"
                                                               onclick="favorite(<?= $contact['link_id']; ?>)"></i>
														<?php endif; ?>
													<?php endif; ?>

                                                    <div class="headsmallcard"></div>
													<?php
													$cardClass = '';
													if ($contact['state'] === '1') {
														if ($contact['direction'] === '-1') {
															$cardClass = 'pending';
														}
                                                        elseif ($contact['direction'] === '1') {
															$cardClass = 'demandecontact';
														}
													}
                                                    elseif ($contact['state'] === '2') {
														$cardClass = 'accepted';
													}
													?>
                                                    <div class="wrapper-small-card-content <?= $cardClass; ?>">
                                                        <div class="w-row">
                                                            <div class="w-col w-col-8">
                                                                <div class="text-block-2"><?= $contact['titre']; ?></div>
                                                            </div>
															<?php if ($contact['direction'] === '-1' && $contact['state'] === '1') : ?>
                                                                <div class="w-col w-col-4">
                                                                    <div class="statuts envoye"><?= JText::_('REQUEST_SENT'); ?></div>
                                                                </div>
															<?php elseif ($contact['direction'] === '1' && $contact['state'] === '1') : ?>
                                                                <div class="w-col w-col-4">
                                                                    <div class="statuts recu"><?= JText::_('REQUEST_RECEIVED'); ?></div>
                                                                </div>
															<?php endif; ?>
                                                        </div>
                                                        <div class="div-block">
                                                            <span class="fa fa-user-graduate"></span>
                                                            <div class="name">
																<?= JFactory::getUser($contact['applicant_id'])->name; ?>
                                                            </div>
                                                        </div>
                                                        <div class="div-block-mail">
                                                            <div class="w-row">
                                                                <div class="w-col w-col-9">
                                                                    <div class="sujet">
																		<?php if ($contact['status'] === 3) : ?>
																			<?= JText::_('OFFER_UNPUBLISHED'); ?>
																		<?php elseif (!empty($contact['linked_fnum'])) : ?>
                                                                            <a href="consultez-les-offres/details/299/<?= $contact['search_engine_page']; ?>"><?= JText::_('CONSULT_OFFER'); ?></a>
																		<?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="column-2 w-col w-col-3">

																	<?php if ($contact_nb_faves[$contact['link_id']] === 2 && !empty($contact_chat[$contact['link_id']])) : ?>
                                                                        <a href="/index.php?option=com_emundus&view=messages&layout=hesamchatroom&chatroom=<?= $contact_chat[$contact['link_id']]; ?>"
                                                                           class="link w-inline-block">
                                                                            <i class="image-chatroom fa fa-chatroom"></i>
                                                                        </a>
																	<?php endif; ?>

																	<?php if ($contact['notify']) : ?>
                                                                        <i class="fa fa-bell em-bell-button"
                                                                           rel="tooltip"
                                                                           title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_NOTIFY_CLICK_HERE_UNNOTIF'); ?>"
                                                                           onclick="unnotify(<?= $contact['link_id']; ?>)"></i>
																	<?php else : ?>
                                                                        <i class="fa fa-bell-slash-o em-bell-button"
                                                                           rel="tooltip"
                                                                           title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_NOTIFY_CLICK_HERE'); ?>"
                                                                           onclick="notify(<?= $contact['link_id']; ?>)"></i>
																	<?php endif; ?>

                                                                    <a href="/index.php?option=com_emundus&view=messages&layout=chat&chatid=<?= $contact['applicant_id']; ?>"
                                                                       class="link w-inline-block">
                                                                        <i class="image-mail fa <?= ($contact['unread'] == 0) ? 'fa-envelope-o' : 'fa-envelope'; ?>"></i>
                                                                    </a>
                                                                    <div class="notif <?= ($contact['unread'] == 0) ? '_0notif' : ''; ?>">
                                                                        <div class="notif-number <?= ($contact['unread'] == 0) ? '_0notif' : ''; ?>"><?= $contact['unread']; ?></div>
                                                                    </div>

																	<?php if ($contact['direction'] === '1' && $contact['state'] === '1') : ?>
                                                                        <div id="contactButtons-<?= $contact['link_id']; ?>"
                                                                             class="contact-buttons">
                                                                            <div class="accepter"
                                                                                 onclick="reply('<?= $contact['link_id']; ?>')"></div>
                                                                            <div class="refuser"
                                                                                 onclick="breakUp('<?= $contact['link_id']; ?>')"></div>
                                                                        </div>
																	<?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
											<?php endforeach; ?>
                                        </div>
									<?php endif; ?>

									<?php if ($application->profile_id !== '1007') : ?>
                                        <div class="w-col w-col-6">

                                            <a href="consultez-les-offres?q=laboratoire&fnum=<?= $application->fnum; ?>"
                                               class="card-ajouter-these w-inline-block">
                                                <div class="ajouter-sujet"><?= JText::_('SEARCH_RESEARCH'); ?></div>
                                            </a>

											<?php foreach ($contacts[$application->fnum]['1007'] as $contact) : ?>

												<?php
												// Here we are checking if a double favorite has been made in the other direction.
												if ($contact['linked_fnum'] === $application->fnum) {
													$other_fnum = $contact['fnum'];
												}
												else {
													$other_fnum = $contact['linked_fnum'];
												}

												// If two favorites are present, display chat icon.
												$contact_favorites[$contact['link_id']] = $m_cifre->checkForTwoFavorites($other_fnum, (int) substr($other_fnum, -7));
												$contact_nb_faves[$contact['link_id']]  = count($contact_favorites[$contact['link_id']]);
												if ($contact_nb_faves[$contact['link_id']] === 2) {

													$m_messages = new EmundusModelMessages();

													$favorite_users = [];
													// Here we get the two users from our favorites who are not us.
													foreach ($contact_favorites[$contact['link_id']] as $fav) {
														$favorite_users[] = $fav->user_from;
														$favorite_users[] = $fav->user_to;
													}

													$favorite_users = array_values(array_unique($favorite_users));

													// Look up the chatroom id using our three users.
													if (in_array($user->id, $favorite_users)) {
														$contact_chat[$contact['link_id']] = $m_messages->getChatroomByUsers($favorite_users[0], $favorite_users[1], $favorite_users[2]);
													}
												}

												?>

                                                <!-- Équipe de recherche -->
                                                <div class="card w-clearfix" id="card-<?= $contact['link_id']; ?>">

                                                    <!-- Favorite system. -->
													<?php if ($contact['state'] === '2') : ?>
														<?php if ($contact['favorite']) : ?>
                                                            <i class="fa fa-star em-star-button link-block-3 w-inline-block"
                                                               id="favorite-<?= $contact['link_id']; ?>" rel="tooltip"
                                                               title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_FAVORITE_CLICK_HERE_UNFAV'); ?>"
                                                               onclick="unfavorite(<?= $contact['link_id']; ?>)"></i>
														<?php else : ?>
                                                            <i class="fa fa-star-o em-star-button link-block-3 w-inline-block"
                                                               rel="tooltip"
                                                               title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_FAVORITE_CLICK_HERE_FAV'); ?>"
                                                               onclick="favorite(<?= $contact['link_id']; ?>)"></i>
														<?php endif; ?>
													<?php endif; ?>

                                                    <div class="headsmallcard"></div>
													<?php
													$cardClass = '';
													if ($contact['state'] === '1') {
														if ($contact['direction'] === '-1') {
															$cardClass = 'pending';
														}
                                                        elseif ($contact['direction'] === '1') {
															$cardClass = 'demandecontact';
														}
													}
                                                    elseif ($contact['state'] === '2') {
														$cardClass = 'accepted';
													}
													?>
                                                    <div class="wrapper-small-card-content <?= $cardClass; ?>">
                                                        <div class="w-row">
                                                            <div class="w-col w-col-8">
                                                                <div class="text-block-2"><?= $contact['titre']; ?></div>
                                                            </div>
															<?php if ($contact['direction'] === '-1' && $contact['state'] === '1') : ?>
                                                                <div class="w-col w-col-4">
                                                                    <div class="statuts envoye"><?= JText::_('REQUEST_SENT'); ?></div>
                                                                </div>
															<?php endif; ?>
                                                        </div>
                                                        <div class="div-block">
                                                            <span class="fa fa-flask"></span>
                                                            <div class="name">
																<?php
																$lab = $m_cifre->getUserLaboratory($contact['applicant_id']);
																echo (empty($lab)) ? JFactory::getUser($contact['applicant_id'])->name : $lab->name;
																?>
                                                            </div>
                                                        </div>
                                                        <div class="div-block-mail">
                                                            <div class="w-row">
                                                                <div class="w-col w-col-9">
                                                                    <div class="sujet">
																		<?php if ($contact['status'] === 3) : ?>
																			<?= JText::_('OFFER_UNPUBLISHED'); ?>
																		<?php elseif (!empty($contact['linked_fnum'])) : ?>
                                                                            <a href="consultez-les-offres/details/299/<?= $contact['search_engine_page']; ?>"><?= JText::_('CONSULT_OFFER'); ?></a>
																		<?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="column-2 w-col w-col-3">

																	<?php if ($contact_nb_faves[$contact['link_id']] === 2 && !empty($contact_chat[$contact['link_id']])) : ?>
                                                                        <a href="/index.php?option=com_emundus&view=messages&layout=hesamchatroom&chatroom=<?= $contact_chat[$contact['link_id']]; ?>"
                                                                           class="link w-inline-block">
                                                                            <i class="image-chatroom fa fa-chatroom"></i>
                                                                        </a>
																	<?php endif; ?>

																	<?php if ($contact['notify']) : ?>
                                                                        <i class="fa fa-bell em-bell-button"
                                                                           rel="tooltip"
                                                                           title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_NOTIFY_CLICK_HERE_UNNOTIF'); ?>"
                                                                           onclick="unnotify(<?= $contact['link_id']; ?>)"></i>
																	<?php else : ?>
                                                                        <i class="fa fa-bell-slash-o em-bell-button"
                                                                           rel="tooltip"
                                                                           title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_NOTIFY_CLICK_HERE'); ?>"
                                                                           onclick="notify(<?= $contact['link_id']; ?>)"></i>
																	<?php endif; ?>

                                                                    <a href="/index.php?option=com_emundus&view=messages&layout=chat&chatid=<?= $contact['applicant_id']; ?>"
                                                                       class="link w-inline-block">
                                                                        <i class="image-mail fa <?= ($contact['unread'] == 0) ? 'fa-envelope-o' : 'fa-envelope'; ?>"></i>
                                                                    </a>
                                                                    <div class="notif <?= ($contact['unread'] == 0) ? '_0notif' : ''; ?>">
                                                                        <div class="notif-number <?= ($contact['unread'] == 0) ? '_0notif' : ''; ?>"><?= $contact['unread']; ?></div>
                                                                    </div>

																	<?php if ($contact['direction'] === '1' && $contact['state'] === '1') : ?>
                                                                        <div id="contactButtons-<?= $contact['link_id']; ?>"
                                                                             class="contact-buttons">
                                                                            <div class="accepter"
                                                                                 onclick="reply('<?= $contact['link_id']; ?>')"></div>
                                                                            <div class="refuser"
                                                                                 onclick="breakUp('<?= $contact['link_id']; ?>')"></div>
                                                                        </div>
																	<?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
											<?php endforeach; ?>
                                        </div>
									<?php endif; ?>

									<?php if ($application->profile_id !== '1008') : ?>
                                        <div class="w-col w-col-6">

                                            <a href="consultez-les-offres?q=collectivite&fnum=<?= $application->fnum; ?>"
                                               class="card-ajouter-these w-inline-block">
                                                <div class="ajouter-sujet"><?= JText::_('SEARCH_COLLECTIVITY'); ?></div>
                                            </a>

											<?php foreach ($contacts[$application->fnum]['1008'] as $contact) : ?>

												<?php
												// Here we are checking if a double favorite has been made in the other direction.
												if ($contact['linked_fnum'] === $application->fnum) {
													$other_fnum = $contact['fnum'];
												}
												else {
													$other_fnum = $contact['linked_fnum'];
												}

												// If two favorites are present, display chat icon.
												$contact_favorites[$contact['link_id']] = $m_cifre->checkForTwoFavorites($other_fnum, (int) substr($other_fnum, -7));
												$contact_nb_faves[$contact['link_id']]  = count($contact_favorites[$contact['link_id']]);
												if ($contact_nb_faves[$contact['link_id']] === 2) {

													$m_messages = new EmundusModelMessages();

													$favorite_users = [];
													// Here we get the two users from our favorites who are not us.
													foreach ($contact_favorites[$contact['link_id']] as $fav) {
														$favorite_users[] = $fav->user_from;
														$favorite_users[] = $fav->user_to;
													}

													$favorite_users = array_values(array_unique($favorite_users));

													// Look up the chatroom id using our three users.
													if (in_array($user->id, $favorite_users)) {
														$contact_chat[$contact['link_id']] = $m_messages->getChatroomByUsers($favorite_users[0], $favorite_users[1], $favorite_users[2]);
													}
												}

												?>

                                                <!-- Acteur public ou associatif -->
                                                <div class="card w-clearfix" id="card-<?= $contact['link_id']; ?>">

													<?php if ($contact['state'] === '2') : ?>
                                                        <!-- Favorite system. -->
														<?php if ($contact['favorite']) : ?>
                                                            <i class="fa fa-star em-star-button link-block-3 w-inline-block"
                                                               id="favorite-<?= $contact['link_id']; ?>" rel="tooltip"
                                                               title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_FAVORITE_CLICK_HERE_UNFAV'); ?>"
                                                               onclick="unfavorite(<?= $contact['link_id']; ?>)"></i>
														<?php else : ?>
                                                            <i class="fa fa-star-o em-star-button link-block-3 w-inline-block"
                                                               rel="tooltip"
                                                               title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_FAVORITE_CLICK_HERE_FAV'); ?>"
                                                               onclick="favorite(<?= $contact['link_id']; ?>)"></i>
														<?php endif; ?>
													<?php endif; ?>

                                                    <div class="headsmallcard"></div>
													<?php
													$cardClass = '';
													if ($contact['state'] === '1') {
														if ($contact['direction'] === '-1') {
															$cardClass = 'pending';
														}
                                                        elseif ($contact['direction'] === '1') {
															$cardClass = 'demandecontact';
														}
													}
                                                    elseif ($contact['state'] === '2') {
														$cardClass = 'accepted';
													}
													?>
                                                    <div class="wrapper-small-card-content <?= $cardClass; ?>">
                                                        <div class="w-row">
                                                            <div class="w-col w-col-8">
                                                                <div class="text-block-2"><?= $contact['titre']; ?></div>
                                                            </div>
															<?php if ($contact['direction'] === '-1' && $contact['state'] === '1') : ?>
                                                                <div class="w-col w-col-4">
                                                                    <div class="statuts envoye"><?= JText::_('REQUEST_SENT'); ?></div>
                                                                </div>
															<?php endif; ?>
                                                        </div>
                                                        <div class="div-block">
                                                            <span class="fa fa-public"></span>
                                                            <div class="name">
																<?= $m_cifre->getUserInstitution($contact['applicant_id'])->nom_de_structure; ?>
                                                            </div>
                                                        </div>
                                                        <div class="div-block-mail">
                                                            <div class="w-row">
                                                                <div class="w-col w-col-9">
                                                                    <div class="sujet">
																		<?php if ($contact['status'] === 3) : ?>
																			<?= JText::_('OFFER_UNPUBLISHED'); ?>
																		<?php elseif (!empty($contact['linked_fnum'])) : ?>
                                                                            <a href="consultez-les-offres/details/299/<?= $contact['search_engine_page']; ?>"><?= JText::_('CONSULT_OFFER'); ?></a>
																		<?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="column-2 w-col w-col-3">

																	<?php if ($contact_nb_faves[$contact['link_id']] === 2 && !empty($contact_chat[$contact['link_id']])) : ?>
                                                                        <a href="/index.php?option=com_emundus&view=messages&layout=hesamchatroom&chatroom=<?= $contact_chat[$contact['link_id']]; ?>"
                                                                           class="link w-inline-block">
                                                                            <i class="image-chatroom fa fa-chatroom"></i>
                                                                        </a>
																	<?php endif; ?>

																	<?php if ($contact['notify']) : ?>
                                                                        <i class="fa fa-bell em-bell-button"
                                                                           rel="tooltip"
                                                                           title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_NOTIFY_CLICK_HERE_UNNOTIF'); ?>"
                                                                           onclick="unnotify(<?= $contact['link_id']; ?>)"></i>
																	<?php else : ?>
                                                                        <i class="fa fa-bell-slash-o em-bell-button"
                                                                           rel="tooltip"
                                                                           title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_NOTIFY_CLICK_HERE'); ?>"
                                                                           onclick="notify(<?= $contact['link_id']; ?>)"></i>
																	<?php endif; ?>

                                                                    <a href="/index.php?option=com_emundus&view=messages&layout=chat&chatid=<?= $contact['applicant_id']; ?>"
                                                                       class="link w-inline-block">
                                                                        <i class="image-mail fa <?= ($contact['unread'] == 0) ? 'fa-envelope-o' : 'fa-envelope'; ?>"></i>
                                                                    </a>
                                                                    <div class="notif <?= ($contact['unread'] == 0) ? '_0notif' : ''; ?>">
                                                                        <div class="notif-number <?= ($contact['unread'] == 0) ? '_0notif' : ''; ?>"><?= $contact['unread']; ?></div>
                                                                    </div>

																	<?php if ($contact['direction'] === '1' && $contact['state'] === '1') : ?>
                                                                        <div id="contactButtons-<?= $contact['link_id']; ?>"
                                                                             class="contact-buttons">
                                                                            <div class="accepter"
                                                                                 onclick="reply('<?= $contact['link_id']; ?>')"></div>
                                                                            <div class="refuser"
                                                                                 onclick="breakUp('<?= $contact['link_id']; ?>')"></div>
                                                                        </div>
																	<?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
											<?php endforeach; ?>
                                        </div>
									<?php endif; ?>
                                </div>
							<?php endif; ?>
                        </div>
                    </div>
				<?php endforeach; ?>
            </div>
		<?php endif; ?>

		<?php if (!empty($chat_requests)) : ?>
            <div class="wrapper-big-car relations-card">
                <div class="headerbig-card header-gray">
                    <div class="div-block-3">
                        <span class="fa fa-comments-o"></span>
                        <div class="text-block-2">&nbsp;<?= JText::_('YOUR_RELATIONS'); ?></div>
                    </div>
                </div>
                <div class="big-card">
					<?php foreach ($chat_requests as $chat_request) : ?>

						<?php
						// Here we are checking if a double favorite has been made in the other direction.
						// If two favorites are present, display chat icon.
						$contact_favorites[$chat_request['link_id']] = $m_cifre->checkForTwoFavorites($chat_request['linked_fnum'], (int) substr($chat_request['linked_fnum'], -7));
						$contact_nb_faves[$chat_request['link_id']]  = count($contact_favorites[$chat_request['link_id']]);
						if ($contact_nb_faves[$chat_request['link_id']] === 2) {

							$m_messages = new EmundusModelMessages();

							$favorite_users = [];
							// Here we get the two users from our favorites who are not us.
							foreach ($contact_favorites[$chat_request['link_id']] as $fav) {
								$favorite_users[] = $fav->user_from;
								$favorite_users[] = $fav->user_to;
							}

							$favorite_users = array_values(array_unique($favorite_users));

							// Look up the chatroom id using our three users.
							if (in_array($user->id, $favorite_users)) {
								$contact_chat[$chat_request['link_id']] = $m_messages->getChatroomByUsers($favorite_users[0], $favorite_users[1], $favorite_users[2]);
							}
						}

						?>

                        <div class="wrapper-big-card" id="card-<?= $chat_request['link_id']; ?>">
                            <div class="card w-clearfix">
                                <a href="#" class="star link-block-3 w-inline-block" data-ix="star"><img
                                            src="https://assets.website-files.com/5e9eea59278d0a02df79f6bd/5ea32c2fd949eca178361a94_star.svg"
                                            alt="" class="image-8"></a>
                                <div class="headsmallcard"></div>
                                <div class="wrapper-small-card-content <?= ($chat_request['state'] === '1') ? 'pending' : 'accepted'; ?>">
                                    <div class="w-row">
                                        <div class="w-col w-col-8">
                                            <div class="div-block-3">
                                                <img src="https://assets.website-files.com/5e9eea59278d0a02df79f6bd/5ebbe1db264db9696201c765_5eaad27c076841830de7d513_5e9f6bfa9fb16576de7aa78d_5e9ef4871565a65129befc4c_Twiice2-%20Plan%20de%20travail%201.svg"
                                                     alt="" class="image-9">
                                                <div>
                                                    <div class="small-explantation">Votre contact -
                                                        <em><?= ($chat_request['state'] === '1') ? JText::_('REQUEST_SENT') : JText::_('ACCEPTED'); ?></em>
                                                    </div>
                                                    <div class="text-block-2"><?= $chat_request['titre']; ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="column-8 w-col w-col-4"></div>
                                    </div>
                                    <div class="div-block-contact">
                                        <img src="https://assets.website-files.com/5e9eea59278d0a02df79f6bd/5e9ef4873152d535b204da4b_Twiice%20-%20Plan%20de%20travail%201.svg"
                                             alt="" class="image">
                                        <div class="name">
											<?= JFactory::getUser($chat_request['applicant_id'])->name; ?>
                                        </div>
                                    </div>
                                    <div class="div-block-mail">
                                        <div class="w-row">
                                            <div class="column-9 w-col w-col-9">
                                                <div class="sujet">
                                                    <a href="consultez-les-offres/details/299/<?= $chat_request['search_engine_page']; ?>"><?= JText::_('CONSULT_OFFER'); ?></a>
                                                </div>
                                            </div>
                                            <div class="column-2 w-col w-col-3">

												<?php if ($contact_nb_faves[$chat_request['link_id']] === 2 && !empty($contact_chat[$chat_request['link_id']])) : ?>
                                                    <a href="/index.php?option=com_emundus&view=messages&layout=hesamchatroom&chatroom=<?= $contact_chat[$chat_request['link_id']]; ?>"
                                                       class="link w-inline-block">
                                                        <i class="image-chatroom fa fa-chatroom"></i>
                                                    </a>
												<?php endif; ?>

												<?php if ($chat_request['state'] === '2') : ?>
													<?php if ($chat_request['notify']) : ?>
                                                        <i class="fa fa-bell em-bell-button" rel="tooltip"
                                                           title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_NOTIFY_CLICK_HERE_UNNOTIF'); ?>"
                                                           onclick="unnotify(<?= $chat_request['link_id']; ?>)"></i>
													<?php else : ?>
                                                        <i class="fa fa-bell-slash-o em-bell-button" rel="tooltip"
                                                           title="<?= JText::_('MOD_EMUNDUS_APPLICATIONS_NOTIFY_CLICK_HERE'); ?>"
                                                           onclick="notify(<?= $chat_request['link_id']; ?>)"></i>
													<?php endif; ?>
												<?php endif; ?>

                                                <a href="/index.php?option=com_emundus&view=messages&layout=chat&chatid=<?= $chat_request['applicant_id']; ?>"
                                                   class="link w-inline-block">
                                                    <i class="image-mail fa <?= ($chat_request['unread'] == 0) ? 'fa-envelope-o' : 'fa-envelope'; ?>"></i>
                                                </a>
                                                <div class="notif <?= ($chat_request['unread'] == 0) ? '_0notif' : ''; ?>">
                                                    <div class="notif-number <?= ($chat_request['unread'] == 0) ? '_0notif' : ''; ?>"><?= $chat_request['unread']; ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="contactButtons-<?= $chat_request['link_id']; ?>"
                                         onclick="breakUp('<?= $chat_request['link_id']; ?>')">
                                        <img src="https://assets.website-files.com/5e9eea59278d0a02df79f6bd/5ebbe17210aa833dc56beaea_5e9f03ced3a57f18c49bad26_5e9ef4873152d535b204da4b_Twiice%20-%20Plan%20de%20travail%201.svg"
                                             alt="" class="image-delete-smallcard">
                                    </div>
                                </div>
                            </div>
                        </div>
					<?php endforeach; ?>
                </div>
            </div>
		<?php endif; ?>


		<?php if ($show_add_application && $position_add_application > 0 && $applicant_can_renew) : ?>
            <a class="big-card-add w-inline-block" href="<?= JURI::base(); ?>component/fabrik/form/102">
                <div class="ajouter-sujet"><?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></div>
            </a>
		<?php endif; ?>

    </div>
</div>

<script type="text/javascript">

    function deletefile(fnum) {

        Swal.fire({
            customClass: {
                title: "heading no-dash",
                confirmButton: 'button-2 w-button',
                cancelButton: 'button-2 w-button'
            },
            title: '<?= JText::_('MOD_EMUNDUS_APPLICATIONS_CONFIRM_DELETE_FILE'); ?>',
            icon: 'warning',
            showCancelButton: true,
            showConfirmButton: true,
            reverseButtons: true,
            cancelButtonText: '<?= JText::_('BACK'); ?>'
        }).then(confirm => {
            if (confirm.value) {
                document.location.href = "index.php?option=com_emundus&task=deletefile&fnum=" + fnum + "&redirect=<?= base64_encode($uri->getPath()); ?>";
            }
        });
    }

    function publishfile(fnum) {
        Swal.fire({
            customClass: {
                title: "heading no-dash"
            },
            title: '<?= JText::_('MOD_EMUNDUS_APPLICATIONS_CONFIRM_PUBLISH_FILE'); ?>',
            icon: 'warning',
            showCloseButton: true
        }).then(confirm => {
            if (confirm.value) {
                document.location.href = "index.php?option=com_emundus&task=completefile&status=1&fnum=" + fnum + "&redirect=<?= base64_encode($uri->getPath()); ?>";
            }
        });
    }

    function unpublishfile(fnum) {
        Swal.fire({
            customClass: {
                title: "heading no-dash"
            },
            title: '<?= JText::_('MOD_EMUNDUS_APPLICATIONS_CONFIRM_UNPUBLISH_FILE'); ?>',
            icon: 'warning',
            showCloseButton: true
        }).then(confirm => {
            if (confirm.value) {
                document.location.href = "index.php?option=com_emundus&task=completefile&status=7&fnum=" + fnum + "&redirect=<?= base64_encode($uri->getPath()); ?>";
            }
        });
    }

    function completefile(fnum, trash) {

        const trashButton = trash ? "<a href=\"index.php?option=com_emundus&task=completefile&status=7&fnum=" + fnum + "&redirect=<?= base64_encode($uri->getPath()); ?>\" class=\"cta-offre w-inline-block\"><?= JText::_('MOD_EMUNDUS_APPLICATIONS_UNPUBLISH_FILE'); ?></a>" : "";

        Swal.fire({
            customClass: {
                title: "heading no-dash"
            },
            title: '<?= JText::_('MOD_EMUNDUS_APPLICATIONS_CONFIRM_COMPLETE_FILE'); ?>',
            html: trashButton +
                "<a href=\"index.php?option=com_emundus&task=completefile&status=2&fnum=" + fnum + "&redirect=<?= base64_encode($uri->getPath()); ?>\" class=\"cta-offre w-inline-block\"><?= JText::_('FILE_BOOKED_WITH_HESAM'); ?></a>" +
                "<a href=\"index.php?option=com_emundus&task=completefile&status=5&fnum=" + fnum + "&redirect=<?= base64_encode($uri->getPath()); ?>\" class=\"cta-offre w-inline-block\"><?= JText::_('FILE_BOOKED_WITHOUT_HESAM'); ?></a>",
            icon: 'warning',
            showConfirmButton: false,
            showCloseButton: true
        });
    }

    function reply(id) {

        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?option=com_emundus&controller=cifre&task=replybyid',
            data: {id: id},
            beforeSend: () => {
                document.getElementById('contactButtons-' + id).innerHTML = '...';
            },
            success: result => {
                if (result.status) {

                    // When we successfully change the status, we simply dynamically change the button.
                    document.getElementById('contactButtons-' + id).outerHTML = '';

                    let cardClass = document.querySelector('#card-' + id + ' .demandecontact');
                    if (typeof cardClass !== 'undefined') {
                        cardClass.classList.remove('demandecontact');
                        cardClass.classList.add('accepted');
                    }

                    Swal.fire({
                        icon: 'success',
                        text: '<?= JText::_('OFFER_ACCEPTED'); ?>'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        text: result.msg
                    });
                }
            },
            error: jqXHR => {
                console.log(jqXHR.responseText);
            }
        });
    }

    /**
     *
     * @param id
     */
    function breakUp(id) {

        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?option=com_emundus&controller=cifre&task=breakupbyid&action=ignore',
            data: {id: id},
            beforeSend: () => {
                document.getElementById('contactButtons-' + id).innerHTML = '...';
            },
            success: result => {
                if (result.status) {

                    // Dynamically change the button back to the state of not having a link.
                    document.getElementById('card-' + id).outerHTML = '';

                } else {
                    Swal.fire({
                        icon: 'error',
                        text: result.msg
                    });
                }
            },
            error: jqXHR => {
                console.log(jqXHR.responseText);
            }
        });
    }

    /**
     *
     * @param name
     * @param desc
     */
    function share(name, desc) {

        const addressed = ' - une offre de thèse financée, adressé' +
            '<?= ($application->profile_id !== '1006') ? ' aux futurs doctorants' : ''; ?>' +
            '<?= ($application->profile_id !== '1006' && $application->profile_id !== '1007') ? ' et' : ''; ?><?= ($application->profile_id !== '1007') ? ' aux cherchers(ses)' : ''; ?>' +
            '<?= ($application->profile_id !== '1008') ? ' et aux collectivités' : ''; ?>' +
            '.';

        const link = '<?= JUri::base(); ?>consultez-les-offres';

        let offer = name + ' - ' + desc;
        if (offer.length + addressed.length + link.length > 280) {
            offer = offer.substring(0, 260 - (addressed.length + link.length)) + '...';
        }
        const text = offer + addressed;

        Swal.fire({
            customClass: {
                title: "heading no-dash"
            },
            title: '<?= JText::_('MOD_EMUNDUS_APPLICATIONS_SHARE_OFFER'); ?>',
            html: '<a href="https://twitter.com/intent/tweet?url=' + encodeURIComponent(link) + '&text=' + encodeURIComponent(text) + '" class="twitter-button cta-offre w-inline-block" target="_blank">Twitter</a>' +
                '<a href="https://www.facebook.com/sharer.php?u=' + encodeURIComponent(link) + '" class="fb-button cta-offre w-inline-block" target="_blank">Facebook</a>' +
                '<a href="https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(link) + '&summary=' + text.replace(/ /g, "+") + '" class="linkedin-button cta-offre w-inline-block" target="_blank">LinkedIn</a>',
            showCloseButton: true,
            showConfirmButton: false
        });
    }

    /**
     *
     * @param link_id
     */
    function favorite(link_id) {

        const star_icon = document.querySelector('#card-' + link_id + ' .em-star-button');
        const other_fav = star_icon.parentElement.parentElement.querySelector('.fa-star');

        jQuery.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=cifre&task=favorite',
            data: {
                link_id: link_id
            },
            beforeSend: function () {
                star_icon.classList.add('fa-spin');
            },
            success: function (result) {

                star_icon.classList.remove('fa-spin');

                result = JSON.parse(result);
                if (result.status) {
                    iconate(star_icon, {
                        from: 'fa-star-o',
                        to: 'fa-star',
                        animation: 'rotateClockwise'
                    });
                    star_icon.setAttribute('onclick', 'unfavorite(' + link_id + ')');
                    star_icon.setAttribute('id', 'favorite-' + link_id);
                    star_icon.setAttribute('data-original-title', '<?= JText::_('MOD_EMUNDUS_APPLICATIONS_FAVORITE_CLICK_HERE_UNFAV');?>');

                    if (other_fav) {
                        const other_fav_link_id = other_fav.id.split("-").pop();
                        iconate(other_fav, {
                            from: 'fa-star',
                            to: 'fa-star-o',
                            animation: 'rotateClockwise'
                        });
                        other_fav.setAttribute('onclick', 'favorite(' + other_fav_link_id + ')');
                        other_fav.removeAttribute('id');
                        other_fav.setAttribute('data-original-title', '<?= JText::_('MOD_EMUNDUS_APPLICATIONS_FAVORITE_CLICK_HERE_FAV');?>');
                    }

                    if (result.reload) {
                        window.location.reload();
                    }

                } else {
                    star_icon.style.color = '#d91e18';
                }
            },
            error: function (jqXHR) {
                star_icon.classList.remove('fa-spin');
                star_icon.style.color = '#d91e18';
                console.log(jqXHR.responseText);
            }
        });
    }


    /**
     *
     * @param link_id
     */
    function unfavorite(link_id) {

        const star_icon = document.querySelector('#card-' + link_id + ' .em-star-button');

        jQuery.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=cifre&task=unfavorite',
            data: {
                link_id: link_id
            },
            beforeSend: function () {
                star_icon.classList.add('fa-spin');
            },
            success: function (result) {

                star_icon.classList.remove('fa-spin');

                result = JSON.parse(result);
                if (result.status) {
                    iconate(star_icon, {
                        from: 'fa-star',
                        to: 'fa-star-o',
                        animation: 'rotateClockwise'
                    });
                    star_icon.setAttribute('onclick', 'favorite(' + link_id + ')');
                    star_icon.removeAttribute('id');
                    star_icon.setAttribute('data-original-title', '<?= JText::_('MOD_EMUNDUS_APPLICATIONS_FAVORITE_CLICK_HERE_FAV');?>');
                } else {
                    star_icon.style.color = '#d91e18';
                }
            },
            error: function (jqXHR) {
                star_icon.classList.remove('fa-spin');
                star_icon.style.color = '#d91e18';
                console.log(jqXHR.responseText);
            }
        });
    }


    /**
     *
     * @param link_id
     */
    function notify(link_id) {

        const bell_icon = document.querySelector('#card-' + link_id + ' .em-bell-button');

        jQuery.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=cifre&task=notify',
            data: {
                link_id: link_id
            },
            success: function (result) {

                result = JSON.parse(result);
                if (result.status) {
                    iconate(bell_icon, {
                        from: 'fa-bell-slash-o',
                        to: 'fa-bell',
                        animation: 'horizontalFlip'
                    });
                    bell_icon.setAttribute('onclick', 'unnotify(' + link_id + ')');
                    bell_icon.setAttribute('data-original-title', '<?= JText::_('MOD_EMUNDUS_APPLICATIONS_NOTIFY_CLICK_HERE_UNNOTIF');?>');

                } else {
                    bell_icon.style.color = '#d91e18';
                }
            },
            error: function (jqXHR) {
                bell_icon.style.color = '#d91e18';
                console.log(jqXHR.responseText);
            }
        });
    }


    /**
     *
     * @param link_id
     */
    function unnotify(link_id) {

        const bell_icon = document.querySelector('#card-' + link_id + ' .em-bell-button');

        jQuery.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=cifre&task=unnotify',
            data: {
                link_id: link_id
            },
            success: function (result) {

                result = JSON.parse(result);
                if (result.status) {
                    iconate(bell_icon, {
                        from: 'fa-bell',
                        to: 'fa-bell-slash-o',
                        animation: 'horizontalFlip'
                    });
                    bell_icon.setAttribute('onclick', 'notify(' + link_id + ')');
                    bell_icon.setAttribute('data-original-title', '<?= JText::_('MOD_EMUNDUS_APPLICATIONS_NOTIFY_CLICK_HERE');?>');
                } else {
                    bell_icon.style.color = '#d91e18';
                }
            },
            error: function (jqXHR) {
                bell_icon.style.color = '#d91e18';
                console.log(jqXHR.responseText);
            }
        });
    }

</script>
