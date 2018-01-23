<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 17/09/14
 * Time: 10:30
 */
?>

<form action = "<?php if($this->edit == 1){echo "index.php?option=com_emundus&controller=users&task=edituser";}else{echo "index.php?option=com_emundus&controller=users&task=adduser";}?>" id="em-add-user" role="form" method="post">
	<h3>
		<?php
			if($this->edit == 1)
			{
				echo JText::_('EDIT_USER');
			}
			else
			{
				echo JText::_('ADD_USER');
			}
		?>
	</h3>
	<fieldset>
		<div class="form-group">
			<label class="control-label" for="firstname"><?php echo JText::_('FIRSTNAME_FORM'); ?></label>
			<input type="text" class="form-control" id="fname" name="firstname" <?php if($this->edit == 1){echo 'value="'.$this->user['firstname'].'"';}?>/>
		</div>
		<div class="form-group">
			<label class="control-label" for="lastname"><?php echo JText::_('LASTNAME_FORM'); ?></label>
			<input type="text" class="form-control" id="lname" name = "lastname" <?php if($this->edit == 1){echo 'value="'.$this->user['lastname'].'"';}?>/>
		</div>
		<div class="form-group">
			<label class="control-label" for="login"><?php echo JText::_('LOGIN_FORM'); ?></label>
			<input type="text" class="form-control"  id="login" name = "login" <?php if($this->edit == 1){echo 'value="'.$this->user['login'].'"';}?> />
		</div>
		<div class="form-group">
			<label class="control-label" for="email"><?php echo JText::_('EMAIL_FORM'); ?></label>
			<input type="text" class="form-control" id="mail" name = "email" <?php if($this->edit == 1){echo 'value="'.$this->user['email'].'"';}?>/>
		</div>
	</fieldset>
	<fieldset>
		<div class="form-group">
			<label class="control-label" for="profiles"><?php echo JText::_('PROFILE_FORM'); ?></label>
			<br/>
			<select id="profiles" name="profiles" class="em-chosen">
				<option value="0"><?php echo JText::_('PLEASE_SELECT')?></option>
				<?php foreach($this->profiles as $profile):?>
					<option id="<?php echo $profile->acl_aro_groups?>" value="<?php echo $profile->id?>"  pub="<?php echo $profile->published?>" <?php if(($this->edit == 1) && ($profile->id == $this->user['profile'])){echo 'selected="true"';}?>><?php echo trim($profile->label);?></option>
				<?php endforeach;?>
			</select>
			<br/><br/>
			<div >
				<label class="control-label" for="otherprofile"><?php echo JText::_('ALL_PROFILES'); ?></label><br/>
				<select id="oprofiles" name="otherprofiles" size="5" multiple="multiple" class="em-chosen">
					<option value="0"><?php echo JText::_('PLEASE_SELECT')?></option>
					<?php foreach($this->profiles as $otherprofile):?>
						<option id="<?php echo $otherprofile->acl_aro_groups?>" value="<?php echo $otherprofile->id?>" <?php if(($this->edit == 1) && (array_key_exists($otherprofile->id, $this->uOprofiles))){echo 'selected="true"';}?>><?php echo trim($otherprofile->label);?></option>
					<?php endforeach;?>
				</select>
			</div>
		</div>
		<div class="form-group em-hidden-nonapli-fields" <?php if(($this->edit != 1) || ($this->user['university_id'] == 0)){echo 'style="display:none;"';}?>>
			<label for="university_id"><?php echo JText::_('UNIVERSITY_FROM'); ?></label>
			<br/>
			<select name="university_id" class="em-chosen" id="univ">
				<option value="0"><?php echo JText::_('PLEASE_SELECT')?></option>
				<?php foreach($this->universities as $university):?>
					<option value="<?php echo  $university->id?>" <?php if(($this->edit == 1) && ($university->id == $this->user['university_id'])){echo 'selected="true"';}?>><?php echo trim($university->title)?></option>
				<?php endforeach;?>
			</select>
		</div>
		<div class="form-group em-hidden-nonapli-fields" <?php if(($this->edit != 1) || (empty($this->uGroups))){echo 'style="display:none;"';}?>>
			<label for="groups"><?php echo JText::_('GROUPS'); ?></label>
			<br/>
			<select class = "em-chosen" name = "groups" id = "groups" multiple="multiple">
				<option value="0"><?php echo JText::_('PLEASE_SELECT')?></option>
				<?php foreach($this->groups as $group):?>
					<option value = "<?php echo $group->id?>" <?php if(($this->edit == 1) && (array_key_exists($group->id, $this->uGroups))){echo 'selected="true"';}?>><?php echo trim($group->label)?></option>
				<?php endforeach;?>
			</select>
		</div>
		<div class="form-group em-hidden-appli-fields" <?php if(($this->edit != 1) || (empty($this->uCamps))){echo 'style="display:none;"';}?>>
			<label for="campaigns"><?php echo JText::_('CAMPAIGN'); ?></label>
			<br/>
			<select name="campaigns" size="5" multiple="multiple" id="campaigns" class="em-chosen">
				<option value="0"><?php echo JText::_('PLEASE_SELECT')?></option>
				<?php foreach($this->campaigns as $campaign):?>
				<option value="<?php echo $campaign->id?>" <?php if(($this->edit == 1) && (array_key_exists($campaign->id, $this->uCamps))){echo 'selected="true"';}?>><?php echo trim($campaign->label.' ('.$campaign->year.') - '.$campaign->training.' | '.JText::_('START_DATE').' : '.$campaign->start_date);?></option>
				<?php endforeach;?>
				</select>
		</div>
		<input type="checkbox" id="news" name = "news" <?php if(($this->edit != 1) || ($this->user['newsletter'])){echo "checked";}?> style="width: 20px !important">
		<label for="news">
		 <?php echo JText::_('NEWSLETTER'); ?>
		</label>
		</div>
	</fieldset>
