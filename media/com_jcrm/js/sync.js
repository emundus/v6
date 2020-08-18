/**
 * Created by yoan on 08/01/15.
 */
var syncApp = angular.module('syncApp', ['ui.bootstrap']);

syncApp.controller('mainCtrl', ['$scope', '$http', function($scope, $http) {
	$scope.arrayConf = {nbItems: 0, limit: 20, nbPages: 0, currentPage:1, end:0};
	$scope.contactToSync = [];
	$scope.showDime = false;
	$scope.loadData = function(currentPage) {
		$scope.showDime = true;
		$http.get('index.php?option=com_jcrm&task=syncs.getdata&current='+currentPage).
			success(data => {
				$scope.arrayConf.nbItems = data.nbItems;
				$scope.contactToSync = data.toSyncs;
				$scope.showDime = false;
			});
	};
	$scope.loadData(1);

	$scope.validOrga = function(index) {
		var postObj = {
			refId: $scope.contactToSync[index].contact.refId,
			index:$scope.contactToSync[index].contact.index, orgaId: $scope.contactToSync[index].orga.orgaId
		};
		$http.post('index.php?option=com_jcrm&task=syncs.syncorga', postObj).
		success(data => {
			$scope.contactToSync[index].orga.synced = true;

			$scope.contactToSync[index].contact.options = data.options;
			$scope.contactToSync[index].contact.cid = data.cIdDefault;
		});
	};

	$scope.validContact = function(index) {
		var postObj = {
			refId: $scope.contactToSync[index].contact.refId,
			index:$scope.contactToSync[index].contact.index,
			contactId: $scope.contactToSync[index].contact.cId
		};

		$http.post('index.php?option=com_jcrm&task=syncs.synccontact', postObj).success(() => {
			$scope.contactToSync[index].contact.synced = true;
		});
	};

	$scope.refreshContact = function(index) {
		var postObj = {
			refId: $scope.contactToSync[index].contact.refId,
			index: $scope.contactToSync[index].contact.index
		};

		$http.post('index.php?option=com_jcrm&task=syncs.refresh', postObj).
			success(data => {
				$scope.contactToSync[index].orga.synced = data.orgaSynced;
				$scope.contactToSync[index].orga.orgaId = data.orgaIdDefault;

				if (!data.orgaSynced) {
					$scope.contactToSync[index].orga.options = data.orgaOptions;
				}
				$scope.contactToSync[index].contact.synced = data.contactSynced;
				$scope.contactToSync[index].contact.cId = data.cIdDefault;
				if (!data.contactSynced) {
					$scope.contactToSync[index].contact.options = data.contactOptions;
				}
			});
	};

	$scope.ignoreContact = function(index) {
		var conf = confirm(Joomla.JText._('CONFIRM_IGNORE_CONTACT'));
		if (conf == true) {
			var postObj = {
				refId: $scope.contactToSync[index].contact.refId,
				index: $scope.contactToSync[index].contact.index
			};
			$http.post('index.php?option=com_jcrm&task=syncs.ignore', postObj).
				success(data => {
				   if(data.status) {
					   $scope.contactToSync.splice(index, 1);
				   }
			   });
		}
	};

	$scope.refreshAll = function() {
		$scope.loadData($scope.arrayConf.currentPage);
	};

	$scope.ignoreAll = function() {
		var conf = confirm(Joomla.JText._('CONFIRM_IGNORE_ALL_CONTACT'));
		if (conf == true) {
			$scope.showDime = true;
			$http.post('index.php?option=com_jcrm&task=syncs.ignoreall', $scope.contactToSync).success(() => {
				window.location.reload();
			 });
		}
	};

	$scope.validAll = function() {
		$scope.showDime = true;
		$http.post('index.php?option=com_jcrm&task=syncs.validall', $scope.contactToSync).success(() => {
		  $scope.loadData($scope.arrayConf.currentPage);
	   });
	};
}]);

syncApp.controller('PaginationDemoCtrl', function ($scope, $log) {
	$scope.totalItems = $scope.arrayConf.nbItems;
	$scope.currentPage = 1;
	$scope.itemsPerPage = 20;
	$scope.setPage = function (pageNo) {
		$scope.currentPage = pageNo;
		$scope.arrayConf.currentPage = pageNo;
	};

	$scope.pageChanged = function() {
		$scope.loadData($scope.currentPage);
		$log.log('Page changed to: ' + $scope.currentPage);
	};
	$scope.maxSize = 10;
});