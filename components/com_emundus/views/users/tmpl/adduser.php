<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 17/09/14
 * Time: 10:30
 */

$jinput = JFactory::getApplication()->input;
$limitstart = $jinput->getString('limitstart', null);

$s = JRequest::getVar( 's', null, 'get', 'none',0);
if ($s == '') {
	$s = $jinput->getString('lastname' , null);
}

$eMConfig = JComponentHelper::getParams('com_emundus');
$applicant_option = false;
$applicant_option_others = false;

require_once (JPATH_SITE . '/components/com_emundus/helpers/date.php');

?>
<style>
    form {
        margin: 0;
    }
    #campaigns_chosen{
        max-width: 62em;
    }
    .form-group {
        margin-bottom: 10px;
    }
    .em-addUser-groups .chzn-container .chzn-drop,.em-addUser-campaign .chzn-container .chzn-drop {
        top: auto;
        bottom: 100%;
        box-shadow: 0 -4px 5px rgba(0, 0, 0, 0.15);
    }
</style>
<form action="index.php?option=com_emundus&controller=users&task=<?= ($this->edit == 1)? 'edituser' : 'adduser'; ?>" id="em-add-user" class="em-addUser" role="form" method="post">
	<fieldset class="em-addUser-detail">
		<?php if (JPluginHelper::getPlugin('authentication','ldap') && $this->edit == 0) :?>
			<div class="form-group em-addUser-detail-ldap">
				<input type="checkbox" id="ldap" name="ldap" style="margin-bottom:5px;" />
				<label class="control-label" for="ldap">LDAP</label>
			</div>
		<?php endif; ?>
		<div id="user-information" class="em-addUser-detail-info">
            <div class="grid grid-cols-2 gap-2">
                <div class="form-group em-addUser-detail-info-lastname">
                    <label class="control-label" for="lname"><?= JText::_('COM_EMUNDUS_FORM_LAST_NAME'); ?></label>
                    <input type="text" class="em-w-100" id="lname" name = "lastname" <?= ($this->edit == 1)?'value="'.$this->user['lastname'].'"':''; ?>/>
                </div>
                <div class="form-group em-addUser-detail-info-firstname">
                    <label class="control-label" for="fname"><?= JText::_('COM_EMUNDUS_FORM_FIRST_NAME'); ?></label>
                    <input type="text" class="em-w-100" id="fname" name="firstname" <?= ($this->edit == 1)?'value="'.$this->user['firstname'].'"':''; ?>/>
                </div>
            </div>

			<div class="form-group em-addUser-detail-info-mail">
				<label class="control-label" for="mail"><?= JText::_('COM_EMUNDUS_EMAIL'); ?></label>
				<input type="text" class="em-w-100" id="mail" name="email" <?= $this->edit == 1?'value="'.$this->user['email'].'"':''; ?>/>
			</div>
            <div class="form-group em-addUser-detail-info-same-login flex items-center">
                <input type="checkbox" id="same_login_email" name="same_login_email" style="margin-bottom: 5px; outline-offset: 1px;" <?= ($this->user['email'] == $this->user['login']) ? 'checked' : ''; ?>>
                <label for="same_login_email" class="mb-0"><?= JText::_('COM_EMUNDUS_USERS_LOGIN_SAME_EMAIL'); ?></label>
            </div>
            <div class="form-group em-addUser-detail-info-id" id="login_field">
                <label class="control-label" for="login"><?= JText::_('COM_EMUNDUS_USERS_LOGIN_FORM'); ?></label>
                <input type="text" class="em-w-100"  id="login" name="login" <?= ($this->edit == 1) ? 'value="'.$this->user['login'].'"' : ''; ?> />
            </div>
		</div>
	</fieldset>
    <?php
    $other_profiles_applicant = false;
    if(!empty($this->uOprofiles) && sizeof(array_intersect(array_keys($this->uOprofiles),$this->app_prof)) > 0){
        $other_profiles_applicant = true;
    }
    ?>
	<fieldset class="em-addUser-profil em-mt-16">
		<div class="form-group em-addUser-profil-selectProfil">
			<label for="profiles"><?php echo JText::_('COM_EMUNDUS_PROFILE'); ?></label>
			<select id="profiles" name="profiles" class="em-chosen em-mt-4">
				<option value="0" disabled="disabled"><?= JText::_('COM_EMUNDUS_PLEASE_SELECT'); ?></option>
				<?php foreach ($this->profiles as $profile)  {
                    if($profile->published && !$applicant_option) {
                    ?>
					<option id="<?= $profile->acl_aro_groups; ?>" value="<?= $profile->id; ?>"  pub="<?= $profile->published; ?>" <?php if(($this->edit == 1) && (in_array($this->user['profile'],$this->app_prof))){echo 'selected="true"';}?>><?= JText::_('COM_EMUNDUS_APPLICANT'); ?></option>
                <?php
                    $applicant_option = true;
                    } elseif(!$profile->published) { ?>
                <option id="<?= $profile->acl_aro_groups; ?>" value="<?= $profile->id; ?>"  pub="<?= $profile->published; ?>" <?php if(($this->edit == 1) && ($profile->id == $this->user['profile'])){echo 'selected="true"';}?>><?= trim($profile->label); ?></option>
				<?php } } ?>
			</select>

			<div class="em-addUser-profil-selectProfil-multiple mt-3">
				<label for="oprofiles"><?= JText::_('COM_EMUNDUS_USERS_ALL_PROFILES'); ?></label>
				<select id="oprofiles" name="otherprofiles" size="5" multiple="multiple" class="em-chosen em-mt-4">
					<option value="0" disabled="disabled"><?= JText::_('COM_EMUNDUS_PLEASE_SELECT'); ?></option>
					<?php foreach ($this->profiles as $otherprofile)  {
                        if($otherprofile->published && !$applicant_option_others) {
                        ?>
						<option id="<?= $otherprofile->acl_aro_groups; ?>" value="<?= $otherprofile->id; ?>" pub="<?= $otherprofile->published; ?>" <?= (($this->edit == 1) && $other_profiles_applicant === true)?'selected="true"':''; ?>><?= JText::_('COM_EMUNDUS_APPLICANT'); ?></option>
                    <?php
                        $applicant_option_others = true;
                        } elseif (!$otherprofile->published) { ?>
                    <option id="<?= $otherprofile->acl_aro_groups; ?>" value="<?= $otherprofile->id; ?>" pub="<?= $otherprofile->published; ?>" <?= (($this->edit == 1) && (array_key_exists($otherprofile->id, $this->uOprofiles)))?'selected="true"':''; ?>><?= trim($otherprofile->label); ?></option>
					<?php } } ?>
				</select>
			</div>
		</div>
        <?php if($eMConfig->get('showUniversities')) : ?>
		<div class="form-group em-hidden-nonapli-fields em-addUser-university" <?= (($this->edit != 1) || ($this->user['university_id'] == 0))?'style="display:none;"':''; ?>>
			<label for="univ"><?= JText::_('COM_EMUNDUS_USERS_UNIVERSITY_FROM'); ?></label>
			<select name="university_id" class="em-chosen em-mt-4" id="univ">
				<option value="0"><?= JText::_('COM_EMUNDUS_PLEASE_SELECT'); ?></option>
				<?php foreach ($this->universities as $university) :?>
					<option value="<?= $university->id; ?>" <?= (($this->edit == 1) && ($university->id == $this->user['university_id']))?'selected="true"':''; ?>><?= trim($university->title); ?></option>
				<?php endforeach;?>
			</select>
		</div>
        <?php endif ?>
        <div class="form-group em-hidden-nonapli-fields em-addUser-groups" style="<?= (($this->edit != 1) || (empty($this->uGroups))) ? 'display:none;' : 'display:block;'; ?>">
			<label for="groups"><?= JText::_('COM_EMUNDUS_GROUPS'); ?></label>
			<select class = "em-chosen em-mt-4" name="groups" id="groups" multiple="multiple">
				<option value="0" disabled="disabled"><?= JText::_('COM_EMUNDUS_PLEASE_SELECT'); ?></option>
				<?php foreach ($this->groups as $group) :?>
					<option value="<?= $group->id; ?>" <?= (($this->edit == 1) && (array_key_exists($group->id, $this->uGroups)))?'selected="true"':''; ?>><?= trim($group->label); ?></option>
				<?php endforeach;?>
			</select>
		</div>


        <?php if($eMConfig->get('showJoomlagroups',0)) : ?>
        <div class="form-group em-hidden-nonapli-fields em-addUser-groups" <?= (($this->edit != 1))?'style="display:none;"':''; ?>>
            <label for="jgroups"><?= JText::_('COM_EMUNDUS_JOOMLA_GROUPE'); ?></label>
            <br/>
            <select class = "em-chosen" name="jgroups" id="jgroups" multiple="multiple">
                <option value="0" disabled="disabled"><?= JText::_('PLEASE_SELECT'); ?></option>
                <?php foreach ($this->jgroups as $jgroup) :?>
                    <option value="<?= $jgroup->group_id; ?>" <?= (($this->edit == 1) && (array_key_exists($jgroup->group_id, $this->juGroups)))?'selected="true"':''; ?>><?= trim($jgroup->category_label); ?></option>
                <?php endforeach;?>
            </select>
        </div>
        <?php endif ?>

        <div class="form-group em-hidden-appli-fields em-addUser-campaign" style="<?= (($this->edit != 1) || (empty($this->uCamps))) ? 'display:none;' : 'display:block;'; ?>">
			<label for="campaigns"><?= JText::_('COM_EMUNDUS_CAMPAIGN'); ?></label>
			<select name="campaigns" size="5" multiple="multiple" id="campaigns" class="em-chosen em-mt-4">
				<option value="0" disabled="disabled"><?= JText::_('COM_EMUNDUS_PLEASE_SELECT'); ?></option>
				<?php foreach ($this->campaigns as $campaign) :?>
				    <option value="<?= $campaign->campaign_id; ?>" <?= (($this->edit == 1) && (array_key_exists($campaign->campaign_id, $this->uCamps)))?'selected="true"':''; ?>><?= trim($campaign->label.' ('.$campaign->year.') - '.$campaign->programme.' | '.JText::_('COM_EMUNDUS_ONBOARD_START_DATE').' : '.EmundusHelperDate::displayDate($campaign->start_date, 'COM_EMUNDUS_DATE_FORMAT', 1));?></option>
				<?php endforeach;?>
			</select>
		</div>

		<!-- LDAP registration will go inside the div -->
		<div id="ldap-form" class="em-addUser-searchLdap" style="display : none;">
			<div id="ldap-errors"></div>
			<label for="s"><strong><?= JText::_( 'COM_EMUNDUS_SEARCH_IN_LDAP'); ?> </strong></label><br/>
			<input type="text" class="input-xxlarge" name="s" id="s" value="<?= $s; ?>" /><div id="sldap" type="button" class="button" style="margin-bottom:10px;"> <?= JText::_('COM_EMUNDUS_ACTIONS_SEARCH'); ?></div>
		</div>
		<div id="ldapresult"></div>
	</fieldset>
