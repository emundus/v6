/**
 * Created by yoan on 03/12/14.
 */

var contactApp = angular.module('contactApp', ['ui.bootstrap', 'angularFileUpload', 'ui.utils', 'ui.tinymce']);
//var contactApp = angular.module('contactApp', ['ui.bootstrap', 'angularFileUpload', 'ui.utils']);

contactApp.factory('jcrmConfig', [function()
	{
		'use strict';
		var group_id = 0;
		var index = 0;
		var setIndex = function(newIndex)
		{
			index = newIndex;
		}
		var setGroupId = function(groupId)
		{
			group_id = groupId;
		};
		var getGroupId = function()
		{
			return group_id;
		}
		var getIndex = function()
		{
			return index;
		}
		return {
			setGroupId: setGroupId,
			setIndex: setIndex,
			getIndex: getIndex,
			getGroupId: getGroupId
		};
	}
]);
contactApp.controller('contactsController', ['$scope', '$http', 'jcrmConfig', function($scope, $http, jcrmConfig)
{
	$scope.contacts = new Array();
	$scope.nbContacts = 0;
	$scope.searchType = false;
	jcrmConfig.setGroupId(0);
	jcrmConfig.setIndex(0);
	$scope.arrayText = {"work":Joomla.JText._('CONTACT_WORK'), "home":Joomla.JText._('CONTACT_HOME'), "fax":Joomla.JText._('CONTACT_FAX'), "cell":Joomla.JText._('CONTACT_CELL')};
	$scope.search="";

	$scope.switchSearch = function()
	{
		$scope.searchType = !$scope.searchType;
		jcrmConfig.setIndex(0);
		$scope.getContacts();
	}

	$scope.searchContact = function($event)
	{
		if((($event.keyCode >= 48) && ($event.keyCode <= 90)) || ($event.keyCode == 8))
		{
			jcrmConfig.setIndex(0);
			$scope.getContacts(jcrmConfig.getGroupId());
		}
	};

	$scope.groupSelected = jcrmConfig.getGroupId();
	$scope.dimeBody = false;
	$scope.dimeList = false;
	$scope.canLoad = true;
	$http.get('index.php?option=com_jcrm&task=contacts.getgroups').
		success(function(data)
        {
	       $scope.groups = data;
        });
	$scope.getContacts = function(id)
	{
		$scope.dimeList = true;
		var diff = false;
		if(id != jcrmConfig.getGroupId())
		{
			diff = true;
			$("#em-jcrm-search").val();
			$scope.search = '';
			jcrmConfig.setGroupId(id);
			$scope.groupSelected = id;
			jcrmConfig.setIndex(0);
		}
		$scope.canLoad = false;
		var type = ($scope.searchType)?1:0;
		$http.get('index.php?option=com_jcrm&task=contacts.getcontacts&group_id='+jcrmConfig.getGroupId()+'&index='+jcrmConfig.getIndex()+'&q='+$scope.search+'&type='+type).
			success(function(data)
			        {
				        if(jcrmConfig.getIndex() == 0 || diff || $scope.search != "")
				        {
					        $scope.contacts = data.contacts;
				        }
				        else
				        {
					      $scope.contacts = $scope.contacts.concat(data.contacts);
				        }
						jcrmConfig.setIndex($scope.contacts.length);
				        $scope.nbContacts = data.nbContacts;
				        $scope.dimeList = false;
				        $scope.canLoad = true;
			        });
	}
	$scope.getContacts(0);
	$scope.contact = {phone:[{type:'work', tel:''}], adr:[{type:'work', array:[]}], email:[{type:'work', uri:''}], infos:'', other:[]};
	$scope.newUser = function()
	{
		var contact =
		{
			phone: [{type: 'work', tel: ''}],
			adr: [{type: 'work', array: []}],
			email: [{type: 'work', uri: ''}],
			formGroup:[],
			infos: '',
			other: []
		};
		return contact;
	};
	$scope.formVisible = false;
	$scope.addContact = function(newContact)
	{
		if(newContact.$valid)
		{
			$scope.dimeBody = true;
			$http({method:'POST', url:'index.php?option=com_jcrm&task=contact.addcontact', data:$scope.contact}).
				success(function(response)
				        {
					        $scope.contacts = $scope.getContacts();
					        $scope.contact = {};
					        $scope.getContact(response.id);
				        });
		}
	};
	$scope.save = function(newContact)
	{
		if ($scope.contact.type)
			$scope.contact.type = 1;
		else
			$scope.contact.type = 0;
		if($scope.contact.id)
		{
			$scope.editContact(newContact);
		}
		else
		{
			$scope.addContact(newContact);
		}
	}
	$scope.addGroup = function()
	{
		$scope.groups.push({name:"", edit:false, id:0});
	}
	$scope.saveGroup = function(index, $event)
	{
		var name = $scope.groups[index].name;
		if($scope.groups[index].id !== 0)
		{
			$http({method:'POST', url:'index.php?option=com_jcrm&task=contact.updategroup', data:$scope.groups[index]}).
				success(function(response)
				        {
					        $scope.groups[index].name = name;
					        $scope.groups[index].edit = false;
					        $('#contact-groups').trigger('chosen:updated');
				        });
		}
		else
		{
			$http({method:'POST', url:'index.php?option=com_jcrm&task=contact.addgroup', data:$scope.groups[index]}).
				success(function(response)
				        {
					        $scope.groups[index].id = response.id;
					        $scope.groups[index].name = name;
					        $('#contact-groups').trigger('chosen:updated');
				        });
		}
		$event.preventDefault();
	}
	$scope.editGroup = function(index)
	{
		$scope.groups[index].edit = true;
	}
	$scope.cancelGroup = function(index)
	{
		if($scope.groups[index].id == 0)
		{
			$scope.groups.splice(index,1);
		}
		else
			$scope.groups[index].edit = false;
	};
	$scope.deleteGroup = function(index, id)
	{
		var r = confirm(Joomla.JText._('CONTACT_ARE_YOU_SURE'));
		if (r == true)
		{
			$scope.groups.splice(index, 1);
			$('#contact-groups').trigger('chosen:updated');
			$http({method:'POST', url:'index.php?option=com_jcrm&task=contact.deletegroup', data: {id: id}}).
				success(function(data)
                        {

                        });
		}
	}
	$scope.addField = function(type)
	{
		switch (type)
		{
			case 'phone':
				$scope.contact.phone.push({type:'work', tel:''});
				break;
			case 'adr':
				$scope.contact.adr.push({type:'work', array:[]});
				break;
			case 'email':
				$scope.contact.email.push({type:'work', uri:''});
				break;
			case 'other':
				$scope.contact.other.push({type:'', value:''});
				break;
		}
	};
	$scope.deleteField = function(index, type)
	{
		switch(type)
		{
			case 'phone':
				$scope.contact.phone.splice(index, 1);
				break;
			case 'email':
				$scope.contact.email.splice(index, 1);
				break;
			case 'adr':
				$scope.contact.adr.splice(index, 1);
				break;
			case 'other':
				$scope.contact.other.splice(index, 1);
				break;
		}
	}
	$scope.getContact = function(id)
	{
		$scope.dimeBody = true;
		$http.get('index.php?option=com_jcrm&task=contact.getcontact&contact_id='+id).success(function(response)
		{
			$scope.hideForm();
			$scope.contact = response;
			$scope.dimeBody = false;
		});
	};
	$scope.showForm = function(id)
	{
		if(!id)
		{
			$scope.contact = $scope.newUser();
		}
		$scope.formVisible = true;
	};
	$scope.hideForm = function()
	{

		$scope.formVisible = false;
	};
	$scope.editContact = function(newContact)
	{
		if(newContact.$valid)
		{
			$scope.dimeBody = true;
			$http({method:'POST', url:'index.php?option=com_jcrm&task=contact.update', data:$scope.contact}).
				success(function(response)
				        {
					        $scope.hideForm();
					        $scope.getContact($scope.contact.id);
					        $scope.dimeBody = false;
					        $scope.contacts = $scope.getContacts();
				        });
		}
	};
	$scope.delete = function(id)
	{
		var r = confirm(Joomla.JText._('CONTACT_ARE_YOU_SURE'));
		if (r == true)
		{
			$http({method:'POST', url:'index.php?option=com_jcrm&task=contact.deletecontact', data: $scope.contact}).
				success(function(data)
				        {
					        jcrmConfig.setIndex(0);
					        $("#em-jcrm-search").val();
					        $scope.search = '';
					        $scope.contact = $scope.newUser();
							$scope.getContacts(jcrmConfig.getGroup);
				        });
		}
	};

}]);