</form>
<script type="text/javascript">
	window.onunload = function(){
	window.opener.location.reload();
	};
	$(document).ready(function()
      {
          var edit = '<?php echo $this->edit?>';
          $('.em-chosen').chosen({width:'80%'});

          if(edit == '1')
          {
              if($('#profiles option:selected').attr('pub') == 1)
              {
                  $('.em-hidden-appli-fields').show();
                  $('.em-hidden-nonapli-fields').hide();
              }
              else
              {
                  $('.em-hidden-nonapli-fields').show();
                  $('.em-hidden-appli-fields').hide();
              }
          }

          $(document).on('change', '#profiles', function()
                     {
							if($('#profiles option[value="'+$(this).val()+'"]').attr('pub') == 1)
							{
								$('.em-hidden-appli-fields').show();
								$('.em-hidden-nonapli-fields').hide();
							}
                            else
							{
								$('.em-hidden-nonapli-fields').show();
								$('.em-hidden-appli-fields').hide();
							}
                     })
          $(document).on('blur', '#mail', function()
                     {
                         var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-z\-0-9]+\.)+[a-z]{2,}))$/;
                         if ($(this).val().length == 0 || !re.test($(this).val()))
                         {
                             $(this).parent('.form-group').addClass('has-error');
                             $(this).after('<span class="help-block">'+Joomla.JText._('NOT_A_VALID_EMAIL')+'</span>');
                         }
                     })
          $(document).on('focus', '#mail', function()
                     {
                         $(this).parent('.form-group').removeClass('has-error');
                         $(this).siblings('.help-block').remove();
                     })

          $(document).on('keyup', '#login', function()
                     {
                        var re = /^[0-9a-zA-Z\_\@\-\.]+$/; // /^[a-z0-9]*$/;
                        if(!re.test($('#login').val()))
                        {
                            if(!$(this).parent('.form-group').hasClass('has-error'))
							{
	                            $(this).parent('.form-group').addClass('has-error');
	                            $(this).after('<span class="help-block">'+Joomla.JText._('NOT_A_VALID_LOGIN_MUST_NOT_CONTAIN_SPECIAL_CHARACTER')+'</span>');
                            }
                        }
						else
						{
							$(this).parent('.form-group').removeClass('has-error');
                         	$(this).siblings('.help-block').remove();
						}
					 }); 
		 

	  });
</script>