</form>
<script type="text/javascript">
	window.onunload = function() {
		window.opener.location.reload();
	};

    $(document).ready(function () {
		var edit = '<?php echo $this->edit?>';
		$('alertes-details').css({padding:"30px"});
		$('.em-chosen').chosen({width:'100%'});

        renderingSelects();

        let loginField = $('#login_field');

        let sameLogin = $('#same_login_email');
		if(sameLogin.is(':checked')){
            loginField.hide();
        }

        $(document).on('change', '#same_login_email', function() {
            let loginField = $('#login_field');
            if ($(this).is(':checked')) {
                loginField.hide();
                $('#login').val($('#mail').val());
            } else {
                loginField.show();
                $('#login').val('');
            }
        });


		$(document).on('change', '#ldap', function() {
			if ($(this).is(':checked')) {

                // Select the div where the search results will show.
                let ldapResult = $('#ldapresult');

				// If the LDAP registration option is selected, we need to modify the window with the LDAP registration interface.
				$('#ldap-form').show();
                ldapResult.show();
				// Hide all inputs linked to standard user creation.
				$('#user-information').children().hide();

				// The LDAP elements to dipslay and use for the user cards.
				// Due to the fact that we need the translated text and we dont know what the info to display will be in advance.
				// We have to create an object containing the translated value and the real value.
				let ldapElements = [
				<?php
					foreach (explode(',',$this->ldapElements) as $elt) {
						echo '["'.$elt.'","'.JText::_(strtoupper($elt)).'"],';
					}
				?>
				];

				function searchLDAP() {

					$.ajax({
						type: "GET",
						url:'index.php?option=com_emundus&controller=users&task=ldapsearch&search='+$('#s')[0].value,
						dataType: 'html',
						beforeSend: function() {
							ldapResult.text('<?= Jtext::_('COM_EMUNDUS_FILTERS_SEARCHING'); ?> ['+$('#s')[0].value+']');
						},
						success: function(result) {

							result = JSON.parse(result);

							if (!result.status) {
                                ldapResult.html("<span class='alert alert-error'> <?php echo JText::_('COM_EMUNDUS_ERROR_OCCURED'); ?> </span>")
                            } else if (result.count == 0) {
                                ldapResult.html("<span class='alert alert-error'> <?php echo JText::_('COM_EMUNDUS_LDAP_NO_RESULT'); ?> </span>")
                            } else {
								ldapResult.html('');
								// Foreach user
								result.ldapUsers.forEach(function(user) {

									var otherElts = [];

									// For each LDAP element defined by our param.
									for (let i = 0; i < ldapElements.length; i++) {
										// Initialize the required objects as well as the other elements.
										// The first 4 elements defined in the params are always required and in the exact order below.
										if (i === 0) {
											var username = {};
											username.value = user[ldapElements[i][0]];
											username.label = ldapElements[i][1]
										} else if (i === 1) {
											var mail = {};
                                            var mails = user[ldapElements[i][0]] + "";
                                            mail.value = mails.split(",")[0];
                                            mail.label = ldapElements[i][1];
										} else if (i === 2) {
											var fname = {};
											fname.value = user[ldapElements[i][0]];
											fname.label = ldapElements[i][1]
										}  else if (i === 3) {
											var lname = {};
											lname.value = user[ldapElements[i][0]];
											lname.label = ldapElements[i][1]
										} else {
											let elt = {};

											// If there is no value for the element then we should display --- instead of 'undefined'
											if (typeof user[ldapElements[i][0]] == 'undefined') {
                                                elt.value = ' --- ';
                                            } else {
                                                elt.value = user[ldapElements[i][0]];
                                            }

											elt.ldap = ldapElements[i][0];
											elt.label = ldapElements[i][1];
											otherElts.push(elt)
										}
									}

									let cardColor = '',
                                        cardInfo = '',
                                        addUser = '';
									if (user.exists) {
										cardColor = 'alert-success';
                                        cardInfo = '<div data-toggle="tooltip" data-placement="top" title="<?php echo JText::_('COM_EMUNDUS_LDAP_USER_EXISTS'); ?>">'+
                                                        '<div class="hide uid">'+username.value+'</div>'+
                                                        '<span class="glyphicon glyphicon-ok" style="font-size:30px; padding-top:60px;"></span>'+
                                                    '</div>';
                                    } else {
                                        cardColor = 'ldap-card';
                                        cardInfo = '<div data-toggle="tooltip" data-placement="top" title="<?php echo JText::_('COM_EMUNDUS_LDAP_USER_NEW'); ?>">'+
                                                        '<div class="hide uid">'+username.value+'</div>'+
                                                        '<span class="glyphicon glyphicon-plus" style="font-size:30px; padding-top:60px;"></span>'+
                                                    '</div>';
                                        addUser = '<a class="create-user" href="#" >';
                                    }

									let userCard = addUser+'<div class="media col-md-3 '+cardColor+'" id="ldap-user-'+username.value+'" style="margin:0 10px 10px 10px; height:200px;">'+
													'<div class="media-left" style="text-align:center; float:left;">'+
														cardInfo+
													'</div>'+
													'<div class="media-body" style="text-align: left;padding-left: 10px;height: 100%;">'+
														'<h4 class="media-heading" style="padding-top:15px;"> '+fname.value+' '+lname.value+'</h4>'+
														'<div class="hide ldap-lname">'+lname.value+'</div>'+
														'<div class="hide ldap-fname">'+fname.value+'</div>'+
														'<div class="hide ldap-mail">'+mail.value+'</div>'+
														'<div class="ldap-username"><strong>'+username.label+':</strong> '+username.value+'</div>'+
														'<div><strong>'+mail.label+':</strong> '+mail.value+'</div>';
									otherElts.forEach(function(elt) {
										userCard += '<p class="ldap-'+elt.ldap+'"><strong>'+elt.label+':</strong> '+elt.value+'</p>';
									});
									userCard += '</div></div>';

                                    if (!user.exists) {
                                        userCard += '</a>';
                                    }

									if (typeof username.value != 'undefined' && typeof mail.value != 'undefined' && typeof fname.value != 'undefined' && typeof lname.value != 'undefined')
										ldapResult.append(userCard)
								});

								$('.create-user').on('click', function(e) {

									e.preventDefault();
									// Get user login name
									let uid = $(this).find('.uid').text();

									// using the login name: find the user card
									let userCard = $('#ldap-user-'+uid.replace( /(:|\.|\[|\]|,|=|@)/g, "\\$1" ));

									// The "create user" form is filled out using the values found in the user card.
									// This is better than sending an Ajax because if the "create user" form is extended then we don't need to modify this code.
									$('#fname').val(userCard.find('.ldap-fname').text());
									$('#lname').val(userCard.find('.ldap-lname').text());
									$('#login').val(uid);
									$('#mail').val(userCard.find('.ldap-mail').text());

									// All user cards have their CSS reset.
									$("div[id^=ldap-user-]").each(function() {
										$(this).removeAttr('style');
										$(this).css({
											'margin': '0 10px 10px 10px',
											'height': '200px'
										});
									});

									// Change the color of the selected user card.
									userCard.css({
										'border': '1px solid #013243',
										'background-color': '#D9EDF5',
										'margin': '0px 10px 10px'
									});

								});
							}
						},
                        error: function() {
                            ldapResult.text("<?= JText::_('COM_EMUNDUS_ERROR_OCCURED'); ?>");
                        }
					});
				}

				// The delay means that the function will not start until the user has stopped typing.
				var delay = (function(){
					var timer = 0;
					return function(callback, ms){
						clearTimeout (timer);
						timer = setTimeout(callback, ms);
					};
				})();

				// Remove event listeners to avoid having double ajax calls.
				$('#s').off();

				$('#s').on('keyup', function(e) {
					delay(function() {
						let input = $('#s')[0];
						if (input.value.length > 3) {
							searchLDAP();
						}
					},1000);
				});

				$('#sldap').off();

				$('#sldap').on('click', function(e) {
					searchLDAP();
				});

			} else {
				$('#ldap-form').hide();
				$('#ldapresult').hide();
				$('#user-information').children().show();
				$('#fname').val('');
				$('#lname').val('');
				$('#login').val('');
				$('#mail').val('');
				// All user cards have their CSS reset.
				$("div[id^=ldap-user-]").each(function() {
					$(this).removeAttr('style');
					$(this).css({
						'margin': '0 10px 10px 10px',
						'height': '200px'
					});
				});
			}
		})

		$(document).on('change', '#profiles', function() {
            renderingSelects($(this).val());
		});

        $(document).on('change', '#oprofiles', function() {
            renderingSelects();
        });

		$(document).on('blur', '#mail', function() {
			var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-z\-0-9]+\.)+[a-z]{2,}))$/;
			if ($(this).val().length == 0 || !re.test($(this).val())) {
				$(this).parent('.form-group').addClass('has-error');
                $(this).siblings('.help-block').remove();
                $(this).after('<span class="help-block">'+Joomla.JText._('COM_EMUNDUS_USERS_ERROR_NOT_A_VALID_EMAIL')+'</span>');
			}
		});

		$(document).on('focus', '#mail', function() {
			$(this).parent('.form-group').removeClass('has-error');
			$(this).siblings('.help-block').remove();
		});

		$(document).on('keyup', '#login', function() {
			var re = /^[0-9a-zA-Z\_\@\-\.]+$/; // /^[a-z0-9]*$/;
			if (!re.test($('#login').val())) {
				if (!$(this).parent('.form-group').hasClass('has-error')) {
					$(this).parent('.form-group').addClass('has-error');
					$(this).after('<span class="help-block">'+Joomla.JText._('COM_EMUNDUS_USERS_ERROR_NOT_A_VALID_LOGIN_MUST_NOT_CONTAIN_SPECIAL_CHARACTER')+'</span>');
				}
			} else {
				$(this).parent('.form-group').removeClass('has-error');
				$(this).siblings('.help-block').remove();
			}
		});


        function renderingSelects(value = null){
            if(value == null){
                value = document.getElementById('profiles').value;
            }
            let main_profile_value = $('#profiles option[value="'+value+'"]').attr('pub');
            let sub_profiles_values = getSelectValues(document.getElementById('oprofiles'));
            let other_profiles_published = false;
            let other_profiles_no_published = false;

            sub_profiles_values.forEach((profile) => {
                if($('#oprofiles option[value="'+profile+'"]')[0].attributes.pub.value == 1){
                    other_profiles_published = true;
                } else {
                    other_profiles_no_published = true;
                }
            })

            if (main_profile_value == 1) {
                $('.em-hidden-appli-fields').show();
                if(other_profiles_no_published === false) {
                    $('#groups_chosen .search-choice-close').click();
                    $('.em-hidden-nonapli-fields').hide();
                } else {
                    $('.em-hidden-nonapli-fields').show();
                }
            } else {
                $('.em-hidden-nonapli-fields').show();
                if(other_profiles_published === false) {
                    $('#campaigns_chosen .search-choice-close').click();
                    $('.em-hidden-appli-fields').hide();
                } else {
                    $('.em-hidden-appli-fields').show();
                }
            }
        }

        function getSelectValues(select) {
            var result = [];
            var options = select && select.options;
            var opt;

            for (var i=0, iLen=options.length; i<iLen; i++) {
                opt = options[i];

                if (opt.selected) {
                    result.push(opt.value || opt.text);
                }
            }
            return result;
        }
	})
</script>