contactApp.directive('chosen', function()
{
	return {
		restrict:'C',
		require:'ngModel',
		link:function($scope, $element, $attrs, ngModelCtrl)
		{
			$scope.$watch('contact.formGroup', function(newValue, oldValue)
			{
				if(newValue)
					$element.trigger("chosen:updated");
			})

			setTimeout(function(){$element.chosen({width: '250px', height: '35px'});}, 500);

			ngModelCtrl.$render = function ()
			{
				$element.trigger("chosen:updated");
			};
		}

	};
});

contactApp.directive('contactTinyMce', ['$timeout',
   function ($timeout) {
       return {
           restrict: 'E',
           require: 'ngModel',
           template: "<form method='post'><textarea id='mail-body' class='form-control' ui-tinymce='tinymceOptions' ng-model='body'></textarea></form>", // A template you create as a HTML file (use templateURL) or something else...
           link: function ($scope, $element, attrs, ngModel)
           {

               // Create the editor itself, use TinyMCE in your case
               /*tinyMCE.init({
                    mode : "exact",
                    elements: "mail-body",
                    setup : function(edd)
                    {
                       edd.onClick.add(function(ed)
                       {
                           var newValue = tinyMCE.activeEditor.getContent();
                           if (!$scope.$$phase)
                           {
                                $scope.$apply(function ()
                                {
                                    ngModel.$setViewValue(newValue);
                                });
                            }
                        }
                        )
                    },
                    theme : "simple" });*/

               ngModel.$render = function ()
               {
                   //tinyMCE.activeEditor.setContent(ngModel.$viewValue);
                   ngModel.$setViewValue(ngModel.$viewValue);
               };
            }
       };
   }]);

