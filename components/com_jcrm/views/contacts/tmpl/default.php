<?php
/**
 * @version     1.0.0
 * @package     com_jcrm
 * @copyright   Copyright (C) 2014. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      Décision Publique <dev@emundus.fr> - http://www.emundus.fr
 */
// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHTML::_('script', 'system/multiselect.js', false, true);
// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('media/com_emundus/lib/bootstrap-emundus/css/bootstrap.min.css');
$document->addStyleSheet(JURI::base()."media/jui/css/chosen.min.css");
$document->addScript(JURI::base() . 'media/jui/js/jquery.min.js');
$document->addScript(JURI::base()."media/jui/js/chosen.jquery.min.js");
$document->addScript('media/editors/tinymce4/tinymce.min.js');
$document->addScript('media/com_jcrm/js/angular.js');
$document->addScript('media/com_jcrm/js/angular-ui-tinymce/tinymce.js');
$document->addScript('media/com_jcrm/js/ui-bootstrap-tpls-0.12.0.min.js');
$document->addScript('media/com_jcrm/js/ui-utils.min.js');
$document->addScript('media/com_jcrm/js/ui-utils-ieshiv.min.js');
$document->addScript('media/com_jcrm/js/angular-file-upload/angular-file-upload.min.js');


$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$ordering = ($listOrder == 'a.ordering');
$canCreate = $user->authorise('core.create', 'com_jcrm');
$canEdit = $user->authorise('core.edit', 'com_jcrm');
$canCheckin = $user->authorise('core.manage', 'com_jcrm');
$canChange = $user->authorise('core.edit.state', 'com_jcrm');
$canDelete = $user->authorise('core.delete', 'com_jcrm');
?>
<style>
ul li {
  display: block;
}
</style>

