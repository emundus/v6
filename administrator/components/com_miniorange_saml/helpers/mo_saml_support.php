<?php
defined('_JEXEC') or die;
/*
 * @package    miniOrange
 * @subpackage Plugins
 * @license    GNU/GPLv3
 * @copyright  Copyright 2015 miniOrange. All Rights Reserved.
*/
JHtml::_('stylesheet', JUri::base() .'components/com_miniorange_saml/assets/css/miniorange_boot.css');

function mo_saml_local_support(){
	$strJsonFileContents = file_get_contents(JURI::root()."/administrator/components/com_miniorange_saml/assets/json/timezones.json"); 
	$timezoneJsonArray = json_decode($strJsonFileContents, true);
    $current_user = JFactory::getUser();
    $result       = (new Mo_saml_Local_Util)->_load_db_values('#__miniorange_saml_customer_details');
    $admin_email  = isset($result['email']) ? $result['email'] : '';
    $admin_phone  = isset($result['admin_phone']) ? $result['admin_phone'] : '';
	if($admin_email == '')
		$admin_email = $current_user->email;
	?>
	<div id="sp_support_saml" class="">
		<div class="mo_boot_row mo_boot_p-3" style="border: 2px solid rgb(15, 127, 182);background-color:white">
			<div class="mo_boot_col-sm-12">
				<div class="mo_boot_row">
                    <h4>Feature Request/Contact Us (24*7 Support)</h4>
					<div class="mo_boot_col-sm-4 mo_boot_p-2">
						<input type="button" value="Setup a Call" class="mo_boot_btn mo_boot_btn-primary setup_call_button" style="float: right;" id="setup_call_button">
					</div>
				</div>
				<hr>
			</div>
			<div class="mo_boot_col-sm-12">
				<form  name="f" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.contactUs');?>">
					<div class="mo_boot_row">
						<div class="mo_boot_col-sm-12">
                            <div style="float: left;width: 7%;margin-top: -7px;"><img src="<?php echo JUri::base();?>/components/com_miniorange_saml/assets/images/phone.svg" width="32" height="32"></div>
                            <p><b>&emsp;Need any help? Just give us a call at <span style="color:red">+1 978 658 9387</span></b></p><br>
                            <p>We can help you with configuring your Identity Provider. Just send us a query and we will get back to you soon.</p>
						</div>
					</div>
					<div class="mo_boot_row mo_boot_text-center">
						<div class="mo_boot_col-sm-12">
							<input style="border: 1px solid #868383 !important;" type="email" class="mo_saml_table_textbox mo_boot_form-control" name="query_email" value="<?php echo $admin_email; ?>" placeholder="Enter your email" required />
						</div>
						<div class="mo_boot_col-sm-12"><br>
							<input style="border: 1px solid #868383 !important;" type="tel" class="mo_saml_table_textbox mo_boot_form-control" name="query_phone" value="<?php echo $admin_phone; ?>" placeholder="Enter your phone"/>
						</div>
						<div class="mo_boot_col-sm-12"><br>
							<textarea  name="mo_saml_query_support" class="mo_saml_settings_textarea" style="border-radius:4px;resize: vertical;width:100%; border: 1px solid #868383 !important;" cols="52" rows="7" required placeholder="Write your query here"></textarea>
						</div>
					</div>
					<div class="mo_boot_row mo_boot_text-center">
						<div class="mo_boot_col-sm-12">
							<input type="hidden" name="option1" value="mo_saml_login_send_query"/><br>
							<input type="submit" name="send_query" value="Submit Query" class="mo_boot_btn mo_boot_btn-primary" />
							<input type="button" onclick="window.open('https://faq.miniorange.com/kb/joomla-saml/')" target="_blank" value="FAQ's"  style="margin-right: 25px; margin-left: 25px;" class="mo_boot_btn mo_boot_btn-success" />
						</div>
					</div>
					<div class="mo_boot_row">
						<div class="mo_boot_col-sm-12">
							<p><br>If you want custom features in the plugin, just drop an email to <a style="word-wrap:break-word!important;" href="mailto:joomlasupport@xecurify.com"> joomlasupport@xecurify.com</a> </p>
						</div>
					</div>
				</form>
			</div>
		</div>
		<form name="f" style="display:none" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.callContactUs'); ?>">
            <div class="mo_boot_row mo_boot_p-4 mo_support_layout" id="mo_setup_call_layout" style="border: 2px solid rgb(15, 127, 182);background-color:white">
            	<div class="mo_boot_col-sm-10">
                    <h3>Setup a Call / Screen-share</h3>
                </div>
                <div class="mo_boot_col-sm-2">
                    <input type="button" value="Back" class="mo_boot_btn mo_boot_btn-danger mo_call_setup_back">
                </div>
                <div class="mo_boot_col-sm-12">
                    <hr>
                	<div class="mo_boot_row">
                        <div class="mo_boot_col-sm-12">
                            <p class="oauth-table">Need any help? Just send us a query and we will get back to you soon.</p>
                        </div>
                    </div>
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-sm-3">
                            <strong>Email<span style="color:#FF0000">*</span>:</td></strong>
                        </div>
                        <div class="mo_boot_col-sm-9">
                            <input class="mo_boot_form-control"  type="email" placeholder="user@example.com" name="mo_sp_setup_call_email" value="<?php echo $admin_email; ?>"  required>
                        </div>
                    </div>
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-sm-3"><br>
                            <strong>Issue<span style="color:#FF0000">*</span>:</strong>
                        </div>
            	        <div class="mo_boot_col-sm-9"><br>
                	        <select class="mo_boot_form-control" name="mo_sp_setup_call_issue" required>
                    	        <option disabled selected>--------Select Issue type--------</option>
                        	    <option id="sso_setup_issue">SSO Setup Issue</option>
                            	<option>Custom requirement</option>
                            	<option id="other_issue">Other</option>
                        	</select>
						</div>
                    </div>
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-sm-3"><br>
                            <strong>Description<span style="color:#FF0000">*</span>:</strong>
                        </div>
                        <div class="mo_boot_col-sm-9"><br>
                            <textarea style="width:100%;" name="mo_sp_setup_call_desc" minlength="15" placeholder="Any queries like oecnajdnacdmv jvndf avdd won't be answered" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-sm-3"><br>
                            <strong>Date<span style="color:#FF0000">*</span>:</strong>
                        </div>
                        <div class="mo_boot_col-sm-9"><br>
                            <input class="mo_boot_form-control" id="mo_sp_setup_call_date" name="mo_sp_setup_call_date" type="date" required>
                        </div>
                    </div>
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-sm-3"><br>
                            <strong>TimeZone<span style="color:#FF0000">*</span>:</strong>
                        </div>
                        <div class="mo_boot_col-sm-9"><br>
                            <select class="selectpicker" data-size="5" data-dropup-auto="false" data-live-search="true" name="mo_sp_setup_call_timezone" required>
                                    <?php
                                        foreach($timezoneJsonArray as $data)
                                        {
                                            echo "<option style='width:270px' data-tokens='".$data."'>".$data."</option>";
                                        }
                                    ?>
                            </select>
                        </div> 
                    </div>
                    <div class="mo_boot_row mo_boot_text-center mo_boot_mt-3">
                        <div class="mo_boot_col-sm-12">
                            <input type="submit" name="send_query" value="Submit Query" class="mo_boot_btn mo_boot_btn-primary">
                        </div>
                    </div>
                </div>
            </div>                
        </form>
    	<script>
            var setup_call_buttons=document.getElementsByClassName("setup_call_button");
            var mo_iter;
            for( mo_iter=0;mo_iter<setup_call_buttons.length;mo_iter++){
                setup_call_buttons[mo_iter].addEventListener("click",function(){
	                this.parentElement.parentNode.parentNode.parentNode.style.display="none";
    	            this.parentNode.parentNode.parentNode.parentNode.parentNode.children[1].style.display="block";
            	});
            }
            var setup_call_back_buttons=document.getElementsByClassName("mo_call_setup_back");
            for( var mo_iterb=0;mo_iterb<setup_call_back_buttons.length;mo_iterb++){
                setup_call_back_buttons[mo_iterb].addEventListener("click",function(){
                    this.parentNode.parentNode.parentNode.parentNode.parentNode.children[0].children[0].style.display="block";
                    this.parentElement.parentNode.parentNode.parentNode.children[1].style.display="none";
                });
			}
            // To disable the previous dates.
            // jQuery(function(){
            //     var dtToday = new Date();
            //     var month = dtToday.getMonth() + 1;
            //     var day = dtToday.getDate();
            //     var year = dtToday.getFullYear();
            //     if(month < 10)
            //         month = '0' + month.toString();
            //     if(day < 10)
            //         day = '0' + day.toString();
            //     var maxDate = year + '-' + month + '-' + day;
            //     jQuery('#mo_sp_setup_call_date').attr('min', maxDate);
            // });
        </script>	
	</div>
<?php
}