contactApp.controller('tinymceCtrl', function($scope) {
  $scope.tinymceOptions = {
    onChange: function(e) {
      alert($scope.body);
    },
    inline: false,
    plugins : 'advlist autolink link image lists charmap print preview',
    skin: 'lightgray',
    theme : 'modern',
    mode : "exact",
    elements: "mail-body"
  };
});

contactApp.directive('jcrmScroll', ['jcrmConfig', function(jcrmConfig)
{
	return{
		restrict:'A',
		link:function($scope, $element, $attrs, $http)
		{
			var raw = $element[0];
			$element.bind('scroll', function()
			{
				if(((raw.scrollTop + raw.offsetHeight) > (raw.scrollHeight * 0.7)) && $scope.canLoad)
				{
					$scope.getContacts();
				}
			})
		}
	}
}])

contactApp.controller('TypeaheadCtrl', ['$scope', '$http', function($scope, $http)
{
	$scope.selected = undefined;
	$scope.getLocation = function(val) {
		return $http.get('index.php?option=com_jcrm&task=contacts.getorganisations', {
			params: {
				org: val
			}
		}).then(function(response){
			var map = response.data.map(function(item)
			{
				return item.organisation;
			});
			return map;
		});
	};
	$scope.onSelect = function ($item, $model, $label)
	{
		$scope.$item = $item;
		if($item.type == 'contact')
		{
			$scope.guestList.contacts.push($item.id);
		}
		else
		{
			$scope.guestList.groups.push($item.id);
		}
		$scope.guestList.items.push($item);
		$scope.contactGuest = '';
	};
	$scope.getMailContact = function(val)
	{
		return $http.get('index.php?option=com_jcrm&task=email.getmailcontact',
        {
			params:
			{
				contact: val
			}
		}).then(function(response)
               {
	               var result = new Array();
	               if(response.data.contacts !== null)
	               {
		               var map = response.data.contacts.map(function(item)
		                                                    {
			                                                    var contact = item.full_name + ' - ' + item.email;
			                                                    return {id: item.id, contact: contact, type:'contact'};
		                                                    });
		             result =  result.concat(map);
	               }
				if(response.data.groups !== null)
				{
					var gr = response.data.groups.map(function(item)
					                                  {
						                                  return {id: item.id, contact: item.name + ' - '+ Joomla.JText._('CONTACT_GROUP'), type:'group'};
					                                  });
					result = result.concat(gr);
				}
				return result;
               });
	};

	$scope.deleteGuest = function(index)
	{
		var guest = $scope.guestList.items[index];
		$scope.guestList.items.splice(index, 1);
		if(guest.type == 'group')
		{
			var idx = $scope.guestList.groups.indexOf(guest.id, 0);
			$scope.guestList.groups.splice(idx, 1);
		}
		else
		{
			var idx = $scope.guestList.contacts.indexOf(guest.id, 0);
			$scope.guestList.contacts.splice(idx, 1);
		}
	}
}]);
//modal mail
contactApp.controller('ModalDemoCtrl', function ($scope, $modal, $log, $http)
{
	$scope.getMailBody = function(id)
	{
		$http.get(''+id).success(function(data)
		{
			$scope.mailBody = data;
		})
	}
	$scope.open = function (size, contact, groupSelected, from)
	{
		$scope.sendTo = {};
		if (contact.id)
		{
			$scope.sendTo = contact;
		}
		$scope.from = from;
		var modalInstance = $modal.open({ templateUrl: 'myModalContent.html',
		                                  controller: 'ModalInstanceCtrl',
	                                      size: size,
	                                      resolve:
                                         {
				                                sendTo: function ()
				                                {
					                                return $scope.sendTo;
				                                },
	                                            from: function()
	                                            {
		                                            return $scope.from;
	                                            },
	                                            groupSelected: function()
	                                            {
		                                            return groupSelected;
	                                            }
			                                }
		                                });

		modalInstance.result.then(function (sendTo, from, groupSelected)
        {
			$scope.sendTo = sendTo;
	        $scope.from = from;
	        $scope.groupSelected  = groupSelected;
		},
        function ()
		{
			$log.info('Modal dismissed at: ' + new Date());
		});
	};
});