<div class="app-container" id="app-container" ng-app="contactApp">
	<div class="fluid-container main" ng-controller="contactsController">
		<div class="row main-row">
			<div class="col-md-2 ct-list contact">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h3 class="panel-title"><?= JText::_('CONTACT_GROUPS'); ?></h3>
						<div ng-click="addGroup()" class="btn btn-primary btn-xs pull-right">
							<span class="glyphicon glyphicon-plus-sign"></span>
						</div>
					</div>
					<div class="panel-body">
						<ul class="contact-group">
							<li class="contact-group-item" ng-click="getContacts(0)"  ng-class="{'bg-warning': (0 == groupSelected)}" >
								<a href="#" class="row">
									<?= JText::_('CONTACT_GROUP_ALL'); ?>
								</a>
							</li>
							<li class="contact-group-item" ng-click="getContacts(group.id)"  ng-class="{'bg-warning': (group.id == groupSelected)}" ng-repeat="group in groups  track by $index " >
								<div class="form-group" ng-show="group.edit || (group.id == 0)" >
									<input type="text" ui-keyup="{13: 'saveGroup($index, $event)'}"  class="form-control group-input" ng-model="group.name" placeholder="<?= JText::_('CONTACT_GROUP_NAME'); ?>"/>
									<span class="glyphicon glyphicon-ok green" ng-click="saveGroup($index)"></span>
									<span class="glyphicon glyphicon-remove red" ng-click="cancelGroup($index)"></span>
								</div>
								<div ng-show="group.id != 0 && !group.edit" class="contact-group-detail">
									<a href="#" class="row">
										{{group.name}}
									</a>
									<div class="pull-right actions-group-name">
										<span class="glyphicon glyphicon-edit blue" ng-click="editGroup($index)"></span>
										<span class="glyphicon glyphicon-remove red" ng-click="deleteGroup($index, group.id)"></span>
									</div>
								</div>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-md-3 ct-list contact">
				<div class="panel panel-info">
					<div class="panel-heading">
						<input type="text" ng-model="search" id="em-jcrm-search"  ui-event="{keyup:'searchContact($event)'}" placeholder="<?= JText::_('CONTACT_SEARCH_CONTACT'); ?>"  class="form-control group-input"/>
						<a href="#" ng-click="showForm()" class="btn btn-primary btn-xs pull-right">
							<span class="glyphicon glyphicon-plus-sign"></span>
						</a>
						<a href="#" ng-click="switchSearch()" class="btn btn-primary btn-xs pull-right">
							<img ng-show="!searchType" class="img-circle img-thumbnail" ng-src="/media/com_jcrm/images/contacts/user.svg" width="20px" alt="...">
							<img ng-show="searchType"  class="img-circle img-thumbnail" ng-src="/media/com_jcrm/images/contacts/org.svg" width="20px" alt="...">
						</a>
					</div>
					<div class="panel-body" id="contact-list-panel">
						<ul class="contact-list" jcrm-scroll>
							<li class="contact-list-item" ng-class="{'bg-warning': (c.id == contact.id)}" ng-repeat="c in contacts | orderBy:'full_name'" ng-click="getContact(c.id)">
								<a href="#" class="row">
                                    <span>
										<img ng-show="c.type == '0'" class="img-circle img-thumbnail" ng-src="/media/com_jcrm/images/contacts/user.svg" width="25px" alt="...">
										<img ng-show="c.type == '1'" class="img-circle img-thumbnail" ng-src="/media/com_jcrm/images/contacts/org.svg" width="25px" alt="...">   {{c.full_name | limitTo: 70}}
									</span>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-md-7 ct-view contact">
				<div class="panel panel-info" ng-controller="ModalDemoCtrl">
					<div class="panel-heading">
						<h3 class="panel-title"><?= JText::_('CONTACT_CONTACT_INFO'); ?></h3>
						<div class="btn btn-default btn xs pull-right"  ng-click="open('', contact, groupSelected, 'vcard')">
                            <span class="glyphicon glyphicon-download-alt"></span>
                        </div>
						<div class="btn btn-default btn xs pull-right"  ng-click="open('lg', contact, groupSelected, 'mail')">
                            <span class="glyphicon glyphicon-envelope"></span>
                        </div>
						<div class="actions pull-right" ng-show="contact.id && !formVisible">
							<button ng-click="showForm(contact.id)" class = "btn btn-primary btn-xs"><?= JText::_('CONTACT_EDIT'); ?></button>
							<button ng-click="delete(contact.id)" class = "btn btn-default btn-xs"><?= JText::_('CONTACT_DELETE'); ?></button>
						</div>
						<div class="actions pull-right" ng-show="formVisible">
							<button type="submit" class="btn btn-primary" ng-click="save(newContact)"><?= JText::_('CONTACT_SAVE'); ?></button>
							<button class="btn btn-default" ng-click="hideForm()"><?= JText::_('CONTACT_CANCEL'); ?></button>
						</div>
					</div>
					<div class="panel-body">
						<div class="my-alt-dime" ng-show="dimeBody"></div>
						<div class="contact-main">
							<div class="col-md-10 contact-card" ng-show="contact.id && !formVisible">
								<div class="row hero-row">
									<div class="col-md-2 ct-type">
										<img class="img-circle img-thumbnail" ng-src="{{contact.photo.uri}}" width="100px" alt="...">
									</div>
									<div class="col-md-8 contact-hero">
										<div class="orga" ng-show="contact.organisation">
											<h3>
                                                {{contact.full_name}}<br/>
												<small ng-show="contact.type == 0">
													{{contact.organisation}}
												</small>
											</h3>
										</div>
										<div class="orga" ng-show="!contact.organisation">
											<h3>
												{{contact.last_name | uppercase}} {{contact.first_name}}
											</h3>
										</div>
									</div>
								</div>
								<hr/>
								<div class="contact-body">
									<H4> <?= JText::_('COM_JCRM_FORM_LBL_CONTACT_PHONE'); ?> </H4>
									<div class="row" ng-repeat="phone in contact.phone">
										<div class="col-md-2 ct-type">
											{{arrayText[phone.type]}}:
										</div>
										<div class="col-md-8">
											{{phone.tel}}
										</div>
									</div>
									<hr/>
									<H4> <?= JText::_('COM_JCRM_FORM_LBL_CONTACT_EMAIL'); ?> </H4>
									<div class="row" ng-repeat="email in contact.email">
										<div class="col-md-2 ct-type">
											{{arrayText[email.type]}}:
										</div>
										<div class="col-md-8">
											{{email.uri}}
										</div>
									</div>
									<hr/>
									<H4> <?= JText::_('CONTACT_ADDRESS_HEADER'); ?> </H4>
									<div class="row" ng-repeat="address in contact.adr">
										<div class="col-md-12">
											<div class="row">
												<div class="col-md-2 ct-type">
													{{arrayText[address.type]}}:
												</div>
												<div class="col-md-8">
													{{address.array[0]}} <br ng-show="address.array[0] != ''">
                                                    {{address.array[1]}} {{address.array[2]}} <br ng-show="address.array[1] != '' && address.array[2] != ''">
													{{address.array[3]}}
												</div>
											</div>
										</div>
									</div>
									<hr/>
									<div class="row">
										<div class="col-md-2 ct-type">
											<?= JText::_('CONTACT_INFOS_LABEL'); ?>:
										</div>
										<div class="col-md-8">
											{{contact.infos}}
										</div>
									</div>
									<hr/>
									<div class="row" ng-repeat="other in contact.other">
										<div class="col-md-2 ct-type">
											{{other.type}}:
										</div>
										<div class="col-md-8">
											{{other.value}}
										</div>
									</div>
									<hr ng-show="contact.contacts.length > 0 || contact.groups.length > 0" />
									<div class="row" ng-show="contact.contacts.length > 0">
										<div class="col-md-2 ct-type">
											<?= JText::_('CONTACT_ORG_USER_HEADER'); ?>
										</div>
										<div class="col-md-8">
											<ul class="contact-view-list">
												<li class="contact-view-item"  ng-repeat="c in contact.contacts | orderBy:'c.full_name'">
													<div class="col-md-6">
														<a href="#" ng-click="getContact(c.id)">{{c.full_name}}</a>
													</div>
													<div class="col-md-6">
														{{c.email}}
													</div>
												</li>
											</ul>
											<hr/>
										</div>
									</div>

									<div class="row" ng-show="contact.groups.length > 0">
										<div class="col-md-2 ct-type">
											<?= JText::_('CONTACT_GROUP_USER_HEADER'); ?>
										</div>
										<div class="col-md-8">
											<ul class="contact-view-list">
												<li class="contact-view-item" ng-repeat="gr in contact.groups track by gr.id">
													{{gr.name}}
												</li>
											</ul>
											<hr/>
										</div>
									</div>
								</div>
							</div>
							<form ng-show="formVisible" name="newContact" novalidate  class="form-horizontal col-md-10">
								<div class="contact-head-block">
									<div class="form-group">
										<div class="col-md-2">
											<img ng-show="!contact.type" class="img-circle img-thumbnail" ng-src="/media/com_jcrm/images/contacts/user.svg" width="100px" alt="...">
											<img ng-show="contact.type" class="img-circle img-thumbnail" ng-src="/media/com_jcrm/images/contacts/org.svg" width="100px" alt="...">
										</div>
										<div class="col-md-8">
											<div class="form-group" ng-show="!contact.type">
												<div class="col-md-6" >
													<input ng-required="!contact.type" type="text" ng-model="contact.last_name" class="form-control jcrm-input" id="inputLn" name="inputLn" placeholder="<?= JText::_('CONTACT_LAST_NAME'); ?>" />
													<div ng-show="newContact.$submitted || newContact.inputLn.$touched">
														<div ng-show="newContact.inputLn.$error.required" class="text-danger"><?= JText::_('CONTACT_REQUIRED'); ?></div>
													</div>
												</div>
												<div class="col-md-6">
													<input type="text" ng-model="contact.first_name" class="form-control" id="inputFn" name="inputFn" placeholder="<?= JText::_('CONTACT_FIRST_NAME'); ?>"/>
												</div>
											</div>
											<div class="form-group">
												<div ng-controller="TypeaheadCtrl">
													<div class="col-md-12" ng-show="!contact.type">
														<input type="text" ng-model="contact.organisation" placeholder="<?= JText::_('CONTACT_ORGANISATION_NAME'); ?>" typeahead="organisation for organisation  in getLocation($viewValue)" typeahead-loading="loadingLocations" class="form-control"/>
														<i ng-show="loadingLocations" class="glyphicon glyphicon-refresh"></i>
													</div>
													<div ng-show="contact.type" class="col-md-12">
														<input class="form-control"  type="text" name="inputOrg" id="inputOrg" ng-model="contact.organisation" placeholder="<?= JText::_('CONTACT_ORGANISATION_NAME'); ?>" ng-required="contact.type"/>
														<div ng-show="contact.type && (newContact.$submitted || newContact.inputOrg.$touched)" >
															<div ng-show="newContact.inputOrg.$error.required" class="text-danger"><?= JText::_('CONTACT_REQUIRED'); ?></div>
														</div>
													</div>
													<div class="col-md-12">
														<label for="contact-type">
															<input type="checkbox" name="contact-type" id="contact-type" value="1" ng-model="contact.type"/>
															<?= JText::_('CONTACT_ORGANISATION'); ?>
														</label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="contact-form-fields">
									<div class="row">
										<h5><?= JText::_('CONTACT_PHONE_HEADER'); ?></h5>
										<div class="col-md-2">
											<div class="btn btn-default" ng-click="addField('phone')">
												<span class="glyphicon glyphicon-plus"></span>
											</div>
										</div>
										<div class="jcrm-field col-md-10" ng-class="{'col-md-offset-2': $index > 0}" ng-repeat="phone in contact.phone track by $index">
											<div class="col-md-2">
												<select name="" id="" class="form-control" ng-model="phone.type">
													<option value="work"><?= JText::_('CONTACT_WORK'); ?></option>
													<option value="home"><?= JText::_('CONTACT_HOME'); ?></option>
													<option value="fax"><?= JText::_('CONTACT_FAX'); ?></option>
													<option value="cell"><?= JText::_('CONTACT_CELL'); ?></option>
												</select>
											</div>
											<div class="col-md-9">
												<input class="form-control" placeholder="0600000000" type="text" ng-model="phone.tel" name="contact-phone" id="contact-phone"/>
												<div class="btn btn-danger">
													<span class="glyphicon glyphicon-remove" ng-click="deleteField($index, 'phone')"></span>
												</div>
											</div>
										</div>
									</div>
                                    <hr>
									<div class="row">
										<h5><?= JText::_('CONTACT_EMAIL_HEADER'); ?></h5>
										<div class="col-md-2">
											<div class="btn btn-default" ng-click="addField('email')">
												<span class="glyphicon glyphicon-plus"></span>
											</div>
										</div>
										<div class="jcrm-field col-md-10" ng-class="{'col-md-offset-2': $index > 0}" ng-repeat="email in contact.email track by $index">
											<div class="col-md-2">
												<select ng-init="contact.email.type='work'" name="" id="" class="form-control" ng-model="email.type">
													<option value="work"><?= JText::_('CONTACT_WORK'); ?></option>
													<option value="home"><?= JText::_('CONTACT_HOME'); ?></option>
												</select>
											</div>
											<div class="col-md-9">
												<input type="email" ng-model="email.uri" class="form-control" id="inputEmail" name="inputEmail" placeholder="<?= JText::_('CONTACT_EMAIL'); ?>">
												<div class="btn btn-danger">
													<span class="glyphicon glyphicon-remove" ng-click="deleteField($index, 'email')"></span>
												</div>
												<div ng-show="newContact.$submitted || newContact.inputEmail.$touched">
													<div ng-show="newContact.inputEmail.$error.email" class="text-danger"><?= JText::_('CONTACT_INVALID_EMAIL'); ?></div>
												</div>
											</div>
										</div>
									</div>
                                    <hr>
									<div class="row">
										<h5><?= JText::_('CONTACT_ADDRESS_HEADER'); ?></h5>
										<div class="col-md-2">
											<div class="btn btn-default" ng-click="addField('adr')">
												<span class="glyphicon glyphicon-plus"></span>
											</div>
										</div>
										<div class="jcrm-field col-md-10" ng-class="{'col-md-offset-2': $index > 0}" ng-repeat="adr in contact.adr track by $index">
											<div class="col-md-2">
												<select name="" id="" class="form-control" ng-model="adr.type">
													<option value="work"><?= JText::_('CONTACT_WORK'); ?></option>
													<option value="home"><?= JText::_('CONTACT_HOME'); ?></option>
												</select>
											</div>
											<div class="jcrm-field col-md-9">
												<input class="form-control" placeholder="<?= JText::_('CONTACT_ADDRESS'); ?>" ng-model="adr.array[0]" type="text" name="contact-address" id="contact-address"/>
												<div class="btn btn-danger">
													<span class="glyphicon glyphicon-remove" ng-click="deleteField($index, 'adr')"></span>
												</div>
											</div>

											<div>
												<div class="jcrm-field col-md-9 col-md-offset-2">
													<input class="form-control" placeholder="<?= JText::_('CONTACT_ZIPCODE'); ?>" ng-model="adr.array[1]" type="text" name="contact-address" id="contact-address"/>
												</div>
											</div>
											<div>
												<div class="jcrm-field col-md-9 col-md-offset-2">
													<input class="form-control" ng-model="adr.array[2]" placeholder="<?= JText::_('CONTACT_CITY'); ?>"  type="text" name="contact-address" id="contact-address"/>
												</div>
											</div>
											<div>
												<div class="jcrm-field col-md-9 col-md-offset-2">
													<input class="form-control" ng-model="adr.array[3]" placeholder="<?= JText::_('CONTACT_COUNTRY'); ?>"  type="text" name="contact-address" id="contact-address"/>
												</div>
											</div>
										</div>
									</div>
                                    <hr>
									<div class="row">
										<h5><?= JText::_('CONTACT_INFOS_HEADER'); ?></h5>

										<div class = "col-md-2">
										</div>
										<div class="jcrm-field col-md-10">
											<div class="col-md-2">
											</div>
											<div class="col-md-9">
												<textarea name="contact-infos" id="contact-infos" class="form-control add-contact" rows="10" ng-model="contact.infos"></textarea>
											</div>
										</div>
									</div>
                                    <hr>
									<div class="row">
										<h5><?= JText::_('CONTACT_OTHER_HEADER'); ?></h5>

										<div class="col-md-2">
											<div class="btn btn-default" ng-click="addField('other')"><span class="glyphicon glyphicon-plus"></span>
											</div>
										</div>
										<div class="jcrm-field col-md-10" ng-class="{'col-md-offset-2': $index > 0}"  ng-repeat="other in contact.other track by $index">
											<div class="col-md-2">
												<select name="contact-other" class="form-control" id="contact-other" ng-model="contact.other[$index].type">
													<option value="bday"> <?= JText::_('BDAY');?></option>
													<option value="categories"> <?= JText::_('CATEGORIES');?></option>
													<option value="geo"> <?= JText::_('GEO');?></option>
													<option value="mailer"> <?= JText::_('MAILER');?></option>
													<option value="nickname"> <?= JText::_('NICKNAME');?></option>
													<option value="role"> <?= JText::_('ROLE');?></option>
													<option value="source"> <?= JText::_('SOURCE');?></option>
													<option value="title"> <?= JText::_('TITLE');?></option>
													<option value="tz"> <?= JText::_('TZ');?></option>
													<option value="url"> <?= JText::_('URL');?></option>
                                                    <option value="{{contact.other[$index].type}}"> {{contact.other[$index].type}} </option>
												</select>
											</div>
											<div class="col-md-9" >
												<input type="text" class="form-control" name="contact-other-input" ng-model="contact.other[$index].value"/>
												<div class="btn btn-danger">
													<span class="glyphicon glyphicon-remove" ng-click="deleteField($index, 'other')"></span>
												</div>
											</div>
										</div>
									</div>
									<hr>
									<div class="row">
										<h5><?= JText::_('CONTACT_GROUPS_HEADER'); ?></h5>
										<div class="col-md-2"></div>
										<div class="col-md-10">
											<div class="col-md-2"></div>
											<div class="jcrm-field col-md-9">
												<select data-placeholder="<?= JText::_('CONTACT_ADD_TO_GROUP'); ?>"  name="contact-groups" multiple ng-model="contact.formGroup" class="chosen" id="contact-groups">
													<option ng-models="contact.groups" ng-repeat="cgr in groups track by cgr.id"  value = "{{cgr.id}}">{{cgr.name}}</option>
												</select>
											</div>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<div class="contact-modal">
						<script type="text/ng-template" id="myModalContent.html">
							<div class="mail-content" ng-show="from == 0">
								<div class="modal-header">
									<h3 class="modal-title"><?= JText::_('CONTACT_SEND_EMAIL'); ?></h3>
								</div>
								<div class="modal-body">
									<div class="my-alt-dime" ng-show="showDimeModal"></div>

									<div class="row">
										<alert ng-repeat="alert in alerts" type="{{alert.type}}" close="closeAlert($index)">{{alert.msg}}</alert>
									</div>

									<div class="row">
										<div class="col-md-12" ng-controller="TypeaheadCtrl">
											<input type="text" class="form-control" ng-model="contactGuest"  placeholder="<?= JText::_('CONTACT_SEARCH_CONTACT_OR_GROUP'); ?>" typeahead="result.contact for result in getMailContact($viewValue)" typeahead-on-select="onSelect($item, $model, $label)"  typeahead-loading="loadingLocations" />
											<i ng-show="loadingLocations" class="glyphicon glyphicon-refresh"></i>
											<div class="contact-guests">
												<ul>
													<li class="label" ng-class="{'label-info': ct.type == 'contact', 'label-warning': ct.type == 'group'}"  ng-repeat="ct in guestList.items | orderBy: 'ct.name' track by $index" style="margin: 5px;">
														<span>{{ct.contact}}</span>
														<span class="glyphicon glyphicon-remove pullright" ng-click="deleteGuest($index)"></span>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<label for="orgMail"> <?= JText::_('CONTACT_SEND_ORG_GROUP_MAIL'); ?> </label><br>
											<input type="radio" name="orgMail" value="members" ng-model="orgMail"> <?= JText::_('CONTACT_ORG_MEMBERS'); ?> <br>
											<input type="radio" name="orgMail" value="direct" ng-model="orgMail"> <?= JText::_('CONTACT_ORG_DIRECT'); ?> <br>
											<input type="radio" name="orgMail" value="both" ng-model="orgMail"> <?= JText::_('CONTACT_ORG_BOTH'); ?>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<select class="form-control" ng-model="bodyId" name="subject" id="subject" ng-change="getBody()">
												<option value="-1" selected><?= JText::_('CONTACT_CHOOSE_MAIL_TEMPLATE'); ?></option>
												<?php foreach ($this->subjects as $subject) :?>
													<option value="<?= $subject->id?>"><?= $subject->subject; ?></option>
												<?php endforeach; ?>
											</select>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<input class="form-control" type="text" name="mail-subject" id="mail-subject" ng-model="subject"/>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<contact-tiny-mce ng-model="body"></contact-tiny-mce>
										</div>
									</div>
								</div>
							</div>
							<div class="export-vcard" ng-show="from == 1">
								<div class="modal-header">
									<h3 class="modal-title"><?= JText::_('CONTACT_EXPORT'); ?></h3>
								</div>
								<div class="modal-body">
									<div class="my-alt-dime" ng-show="showDimeModal"></div>
									<div class="row">
										<alert ng-repeat="alert in alerts" type="{{alert.type}}" close="closeAlert($index)">{{alert.msg}}</alert>
										<a href="{{dlButton.link}}" class="btn btn-success" target="_blank" ng-show="dlButton.link">{{dlButton.linkMsg}}</a>
									</div>

									<div class="row">
										<div class="col-md-12" ng-controller="TypeaheadCtrl">
											<input type="text" class="form-control" ng-model="contactGuest" placeholder="<?= JText::_('CONTACT_SEARCH_CONTACT_OR_GROUP'); ?>" typeahead="result.contact for result in getMailContact($viewValue)" typeahead-on-select="onSelect($item, $model, $label)" typeahead-loading="loadingLocations" />
											<i ng-show="loadingLocations" class="glyphicon glyphicon-refresh"></i>
											<div class="contact-guests">
												<ul>
													<li class="label" ng-class="{'label-info': ct.type == 'contact', 'label-warning': ct.type == 'group'}" ng-repeat="ct in guestList.items | orderBy: 'ct.name' track by $index" style="margin: 5px;">
														<span>{{ct.contact}}</span>
														<span class="glyphicon glyphicon-remove pullright" ng-click="deleteGuest($index)"></span>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<label for="orgExport"> <?= JText::_('CONTACT_ORG_EXPORT'); ?> </label> <br>
											<input type="radio" name="orgExport" value="members" ng-model="orgExport"> <?= JText::_('CONTACT_ORG_MEMBERS'); ?> <br>
											<input type="radio" name="orgExport" value="direct" ng-model="orgExport"> <?= JText::_('CONTACT_ORG_DIRECT'); ?> <br>
											<input type="radio" name="orgExport" value="both" ng-model="orgExport"> <?= JText::_('CONTACT_ORG_BOTH'); ?>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<select class="form-control" name="contact-export-select" ng-model="export.type" id="contact-export-select">
												<option value="0"><?= JText::_('CONTACT_EXPORT_CSV'); ?></option>
												<option value="1"><?= JText::_('CONTACT_EXPORT_VCARD'); ?></option>
											</select>
										</div>
									</div>
								</div>
							</div>

							<div class="modal-footer">
								<button class="btn btn-primary" ng-click="ok()"><?= JText::_('CONTACT_SEND'); ?></button>
								<button class="btn btn-default" ng-click="cancel()"><?= JText::_('CONTACT_CANCEL'); ?></button>
							</div>
						</script>
					</div>
				</div>
			</div>
		</div>
		<div class="foot-row row">
			<div class="col-md-2"></div>
			<div class="col-md-2">
				<span>{{nbContacts}}</span> <span> contacts</span>
			</div>
		</div>
	</div>
</div>

<?php
$document->addStyleSheet('media/com_jcrm/css/contact.css');
$document->addScript('media/com_jcrm/js/app.js')
?>
<script type="text/javascript">
    if (typeof jQuery == 'undefined') {
        var headTag = document.getElementsByTagName("head")[0];
        var jqTag = document.createElement('script');
        jqTag.type = 'text/javascript';
        jqTag.src = '//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js';
        jqTag.onload = jQueryCode;
        headTag.appendChild(jqTag);
    } else {
        jQueryCode();
    }

    function jQueryCode() {
        jQuery('.delete-button').click(function () {
            var item_id = jQuery(this).attr('data-item-id');
            if (confirm("<?= JText::_('COM_JCRM_DELETE_MESSAGE'); ?>")) {
                window.location.href = '<?= JRoute::_('index.php?option=com_jcrm&task=contactform.remove&id=', false, 2) ?>' + item_id;
            }
        });
    }
</script>