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
$document->addScript('media/com_jcrm/js/angular.js');
$document->addScript('media/com_jcrm/js/ui-bootstrap-tpls-0.12.0.min.js');

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

<div class="app-container" ng-app="syncApp">
	<div ng-controller="mainCtrl">
		<button class="btn btn-primary" ng-click="validAll()"><?= JText::_('JCRM_SYNC_VALID_ALL')?></button>
		<button class="btn btn-primary ignore" ng-click="ignoreAll()"><?= JText::_('JCRM_SYNC_IGNORE_ALL')?></button>
		<div class="my-alt-dime" ng-show="showDime"></div>
		<table class="table table-stripped" >
			<thead>
			<tr>
				<th>
					{{arrayConf.nbItems}} <?= JText::_('JCRM_CONTACT_TO_SYNC')?>
				</th>
			</tr>
			<tr>
				<th></th>
				<th><?= JText::_('JCRM_SYNC_CONTACT') ?></th>
				<th><?= JText::_('JCRM_SYNC_ORGANISATION') ?></th>
			</tr>
			</thead>
			<tbody>
			<tr ng-repeat="ref in contactToSync track by $index">
				<td>
					<span>{{$index + 1}}</span>
					<span class="glyphicon glyphicon-remove red clickable" ng-click="ignoreContact($index)"></span>
				</td>
				<td ng-class="{'bg-danger': (ref.contact.options.length >= 2)}">
					<p>{{ref.contact.lastName}} {{ref.contact.firstName}}</p>
					<p>{{ref.contact.email}}</p>
					<p class="text-warning" ng-hide="ref.orga.synced"><?= JText::_('CHECK_ORGANISATION'); ?></p>
					<div ng-show="ref.contact.synced && ref.orga.synced">
						<p class="text-success"><?= JText::_('CONTACT_IN'); ?></p>
					</div>
					<div ng-show="!ref.contact.synced && ref.orga.synced">
						<select name="select-contact-sync"  ng-model="ref.contact.cId">
							<option value="new"><?= JText::_('NEW_CONTACT'); ?></option>
							<option ng-selected="ref.contact.cId == option.id" value="{{option.id}}" ng-repeat="option in ref.contact.options">
								{{option.full_name}} {{option.email}}
							</option>
						</select>
						<span class="glyphicon glyphicon-ok green clickable" ng-click="validContact($index)"></span>
					</div>
				</td>
				<td ng-class="{'bg-danger': (ref.orga.options.length >= 2)}">
					<h4>{{ref.contact.organisation}}</h4>
					<div ng-show="ref.orga.synced">
						<p class="text-success"><?= JText::_('ORGANISATION_IN_DB'); ?></p>
					</div>
					<div ng-hide="ref.orga.synced">
						<select name="select-orga" id="" ng-model="ref.orga.orgaId">
							<option value="{{option.id}}" ng-selected="option.id == ref.orga.orgaId" ng-repeat="option in ref.orga.options">
								{{option.organisation}}
							</option>
							<option value="new"><?= JText::_('NEW_ACCOUNT'); ?></option>
						</select>
						<span class="glyphicon glyphicon-ok green clickable" ng-click="validOrga($index)"></span>
					</div>
				</td>
			</tr>
			</tbody>

		</table>
		<div ng-controller="PaginationDemoCtrl">
			<pagination total-items="arrayConf.nbItems" ng-model="currentPage" max-size="maxSize" class="pagination-sm"items-per-page="itemsPerPage" boundary-links="true" rotate="true" num-pages="numPages" previous-text="<?= JText::_('PREVIOUS')?>" next-text="<?= JText::_('NEXT')?>" first-text="<?= JText::_('FIRST')?>" last-text="<?= JText::_('LAST')?>" ng-change="pageChanged()"></pagination>
		</div>
	</div>

</div>
<?php
    $document->addStyleSheet('media/com_jcrm/css/sync.css');
    $document->addScript('media/com_jcrm/js/sync.js')
?>