// Please note that $modalInstance represents a modal window (instance) dependency.
// It is not the same as the $modal service used above.
contactApp.controller('ModalInstanceCtrl', function($scope, $modalInstance, sendTo, from, groupSelected, $http) {
	$scope.alerts = new Array();
	$scope.showDimeModal = false;
	if (from == 'mail') {
		$scope.body='';
		$scope.bodyId = -1;
		$scope.subject = '';
		$scope.from = 0;
		$scope.orgMail = 'direct';
	} else if (from == 'vcard') {
		$scope.export= {type:0};
		$scope.from = 1;
		$scope.orgExport = 'direct';
	}
	$scope.guestList = {contacts:new Array(), groups:new Array(), items:new Array()};
	if (sendTo.id) {
		$scope.sendTo = sendTo;
		$scope.guestList.contacts.push(sendTo.id);
		$scope.guestList.items.push({id:sendTo.id, contact:sendTo.full_name, type:'contact'});
	}

	$scope.ok = function() {
		//envoi du mail dans le cas d'un envoi
		if ($scope.from == 0) {
			var canSend = false;
			if ($scope.guestList.contacts.length > 0) {
				canSend = true;
			}

			if ($scope.guestList.groups.length > 0) {
				canSend = true;
			}

			if(canSend) {
				$scope.showDimeModal = true;

				var datas = {
					contacts:$scope.guestList,
					body: $scope.body,
					subject:$scope.subject,
					id: $scope.bodyId,
					orgmail: $scope.orgMail
				};

				$http.post('index.php?option=com_jcrm&task=email.sendmail', datas).success(res => {
	                     $scope.showDimeModal = false;
	                     if (res.status) {
	                         $scope.alerts = [{type:'success', msg: res.msg}];
	                         setTimeout(function() {
								$modalInstance.close();
							}, 1500);
	                     } else {
	                         $scope.alerts = [{type:'alert', msg: res.msg}];
	                     }
	                 });
			} else {
				$scope.alerts.push({type:'danger', msg: Joomla.JText._('CONTACT_ADD_CONTACT_PLEASE')});
			}
		} else if ($scope.from == 1) {
			$scope.showDimeModal = true;
			if (parseInt($scope.export.list) == 0) {
				$scope.export.id = sendTo.id;
			} else {
				$scope.export.id = groupSelected;
			}

			var data = {
				contacts: $scope.guestList,
				export: $scope.export.type,
				orgexport: $scope.orgExport
			};

			$http.post('index.php?option=com_jcrm&task=contacts.export', data).success(res => {
				$scope.showDimeModal = false;
				if (res.status) {
					$scope.alerts = [{type:'success', msg: res.msg}];
					$scope.dlButton = {link:res.link, linkMsg:res.linkMsg};
				} else {
					$scope.alerts.push({type:'danger', msg: res.msg});
				}
			});
		}
	};

	$scope.cancel = function () {
		$modalInstance.dismiss('cancel');
	};

	$scope.getBody = function() {
		if ($scope.bodyId != "0") {
			$http.get('index.php?option=com_jcrm&task=email.getmailbody&bid='+$scope.bodyId).success(data => {
				 $scope.body = data.message;
				 $scope.subject = data.subject;
			 });
		} else {
			$scope.body = '';
		}
	}

	$scope.closeAlert = function(index) {
		$scope.alerts.splice(index, 1);
	};
});

