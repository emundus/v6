
<?php
defined('_JEXEC') or die;
/*
 * @package    miniOrange
 * @subpackage Plugins
 * @license    GNU/GPLv3
 * @copyright  Copyright 2015 miniOrange. All Rights Reserved.
*/

JHtml::_('jquery.framework');
JHtml::_('stylesheet', JUri::base() .'components/com_miniorange_saml/assets/css/mo_saml_style.css');
JHtml::_('stylesheet', JUri::base() . 'components/com_miniorange_saml/assets/css/bootstrap-tour-standalone.css');
JHtml::_('stylesheet', JUri::base() . 'components/com_miniorange_saml/assets/css/bootstrap-select-min.css');
JHtml::_('stylesheet', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
JHtml::_('stylesheet', JUri::base() . 'components/com_miniorange_saml/assets/css/miniorange_boot.css');
JHtml::_('script', JUri::base() . 'components/com_miniorange_saml/assets/js/bootstrap-tour-standalone.min.js');
JHtml::_('script', JUri::base() . 'components/com_miniorange_saml/assets/js/visual_tour.js');
JHtml::_('script', JUri::base() . 'components/com_miniorange_saml/assets/js/samlUtility.js');
JHtml::_('script', JUri::base() . 'components/com_miniorange_saml/assets/js/bootstrap-select-min.js');
?>
<?php
if (!Mo_Saml_Local_Util::is_curl_installed())
{
?>
    <div id="help_curl_warning_title" class="mo_saml_title_panel">
        <p><a target="_blank" style="cursor: pointer;"><font color="#FF0000">Warning: PHP cURL extension is not installed or disabled. <span style="color:blue">Click here</span> for instructions to enable it.</font></a></p>
    </div>
    <div hidden="" id="help_curl_warning_desc" class="mo_saml_help_desc">
        <ul>
            <li>Step 1:&nbsp;&nbsp;&nbsp;&nbsp;Open php.ini file located under php installation folder.</li>
            <li>Step 2:&nbsp;&nbsp;&nbsp;&nbsp;Search for <b>extension=php_curl.dll</b> </li>
            <li>Step 3:&nbsp;&nbsp;&nbsp;&nbsp;Uncomment it by removing the semi-colon(<b>;</b>) in front of it.</li>
            <li>Step 4:&nbsp;&nbsp;&nbsp;&nbsp;Restart the Apache Server.</li>
        </ul>
        For any further queries, please <a href="mailto:joomlasupport@xecurify.com">contact us</a>.
    </div>
    <?php
}

$tab = "idp";
$get = JFactory::getApplication()->input->get->getArray();
if (isset($get['tab']) && !empty($get['tab']))
{
    $tab = $get['tab'];
}
?>

    <div class="mo_boot_row" style="width:100%!important">
        <div class="mo_boot_col-lg-10 mo_boot_p-0">
            <div class="nav-tab-wrapper mo_idp_nav-tab-wrapper ">
                <a id="idptab"  class="mo_nav-tab <?php echo $tab == 'idp' ? 'mo_nav_tab_active' : ''; ?>" href="#identity-provider"
                onclick="add_css_tab('#idptab');" 
                data-toggle="tab" style="width:12% !important">Service Provider Setup
                </a>

                <a id="descriptiontab" class="mo_nav-tab <?php echo $tab == 'description' ? 'mo_nav_tab_active' : ''; ?>" href="#description"
                onclick="add_css_tab('#descriptiontab');"
                data-toggle="tab" style="width:12% !important">Service Provider Metadata
                </a>

                <a id="sso_login" class="mo_nav-tab <?php echo $tab == 'sso_settings' ? 'mo_nav_tab_active' : ''; ?>" href="#sso_settings"
                onclick="add_css_tab('#sso_login');"
                data-toggle="tab">Login Settings
                </a>

                <a id="attributemappingtab" class="mo_nav-tab <?php echo $tab == 'attribute_mapping' ? 'mo_nav_tab_active' : ''; ?>" href="#attribute-mapping"
                onclick="add_css_tab('#attributemappingtab');"
                data-toggle="tab">Attribute Mapping
                </a>

                <a id="groupmappingtab" class="mo_nav-tab <?php echo $tab == 'group_mapping' ? 'mo_nav_tab_active' : ''; ?>" href="#group-mapping"
                onclick="add_css_tab('#groupmappingtab');"
                data-toggle="tab">Group Mapping
                </a>

                <a id="custcert" class="mo_nav-tab <?php echo $tab == 'ccert' ? 'mo_nav_tab_active' : ''; ?>" href="#ccert"
                onclick="add_css_tab('#custcert');"
                data-toggle="tab">Custom Certificate
                </a>

                <a id="licensingtab" class="mo_nav-tab <?php echo $tab == 'licensing' ? 'mo_nav_tab_active' : ''; ?>" href="#licensing-plans"
                onclick="add_css_tab('#licensingtab');"
                data-toggle="tab" style="background-color: orange !important;">Upgrade
                </a>

                <a id="registrationtab" class="mo_nav-tab <?php echo $tab == 'account' ? 'mo_nav_tab_active' : ''; ?>" href="#account"
                onclick="add_css_tab('#registrationtab');"
                data-toggle="tab">My Account
                </a>
            </div>
        </div>
        <div class="mo_boot_col-lg-2">
        <input type="button" id="sp_ot_tourend" value="Start Plugin Tour" onclick="restart_tourot();" class="mo_boot_btn mo_boot_btn-success mo_boot_float-lg-right"/>
        </div>
    </div>
    
    <!-- <style>
        .mo_nav_tab_active, .mo_nav_tab_active > * {
            box-shadow: 3px 4px 3px #888888 !important;
            background-color: #226a8b !important;
            color: white !important;
        }
    </style> -->
    <div class="mo_boot_row" style="background-color:#e0e0d8;">
        <div class="mo_boot_col-sm-12">
            <div class="tab-content" id="myTabContent">
                <div id="account" class="tab-pane <?php if ($tab == 'account') echo 'active'; ?> ">
                    <?php common_classes_for_UI('account_tab', 'mo_saml_local_support');?>
                </div>

                <div id="description" class="tab-pane <?php if ($tab == 'description') echo 'active'; ?> ">
                    <?php common_classes_for_UI('description', 'mo_saml_local_support');?>
                </div>

                <div id="sso_settings" class="tab-pane <?php if ($tab == 'sso_settings') echo 'active'; ?>">
                    <?php common_classes_for_UI('mo_sso_login', 'requestfordemo');?>
                </div>

                <div id="identity-provider" class="tab-pane <?php if ($tab == 'idp') echo 'active'; ?>">
                    <?php common_classes_for_UI('select_identity_provider', 'mo_saml_local_support');?>
                </div>

                <div id="attribute-mapping" class="tab-pane <?php if ($tab == 'attribute_mapping') echo 'active'; ?>">
                    <?php common_classes_for_UI('attribute_mapping', 'requestfordemo');?>
                </div>

                <div id="group-mapping" class="tab-pane <?php if ($tab == 'group_mapping') echo 'active'; ?>">
                    <?php common_classes_for_UI('group_mapping', 'requestfordemo');?>
                </div>

                <div id="proxy-setup" class="tab-pane <?php if ($tab == 'proxy_setup') echo 'active'; ?>">
                    <?php common_classes_for_UI('proxy_setup', 'mo_saml_local_support');?>
                </div>

                <div id="licensing-plans" class="tab-pane <?php if ($tab == 'licensing') echo 'active'; ?>">
                    <div class="row-fluid">
                        <table style="width:100%;">
                            <tr>
                                <td style="width:65%;vertical-align:top;" class="configurationForm">
                                    <?php Licensing_page(); ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div id="ccert" class="tab-pane <?php if ($tab == 'ccert') echo 'active'; ?>">
                    <?php common_classes_for_UI('customcertificate', 'mo_saml_local_support');?>
                </div>
            </div>
        </div>
    </div>
    

<?php function common_classes_for_UI($tab_func, $support_func)
{
    ?>
     <div class="mo_boot_row mo_boot_px-4 mo_boot_py-3">
        <div class="mo_boot_col-sm-8">
            <div>
                <?php
                    $tab_func();
                ?>
            </div>
        </div>
        <div class="mo_boot_col-sm-4">
            <div id="mo_saml_support1">
                <?php 
                    $support_func();
                ?>
            </div>
        </div>
    </div>
    <?php
}
?>

<?php

function account_tab()
{
    ?>
   <div class="mo_boot_row mo_boot_m-0 mo_boot_p-0" id="registrationForm">
       <?php
            $customer_details = (new Mo_saml_Local_Util)->_load_db_values('#__miniorange_saml_customer_details');
            $login_status = $customer_details['login_status'];
            $registration_status = $customer_details['registration_status'];
            if ($login_status)
            {
                mo_saml_local_login_page();
            }
            else if ($registration_status == 'MO_OTP_DELIVERED_SUCCESS' || $registration_status == 'MO_OTP_VALIDATION_FAILURE' || $registration_status == 'MO_OTP_DELIVERED_FAILURE')
            {
                mo_saml_local_show_otp_verification();
            }
            else if (!Mo_Saml_Local_Util::is_customer_registered())
            {
                mo_saml_local_registration_page();
            }
            else
            {
                mo_saml_local_account_page();
            }
        ?>
    </div>
    <?php
}

function mo_saml_local_login_page()
{
    ?>
    <div class="mo_boot_row  mo_boot_mr-1 mo_boot_p-3" style="border: 2px solid rgb(15, 127, 182); background-color:white">
        <div class="mo_boot_col-sm-12">
            <form name="f" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.verifyCustomer'); ?>">
                <div class="mo_boot_row mo_boot_mt-2">
                    <div class="mo_boot_col-sm-12">
                        <input type="hidden" name="option1" value="mo_saml_local_verify_customer" />
                        <h3>Login with miniOrange</h3><hr>
                        <p>
                            Please enter your miniOrange account credentials. If you forgot your password then enter your email and click
                            on <b>Forgot your password</b> button. If you are not registered with miniOrange then click on <b>Back to registration</b> button. 
                        </p>
                    </div>
                </div>
                <div id="panel1" style="align:center!important;">
                    <div class="mo_boot_row mo_boot_mt-2">
                        <div class="mo_boot_col-sm-2 mo_boot_offset-sm-1">
                            <b><i style="color:#FF0000">*</i>Email:</b>
                        </div>
                        <div class="mo_boot_col-sm-6">
                            <input class="mo_saml_table_textbox mo_boot_form-control" type="email" name="email" style="border: 1px solid #868383 !important;" required placeholder="person@example.com" value="" />
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-2">
                        <div class="mo_boot_col-sm-2 mo_boot_offset-sm-1">
                            <b><i style="color:#FF0000">*</i>Password:</b>
                        </div>
                        <div class="mo_boot_col-sm-6">
                            <input class="mo_saml_table_textbox mo_boot_form-control" required type="password" name="password" style="border: 1px solid #868383 !important;" placeholder="Enter your miniOrange password" />
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-2">
                        <div class="mo_boot_col-sm-12 mo_boot_text-center">
                            <input type="submit" class="mo_boot_btn mo_boot_btn-primary mo_boot_mt-1" value="Login"/>
                            <input type="button" value="Back to Registration" onclick="moSAMLCancelForm();" class="mo_boot_btn mo_boot_btn-danger mo_boot_mt-1" />
                            <!-- <a href="#mo_saml_local_forgot_password_link" class="mo_boot_btn mo_boot_btn-primary mo_boot_mt-1">Forgot your password?</a> -->
                            <a href="https://login.xecurify.com/moas/idp/resetpassword" target="_blank"  class="mo_boot_btn mo_boot_btn-primary mo_boot_mt-1">Forgot your password?</a>
                        </div>
                    </div>
                </div>
            </form>
            <form id="forgot_password_form" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.forgotPassword'); ?>">
                <input type="hidden" name="option1" value="user_forgot_password" />
            </form>
            <form id="cancel_form" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.cancelform'); ?>">
                <input type="hidden" name="option1" value="mo_saml_local_cancel" />
            </form>
        </div>
    </div>
    <?php
}

function mo_saml_local_account_page()
{
    $result = new Mo_saml_Local_Util();
	$result = $result->_load_db_values('#__miniorange_saml_customer_details');
	$email = $result['email'];
    $customer_key = $result['customer_key'];
    $api_key = $result['api_key'];
    $customer_token = $result['customer_token'];
    $hostname = Mo_Saml_Local_Util::getHostname();
    ?>
    <div class="mo_boot_row  mo_boot_mr-1 mo_boot_p-3" id="cum_pro" style="background-color:#FFFFFF;border:2px solid rgb(15, 127, 182);">
        <div class="mo_boot_col-sm-12 mo_saml_welcome_message">
            <h4>Thank You for registering with miniOrange.</h4>
        </div>
        <div class="mo_boot_col-sm-12 table-responsive">
            <h3>Your Profile</h3>
            <table class="table table-bordered" style="border:1px solid #CCCCCC;">
                <tr>
                    <th>Username/Email</th>
                    <th>Customer ID</th>
                </tr>
                <tr>
                    <td><?php echo $email ?></td>
                    <td><?php echo $customer_key ?></td>
                </tr>
            </table>
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_text-center" id="sp_proxy_setup">
            <input id="sp_proxy" type="button" class='mo_boot_btn mo_boot_btn-primary mo_boot_d-inline-block' onclick='show_proxy_form()' value="Configure Proxy"/>
            <form class="mo_boot_d-inline-block" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.ResetAccount'); ?>" name="reset_useraccount" method="post">
                <input type="button"  value="Remove Account" onclick='submit();' class="mo_boot_btn mo_boot_btn-danger"  /> <br/>
            </form>
        </div>
    </div>
    <div class="mo_boot_row" id="submit_proxy" style="background-color:#FFFFFF;border:2px solid rgb(15, 127, 182); display:none ;" >
	    <?php proxy_setup() ?>
    </div>
    <?php
}

/* Show OTP verification page*/
function mo_saml_local_show_otp_verification()
{
    ?>
    <div id="panel2" class="mo_boot_p-4" style="background-color:#FFFFFF; border:2px solid rgb(15, 127, 182);">
        <form name="f" method="post" id="idp_form" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.validateOtp'); ?>">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <input type="hidden" name="option1" value="mo_saml_local_validate_otp" />
                    <h3>Verify Your Email</h3>
                    <hr>
                </div>
            </div>
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-2">
                    <b><font color="#FF0000">*</font>Enter OTP:</b>
                </div>
                <div class="mo_boot_col-sm-6">
                    <input class="mo_boot_form-control" autofocus="true" type="text" name="otp_token" required placeholder="Enter OTP"/>
                </div>
                <div class="mo_boot_col-sm-4">
                    <a style="cursor:pointer;" class="mo_boot_btn mo_boot_btn-primary" onclick="document.getElementById('resend_otp_form').submit();">Resend OTP over Email</a>
                </div>
            </div>
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12 mo_boot_text-center"><br>
                    <input type="submit" value="Validate OTP" class="mo_boot_btn mo_boot_btn-success"/>
                    <input type="button" value="Back" class="mo_boot_btn mo_boot_btn-danger" onclick="moSAMLBack();"/>
                </div>
            </div>
        </form>

        <form method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.cancelform'); ?>" id="mo_saml_cancel_form">
            <input type="hidden" name="option1" value="mo_saml_local_cancel" />
        </form>

        <form name="f" id="resend_otp_form" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.resendOtp'); ?>">
            <input type="hidden" name="option1" value="mo_saml_local_resend_otp"/>
        </form>
        <hr>
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12">
                <h3>I did not recieve any email with OTP . What should I do ?</h3>
            </div>
        </div>
        <form id="phone_verification" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.phoneVerification'); ?>">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-12">
                        <input type="hidden" name="option1" value="mo_saml_local_phone_verification" />
                        <p>
                            If you can't see the email from miniOrange in your mails, please check your <b>SPAM Folder</b>. If you don't see an email even in SPAM folder, verify your identity with our alternate method.<br><br>
                            <b>Enter your valid phone number here and verify your identity using one time passcode sent to your phone.</b>
                        </p>
                    </div>
                </div>
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-6">
                        <input class="mo_boot_form-control" required="true" pattern="[\+]\d{1,3}\d{10}" autofocus="true" type="text"
                        name="phone_number" id="phone" placeholder="Enter Phone Number with country code eg. +1xxxxxxxxx" title="Enter phone number without any space or dashes with country code. (Please include country code ex:+91xxxxxxxxxx)"/>
                    </div>
                    <div class="mo_boot_col-sm-3">
                        <input type="submit" value="Send OTP on Phone" class="mo_boot_btn mo_boot_btn-primary"/>
                    </div>
                </div>
        </form>
    </div>

    <?php
}
/* End Show OTP verification page*/
/* Create Customer function */
function mo_saml_local_registration_page()
{
    $database_name = '#__miniorange_saml_customer_details';
    $updatefieldsarray = array(
        'new_registration' => 1,
    );
    $result = new Mo_saml_Local_Util();
    $result->generic_update_query($database_name, $updatefieldsarray);
    ?>

    <!--Register with miniOrange-->
    <div class="mo_boot_row  mo_boot_mr-1 mo_boot_p-3" id="submit_proxy" style="background-color:#FFFFFF; border:2px solid rgb(15, 127, 182);display:none;">
        <div class="mo_boot_col-sm-12">
            <?php 
                proxy_setup() 
            ?>
        </div>
    </div>
    <div class="mo_boot_row  mo_boot_mr-1 mo_boot_p-3" id="panel1" style="border: 2px solid rgb(15, 127, 182); background-color:white">
        <div class="mo_boot_col-sm-12">
            <form name="f" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.registerCustomer'); ?>">
                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-lg-6  mo_boot_mt-1">
                        <input type="hidden" name="option1" value="mo_saml_local_register_customer" />
                        <h3>Register with miniOrange (Optional)</h3>
                    </div>
                    <div class="mo_boot_col-lg-4  mo_boot_mt-1">
                        <input type="button" value="Already registered with miniOrange?" class="mo_boot_btn mo_boot_btn-primary" onclick="mo_login_page();"/>
                    </div>
                    <div class="mo_boot_col-lg-2 mo_boot_mt-1">
                        <input type="button" id="sprg_end_tour" value="Start Tab Tour" onclick="restart_tourrg();" class="mo_boot_btn mo_boot_btn-success" />
                    </div>
                </div>
                <hr/>
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-12">
                        <h4>Why should I register?</h4>
                        <p class='alert alert-info'>
                            You should register so that in case you need help, we can help you with step by step instructions. We support all known IdPs - ADFS, Okta, Salesforce,
                            Shibboleth, SimpleSAMLphp, OpenAM, Centrify, Ping, RSA, IBM, Oracle, OneLogin, Bitium, WSO2 etc. <b>You will also need a miniOrange account to upgrade 
                            to the license version of the plugins</b>. We do not store any information except the email that you will use to register with us.
                        </p>
                        <p style="color: #fa2727">
                            If you face any issues during registraion then you can 
                            <a href="https://www.miniorange.com/businessfreetrial" target="_blank"><b>click here</b></a> 
                            to quick register your account with miniOrange and use the same credentials to login into the plugin.
                        </p>
                    </div>
                </div>
                <div id="spregister" class="mo_saml_settings_table">
                    <div class="mo_boot_row" id="spemail">
                        <div class="mo_boot_col-sm-3">
                            <b>Email<i style="color:#FF0000">*</i>:</b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <?php 
                                $current_user = JFactory::getUser();
                                $result = new Mo_saml_Local_Util();
                                $result = $result->_load_db_values('#__miniorange_saml_customer_details');
                                $admin_email = $result['email'];
                                $admin_phone = $result['admin_phone'];
                                if ($admin_email == '')
                                {
                                    $admin_email = $current_user->email;
                                }
                            ?>
                            <input class="mo_saml_table_textbox mo_boot_form-control" type="email" name="email" style="border: 1px solid #868383 !important;" placeholder="person@example.com" required value="<?php echo $admin_email; ?>" />
                        </div>
                    </div><br>
                    <div class="mo_boot_row" id="sprg_phone">
                        <div class="mo_boot_col-sm-3">
                            <b>Phone number:</b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <input class="mo_saml_table_textbox mo_boot_form-control" type="tel" id="phone" style="border: 1px solid #868383 !important;" pattern="[\+]\d{11,14}|[\+]\d{1,4}([\s]{0,1})(\d{0}|\d{9,10})" name="phone" title="Phone with country code eg. +1xxxxxxxxxx"  placeholder="Phone with country code eg. +1xxxxxxxxxx" value="<?php echo $admin_phone; ?>" />
                            <p><i><strong>NOTE:</strong>We will call only if you call for support</i></p>
                        </div>
                    </div>
                    <div class="mo_boot_row" id="sprg_passwd">
                        <div class="mo_boot_col-sm-3">
                            <b>Password<i style="color:#FF0000">*</i>:</b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <input class="mo_saml_table_textbox mo_boot_form-control"  required  type="password" style="border: 1px solid #868383 !important;" name="password" placeholder="Choose your password (Min. length 6)" />
                        </div>
                    </div><br>
                    <div class="mo_boot_row" id="rg_repasswd">
                        <div class="mo_boot_col-sm-3">
                            <b>Confirm Password<i style="color:#FF0000">*</i>:</b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <input class="mo_saml_table_textbox mo_boot_form-control"  required type="password" style="border: 1px solid #868383 !important;" name="confirmPassword" placeholder="Confirm your password" />
                        </div>
                    </div>
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-sm-12 mo_boot_text-center">
                            <input type="submit" value="Register" class="mo_boot_btn mo_boot_btn-primary" />
                            <div class="mo_boot_d-inline-block" id="sp_proxy_setup"><br>
                                <input id="sp_proxy" type="button" class='mo_boot_btn mo_boot_btn-primary' onclick='show_proxy_form_one()' value="Configure Proxy"/>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <form name="f" id="customer_login_form" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.customerLoginForm'); ?> ">
            </form>
        </div>
    </div>
    <?php
}

function description()
{
    $siteUrl = JURI::root();
    $sp_base_url = '';

    $result = (new Mo_saml_Local_Util)->_load_db_values('#__miniorange_saml_config');
	$sp_entity_id = isset($result['sp_entity_id']) ? $result['sp_entity_id'] : '';

	if($sp_entity_id == ''){
        $sp_entity_id = $siteUrl . 'plugins/authentication/miniorangesaml';
	}

	if(isset($result['sp_base_url'])){
        $sp_base_url = $result['sp_base_url'];
	}

    if (empty($sp_base_url))
        $sp_base_url = $siteUrl;

    ?>
        <div class="mo_boot_row  mo_boot_mr-1  mo_boot_p-3"  style="background-color:#FFFFFF; border:2px solid rgb(15, 127, 182);">
            <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-lg-10 mo_boot_mt-1">
                        <h3>Service Provider Metadata <sup><a href="https://docs.miniorange.com/documentation/service-provider-metadata" style="font-size:12px;" target="_blank" title="Know More">[know More]</a></sup></h3>
                    </div>
                    <div class="mo_boot_col-lg-2 mo_boot_mt-1">
                        <input type="button" id="sp_ds_tourend" value="Start Tab Tour" onclick="restart_tourds();" class="mo_boot_btn mo_boot_btn-success"/>
                    </div>
                </div><hr>
            </div>

            <div class="mo_boot_col-sm-12  mo_boot_mt-2">
                <p style="color: #d9534f;">
                    <b>Provide this plugin information to your Identity Provider team. You can choose any one of the below options:</b>
                </p>
                <p  class="mo_boot_mt-3">
                    <b>a) Provide this metadata URL to your Identity Provider OR download the .xml file to upload it in your idp:</b>
                </p>
            </div>
            <div class="mo_boot_col-sm-12  mo_boot_mt-2 mo_boot_table-responsive">
                <div class="mo_saml_highlight_background_url_note">
                    <b>Metadata URL:
                        <span id="idp_metadata_url" >
                            <a  href='<?php echo $sp_base_url . '?morequest=metadata'; ?>' id='metadata-linkss' target='_blank'><?php echo '<b>' . $sp_base_url . '?morequest=metadata </b>'; ?></a>
                        </span>
                    </b>
                </div>
                <i class="fa fa-lg fa-copy mo_copy" onclick="copyToClipboard('#idp_metadata_url');" style="color:red;margin-left: 1%;"> </i>
            </div>
            <div class="mo_boot_col-sm-12 mo_boot_mt-2">
                <p id="mo_download_metadata" class="mo_boot_mt-3">
                    <b>Download metadata XML file:</b>
                    <a href="<?php echo $sp_base_url . '?morequest=download_metadata'; ?>" class="mo_boot_btn mo_boot_btn-primary btn-large mo_saml_btn" style="padding: 4px 10px;">
                        Download XML Metadata
                    </a>
                </p>
                <h2 style="text-align: center">OR</h2>
                <p  class="mo_boot_mt-3">
                    <b>b) You will need the following information to configure your Identity Provider. Copy it and keep it handy:</b>
                </p>
            </div>
            <div class="mo_boot_col-sm-12  mo_boot_mt-3 mo_boot_table-responsive">
                <table id="mo_other_idp" class='table table-bordered table-hover table-striped mo_saml_metadata_td'>
                    <tr class='info'>
                        <td style="width:33%"><b>SP-EntityID / Issuer</b></td>
                        <td>
                            <span id="sp_entityid" ><?php echo $sp_entity_id; ?></span>
                            <i class="fa fa-pull-right fa-lg fa-copy mo_copy" onclick="copyToClipboard('#sp_entityid');" style="color:red"> </i>
                        </td>
                    </tr>
					<tr>
					    <td><b>ACS (AssertionConsumerService) URL / Single Sign-On URL (SSO)</b></td>
                        <td>
                            <span id="acs_url"  ><?php echo $sp_base_url . '?morequest=acs'; ?></span>
                            <i class="fa fa-pull-right  fa-lg fa-copy mo_copy" onclick="copyToClipboard('#acs_url');" style="color:red"> </i>
                        </td>
                    </tr>
					<tr class='info'>
                        <td ><b>Audience URI</b></td>
                        <td>
						    <span id="audience_url"><?php echo $sp_entity_id; ?></span>
                            <i class="fa fa-pull-right  fa-lg fa-copy mo_copy" onclick="copyToClipboard('#audience_url');" style="color:red;"></i>
					    </td>
                    </tr>
                    <tr id="sp_nameid_format">
                        <td><b>NameID Format</b></td>
                        <td>urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified</td>
                    </tr>
                    <tr id="sp_slo" class='info' style="line-height: 37px;">
                        <td><b>Single Logout URL (SLO)</b></td>
                        <td>Available in the <b><a href='#' class='premium' onclick="moSAMLUpgrade();"><b>Premium</b></a>, <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>Enterprise</b></a></b> versions</td>
                    </tr>
                    <tr id="sp_default_relaystate" >
                        <td><b>Default Relay State (Optional)</b></td>
                        <td>Available in the <b><a href='#' class='premium' onclick="moSAMLUpgrade();"><b>Premium</b></a>, <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>Enterprise</b></a></b> versions</td>
                    </tr >
                    <tr id="sp_certificate" class='info'>
                        <td><b>x.509 certificate (Optional)</b></td>
                        <td>Available in the <b><a href='#' class='premium' onclick="moSAMLUpgrade();"><b>Standard</b></a>, <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>Premium</b></a>, <a href='#' class='premium'><b>Enterprise</b></a></b> versions</td>
                    </tr>
				</table>
            </div>
            <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                <h3>Update SP Entity ID or Base URL</h3><hr>
                <form action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.updateSPIssuerOrBaseUrl'); ?>" method="post" name="updateissueer" id="identity_provider_update_form">
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-sm-3">
                            <b>SP EntityID / Issuer:<span style="color: red;">*</span></b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <input class="mo_saml_table_textbox mo_boot_form-control" type="text" style="border: 1px solid #868383 !important;" name="sp_entity_id" value="<?php echo $sp_entity_id; ?>" required />
                            <b>Note:</b>If you have already shared the above URLs or Metadata with your IdP, do NOT change SP EntityID. It might break your existing login flow.<br><br>
                        </div>

                        <div class="mo_boot_col-sm-3">
                            <b>SP Base URL: <span style="color: red;">*</span></b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <input class="mo_saml_table_textbox mo_boot_form-control" type="text" style="border: 1px solid #868383 !important;" name="sp_base_url" value="<?php echo $sp_base_url; ?>" required />
                        </div>
                    </div>
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-sm-12 mo_boot_text-center"><br>
                            <input type="submit" class="mo_boot_btn mo_boot_btn-primary" value="Update"/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <style>
                .selected-text, .selected-text>*{
                background: #2196f3;
                color: #ffffff;
                }
        </style>
    <?php
}
function mo_saml_get_saml_request_url()
{
    $url = '?morequest=sso&q=sso';
    return $url;
}
function mo_saml_get_saml_response_url()
{
    $url = '?morequest=sso&RelayState=response';
    return $url;
}
function Licensing_page()
{
	$useremail = new Mo_saml_Local_Util();
	$useremail = $useremail->_load_db_values('#__miniorange_saml_customer_details');
    if (isset($useremail)) $user_email = $useremail['email'];
    else $user_email = "xyz";
    ?>
    <div id="myModal" class="modal">
        <div class="modal-content mo_boot_text-center">
            <span class="modal-close" onclick="hidemodal()" >&times;</span><br><br><br>
            <p style="font-size:20px;line-height:30px;">
                You Need to Login / Register in <b>My Account</b> tab to Upgrade your License </p>
            <br><br>
            <a href="<?php echo JURI::base()?>index.php?option=com_miniorange_saml&tab=account" class="mo_boot_btn mo_boot_btn-primary">LOGIN / REGISTER</a>
        </div>
    </div>
    <div class="mo_boot_row mo_boot_p-4">
        <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_text-center">     
            <style>
                .switch
                {
                    position: relative;
                    display: inline-block;
                    width: 210px;
                    height: 34px;
                }

                .switch input
                {
                    opacity: 0;
                    width: 0;
                    height: 0;
                }

                .slider
                {
                    position: absolute;
                    cursor: pointer;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: #007bff;
                    -webkit-transition: .4s;
                    transition: .4s;

                }

                .slider:before
                {
                    position: absolute;
                    content: "Plans";
                    font-weight:400;
                    height: 26px;
                    width: 100px;
                    left: 4px;
                    bottom: 4px;
                    background-color: white;
                    -webkit-transition: .4s;
                    transition: .4s;
                }

                input:checked + .slider
                {
                    background-color: #2196F3;
                }

                input:focus + .slider
                {
                    box-shadow: 0 0 1px #2196F3;
                }

                input:checked + .slider:before
                {
                    -webkit-transform: translateX(100px);
                    -ms-transform: translateX(100px);
                    transform: translateX(100px);
                    content:"Bundle Plan";
                }

                /* Rounded sliders */
                .slider.round
                {
                    border-radius: 34px;
                }

                .slider.round:before
                {
                    border-radius: 34px;
                }
            </style>
            <label class="switch">
                <input type="checkbox" id="bundle_checked" onclick="show_bundle()" />
                <span class="slider round"></span>
            </label>
            <br>
        </div>
        <div style="text-align: center; font-size: 14px; color: white; padding-top: 4px;border-radius: 16px;"></div>
        <div class="tab-content" style="background-color: #c1c5c6;border: 2px solid rgb(15, 127, 182) !important;">
            <div class="mo_boot_m-5 mo_boot_text-center" style="display:none;" id="bundle_content">
                <div class="mo_boot_col-sm-12">
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-sm-4">
                            <div class="mo_boot_row mo_boot_p-2">
                                <div class="mo_boot_col-sm-8 mo_boot_p-5 mo_boot_bg-light">
                                    <h3>
                                        <b>Joomla SAML SP Standard</b><br>
                                        <b>+</b><br>
                                        <b>Joomla SCIM Standard</b>
                                    </h3>
                                </div>
                                <div class="mo_boot_col-sm-4 mo_boot_p-4" style="background:#38ACEC">
                                    <h3>
                                        <b>$249</b><br>
                                        <b>+</b><br>
                                        <b>$199</b><br><br>
                                        <b><del style="color:red">$448</del><br>$399</b>
                                    </h3>
                                    <a href="https://www.miniorange.com/contact" target="_blank" class="btn btn-success">Buy Now</a>
                                </div>
                            </div>
                        </div>
                        <div class="mo_boot_col-sm-4">
                            <div class="mo_boot_row mo_boot_p-2">
                                <div class="mo_boot_col-sm-8 mo_boot_p-5 mo_boot_bg-light">
                                    <h3>
                                        <b>Joomla SAML SP Premium</b><br>
                                        <b>+</b><br>
                                        <b>Joomla SCIM Standard</b>
                                    </h3>
                                </div>
                                <div class="mo_boot_col-sm-4 mo_boot_p-4" style="background:#38ACEC">
                                    <h3>
                                        <b>$399</b><br>
                                        <b>+</b><br>
                                        <b>$199</b><br><br>
                                        <b><del style="color:red">$598</del><br>$499</b>
                                    </h3>
                                    <a href="https://www.miniorange.com/contact" target="_blank" class="btn btn-success">Buy Now</a>
                                </div>
                            </div>
                        </div>
                        <div class="mo_boot_col-sm-4">
                            <div class="mo_boot_row mo_boot_p-2">
                                <div class="mo_boot_col-sm-8 mo_boot_p-5 mo_boot_bg-light">
                                    <h3>
                                        <b>Joomla SAML SP Enterprise</b><br>
                                        <b>+</b><br>
                                        <b>Joomla SCIM Standard</b>
                                    </h3>
                                </div>
                                <div class="mo_boot_col-sm-4 mo_boot_p-4" style="background:#38ACEC">
                                    <h3>
                                        <b>$499</b><br>
                                        <b>+</b><br>
                                        <b>$199</b><br><br>
                                        <b><del style="color:red">$648</del><br>$548</b>
                                    </h3>
                                    <a href="https://www.miniorange.com/contact" target="_blank" class="btn btn-success">Buy Now</a>
                                </div>
                            </div>
                        </div>
                        <div class="mo_boot_col-sm-4">
                            <div class="mo_boot_row mo_boot_p-2">
                                <div class="mo_boot_col-sm-8 mo_boot_p-5 mo_boot_bg-light">
                                    <h3>
                                        <b>Joomla SAML SP Standard</b><br>
                                        <b>+</b><br>
                                        <b>Joomla SCIM Premium</b>
                                    </h3>
                                </div>
                                <div class="mo_boot_col-sm-4 mo_boot_p-4" style="background:#38ACEC">
                                    <h3>
                                        <b>$249</b><br>
                                        <b>+</b><br>
                                        <b>$299</b><br><br>
                                        <b><del style="color:red">$548</del><br>$499</b>
                                    </h3>
                                    <a href="https://www.miniorange.com/contact" target="_blank" class="btn btn-success">Buy Now</a>
                                </div>
                            </div>
                        </div>
                        <div class="mo_boot_col-sm-4">
                            <div class="mo_boot_row mo_boot_p-2">
                                <div class="mo_boot_col-sm-8 mo_boot_p-5 mo_boot_bg-light">
                                    <h3>
                                        <b>Joomla SAML SP Premium</b><br>
                                        <b>+</b><br>
                                        <b>Joomla SCIM Premium</b>
                                    </h3>
                                </div>
                                <div class="mo_boot_col-sm-4 mo_boot_p-4" style="background:#38ACEC">
                                    <h3>
                                        <b>$399</b><br>
                                        <b>+</b><br>
                                        <b>$299</b><br><br>
                                        <b><del style="color:red">$698</del><br>$599</b>
                                    </h3>
                                    <a href="https://www.miniorange.com/contact" target="_blank" class="btn btn-success">Buy Now</a>
                                </div>
                            </div>
                        </div>
                        <div class="mo_boot_col-sm-4">
                            <div class="mo_boot_row mo_boot_p-2">
                                <div class="mo_boot_col-sm-8 mo_boot_p-5 mo_boot_bg-light">
                                    <h3>
                                        <b>Joomla SAML SP Enterprise</b><br>
                                        <b>+</b><br>
                                        <b>Joomla SCIM Premium</b>
                                    </h3>
                                </div>
                                <div class="mo_boot_col-sm-4 mo_boot_p-4" style="background:#38ACEC">
                                    <h3>
                                        <b>$449</b><br>
                                        <b>+</b><br>
                                        <b>$299</b><br><br>
                                        <b><del style="color:red">$748</del><br>$649</b>
                                    </h3>
                                    <a href="https://www.miniorange.com/contact" target="_blank" class="btn btn-success">Buy Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mo_boot_m-5 mo_boot_text-center" id="license_content">
                <div class="mo_boot_col-sm-12"  style="border:2px solid blue;background:white;box-sizing:border-box;">
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2  mo_boot_table-bordered mo_boot_p-2"><br><h3>Feature List</h3></div>
                        <div class="mo_boot_col-sm-2 mo_boot_px-0  mo_boot_table-bordered mo_boot_pt-2"><br>
                            <h3>FREE</h3><br><br><br><br><br>
                            <span id="plus_total_price" style="font-weight: bolder;font-size: xx-large ;">$0*</span><br><br>
                            <div class="mo_boot_mt-5">
                                <button class="cd-select" style="font-size: 85.5%; cursor: pointer;width:100%;border:none;background-color: #0d3663" > Active Plan</button>
                            </div>                        
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_px-0  mo_boot_table-bordered mo_boot_pt-2">
                        <br>
                            <h3>STANDARD</h3><span class="mo_saml_plan_description"><b>(AUTO REDIRECT TO IDP)</b></span><br><br><br><br><br>
                            <span id="plus_total_price" style="font-weight: bolder;font-size: xx-large ;">$249*</span><br><span class="mo_saml_note"><b>[One Time Payment]</b></span><br>
                            <div class="mo_boot_mt-5">
                                <?php
                                    if (!Mo_Saml_Local_Util::is_customer_registered())
                                    {
                                        echo '<button class="cd-select" style="font-size: 85.5%; cursor: pointer;width:100%;border:none" onclick="showmodal()">UPGRADE NOW</button>';
                                    }
                                    else
                                    {
                                        $redirect1= "https://login.xecurify.com/moas/login?username=" . $user_email . "&redirectUrl=https://login.xecurify.com/moas/initializepayment&requestOrigin=joomla_saml_sso_standard_plan";
                                        echo '<a target="_blank" class="cd-select" style="font-size: 85.5%; cursor: pointer;width:100%;border:none"  href="'.$redirect1.'" >UPGRADE NOW</a>';
                                    }
                                ?>
                            </div> 
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_px-0  mo_boot_table-bordered mo_boot_pt-2">
                            <br><h3>PREMIUM</h3><span class="mo_saml_plan_description"><b>(ATTRIBUTE & ROLE MANAGEMENT)</b></span><br><br><br><br><br>
                            <span id="plus_total_price" style="font-weight: bolder;font-size: xx-large ;">$399*</span><br>
                            <span class="mo_saml_note"><b>[One Time Payment]</b></span><br>
                            <div class="mo_boot_mt-5">
                                <?php
                                    if (!Mo_Saml_Local_Util::is_customer_registered())
                                    {
                                        echo '<button class="cd-select" style="font-size: 85.5%; cursor: pointer;width:100%;border:none" onclick="showmodal()">UPGRADE NOW</button>';
                                    }
                                    else
                                    {
                                        $redirect2= "https://login.xecurify.com/moas/login?username=" . $user_email . "&redirectUrl=https://login.xecurify.com/moas/initializepayment&requestOrigin=joomla_saml_sso_premium_plan";
                                        echo '<a target="_blank" class="cd-select" style="font-size: 85.5%; cursor: pointer;width:100%;border:none"  href="'.$redirect2.'" >UPGRADE NOW</a>';
                                    } 
                                ?>
                            </div> 
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_px-0  mo_boot_table-bordered mo_boot_pt-2">
                            <br><h3>ENTERPRISE</h3><span class="mo_saml_plan_description"><b>(AUTO-SYNC IDP METADATA & MULTIPLE CERTIFICATE)</b></span><br><br><br><br>
                            <span id="plus_total_price" style="font-weight: bolder;font-size: xx-large ;">$449*</span><br>
                            <span class="mo_saml_note"><b>[One Time Payment]</b></span><br>
                            <div class="mo_boot_mt-5">
                                <?php
                                    if (!Mo_Saml_Local_Util::is_customer_registered())
                                    {
                                        echo '<button class="cd-select" style="font-size: 85.5%; cursor: pointer;width:100%;border:none" onclick="showmodal()"  >UPGRADE NOW</button>';
                                    }
                                    else
                                    {
                                        $redirect3= "https://login.xecurify.com/moas/login?username=" . $user_email . "&redirectUrl=https://login.xecurify.com/moas/initializepayment&requestOrigin=joomla_saml_sso_enterprise_plan";
                                        echo '<a target="_blank" class="cd-select" style="font-size: 85.5%; cursor: pointer;width:100%;border:none"  href="'.$redirect3.'" >UPGRADE NOW</a>';
                                    } 
                                ?>
                            </div>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_px-0  mo_boot_table-bordered mo_boot_pt-2">
                            <br><h3>ALL INCLUSIVE</h3><span class="mo_saml_plan_description"><b>(ALL FEATURES ALONG WITH ALL ADD-ONS)</b></span><br><br><br><br>
                            <span id="plus_total_price" style="font-weight: bolder;font-size: xx-large ;">$649*</span><br>
                            <span class="mo_saml_note"><b>[One Time Payment]</b></span>
                            <div class="mo_boot_mt-5">
                                <?php
                                    $redirect3= "https://www.miniorange.com/contact";
                                    echo '<a target="_blank" class="cd-select" style="font-size: 85.5%; cursor: pointer;width:100%;border:none"  href="'.$redirect3.'" >CONTACT US</a>';
                                ?>
                            </div> 
                        </div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Auto creation of users </b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">Upto 10</div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">Unlimited</div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">Unlimited</div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">Unlimited</div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">Unlimited</div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Authentications</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">Unlimited</div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">Unlimited</div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">Unlimited</div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">Unlimited</div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">Unlimited</div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Proxy Server Setup</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Configure SP Using Metadata XML File</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Configure SP Using Metadata URL</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Export Configuration</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Basic Role Mapping</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Basic Attribute Mapping(User Name, Email)</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Import configuration</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Select SAML Request binding type</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Auto-Redirect to IdP</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Default redirect URL after Login</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Integrated Windows Authentication</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Custom admin login URL</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Signed Request for SSO</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Custom Role Mapping</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Custom Attribute Mapping</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Single Logout</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Backend Login for Super Users</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Domain Restriction</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Domain Mapping</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Generate Custom SP Certificate</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Auto-sync IdP Configuration from metadata</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Store Multiple IDP certificates</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>Multiple IdP Support**</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>End to End Identity Provider Configuration ***</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2">
                            <p><b>1 Year plugin Updates</b></p>
                        </div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2"><b>&check;</b></div>
                    </div>
                    <div class="mo_boot_row mo_timepass">
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2 "><b>Add-Ons</b></div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2 ">--</div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2 ">--</div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2 ">--</div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2 ">--</div>
                        <div class="mo_boot_col-sm-2 mo_boot_table-bordered mo_boot_p-2 "><b>(1) IP based restriction for auto redirect<br>(2) Page Restriction<br>(3) SSO Login Audit<br>(4) Role/Group Based Redirection<br></b>
                        </div>
                    </div>
                </div>
            </div>
            
            
            <!-- Modal -->
            <br/><br/>
            <br/>

            <?php echo showAddonsContent();?>

            <!--Don't delete below function call-->
            <div style="margin-left: 60px;background: #e1e5e9;margin-right: 55px;margin-bottom: 55px;">
                <div style="margin-left: 33px;"><br><br>
                    <h3>* This is the price for 1 instance. Check our <a style="color:blue;" href="https://login.xecurify.com/moas/login?username=&redirectUrl=https://login.xecurify.com/moas/initializepayment&requestOrigin=joomla_saml_sso_enterprise_plan" target='_blank'>payment page</a> for full details.</h3>
                    <h4>Steps to Upgrade to License Versions of the Plugin -</h4>
                    <p>1. By clicking on upgrade button you will be redirected to miniOrange Login Console. Enter your username and password with which you created an account with us. After that you will be redirected to payment page.</p>
                    <p>2. Enter your card details and complete the payment. On successful payment completion, you will see the link to download the license version of the plugin.</p>
                    <p>3. Once you download that plugin, first delete existing plugin then install the premium plugin. <br>
                    <p><h3>** Multiple IdPs Supported</h3></p>
                    <p>If you want users from different Identity Providers to SSO into your site then you can configure the plugin with multiple IDPs. Additional charges will be applicable based on the number of Identity Providers you wish to configure.</p>
                    <h3>*** End to End Identity Provider Integration - </h3>
                    <p>We will setup a Conference Call / Gotomeeting and do end to end configuration for your IDP as well as plugin. We provide services to do the configuration on your behalf. (Extra charges applicable at $60/hr) </p>
                    <p>If you have any doubts regarding the licensing plans, you can email us at <a href="mailto:joomlasupport@xecurify.com">joomlasupport@xecurify.com</a>.</p>
                    <h3>10 Days Return Policy -</h3>
                    <p>At miniOrange, we want to ensure you are 100% happy with your purchase. If the premium plugin you purchased is not working as advertised and you've attempted to resolve any issues with our support team, which couldn't get resolved. We will refund the whole amount within 10 days of the purchase. Please email us at <a href="mailto:joomlasupport@xecurify.com"><b>joomlasupport@xecurify.com</b></a> for any queries regarding the return policy.
                    </p><br>
                </div>
            </div>
        </div>
    </div>
    <script>
        function show_bundle()
        {
            if(jQuery("#bundle_checked").is(":checked"))
            {
                jQuery("#bundle_content").css("display","block");
                jQuery("#license_content").css("display","none");
            }
            else
            {
                jQuery("#bundle_content").css("display","none");
                jQuery("#license_content").css("display","block");
            }
        }
    </script>
	<style>
	 .cd-black :hover #singlesite_tab.is-visible{
           margin-right : 4px;
           transition : 0.4s;
           -moz-transition : 0.4s;
           -webkit-transition : 0.4s;
           border-radius: 8px;
           transform: scale(1.03);
           -ms-transform: scale(1.03); /* IE 9 */
           -webkit-transform: scale(1.03); /* Safari */

           box-shadow: 0 0 4px 1px rgba(255,165, 0, 0.8);
       }
	h1 {
            margin: .67em 0;
            font-size: 2em;
        }

        ul {
            list-style: none; /* Remove HTML bullets */
            padding: 0;
            margin: 0;
        }
		
		li {
            list-style: none; /* Remove HTML bullets */
            padding: 0;
            margin: 0;
        }
	</style>
	<style>
	.popover-title{
			background: #ffff99;
		}
		.popover-content{ background: #F2F8FA; }
	</style>
	  
	  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
	<script>
		jQuery(document).ready(function(){
			jQuery('[data-toggle="popover"]').popover();   
		});
    </script>
    <?php
}

function showAddonsContent(){

    define("MO_ADDONS_CONTENT",serialize( array(

        "JOOMLA_ICB" =>      [
             'id' => 'mo_joomla_icb',
            'addonName'  => 'Integrate with Community Builder',
            'addonDescription'  => 'By using the Community Builder Add-on you would be mapping the user details into the CB\'s comprofilers fields table which containing the values from the table comprofiler',
        ],
        "JOOMLA_IP_RESTRICT" =>      [
            'id' => 'mo_joomla_ip_rest',
            'addonName'  => 'IP based restriction for auto redirect',
            'addonDescription'  => 'Restrict specific IP address from auto-redirect to IDP.',
        ],
        "JOOMLA_USER_SYNC_OKTA" =>      [
            'id' => 'mo_joomla_okta_sync',
            'addonName'  => 'Sync users from your IdP in Joomla (SCIM Plugin)',
            'addonDescription'  => 'This add-ons sync users from your IdP to Joomla database.',
        ],
        "JOOMLA_PAGE_RESTRICTION" =>      [
            'id' => 'mo_joomla_page_rest',
            'addonName'  => 'Page Restriction',
            'addonDescription'  => 'This add-on is basically used to protect the pages/posts of your site with IDP login page and also, restrict the access to pages/posts of the site based on the user roles.',
        ],
        "JOOMLA_SSO_AUDIT" =>      [
            'id' => 'mo_joomla_audit',
            'addonName'  => 'SSO Login Audit',
            'addonDescription'  => 'SSO Login Audit captures all the SSO users and will generate the reports.',
        ],
        "JOOMLA_RBA" =>      [
            'id' => 'mo_joomla_rba',
            'addonName'  => 'Role/Group Based Redirection',
            'addonDescription'  => 'This add-on helps you to redirect your users to different pages after they log into your site, based on the role sent by your Identity Provider.',
        ],
    )));

    $displayMessage = "";
    $messages = unserialize(MO_ADDONS_CONTENT);
   

    echo '<div style="background: #ffffff;padding: 55px;" id="addonContent"><h3>SAML 2.0 Plugin Add-ons</h3><hr><div class="mo_otp_wrapper">';
    foreach ($messages as $messageKey)
    {
        $message_keys = isset($messageKey['addonName']) ? $messageKey['addonName'] : '';
        $message_description = isset($messageKey["addonDescription"]) ? $messageKey["addonDescription"] : 'Hi! I am interested in the addon, could you please tell me more about this addon?';
        echo'<div id="'.$messageKey["id"].'">
                 <center><h3 style="color:white;">'.$message_keys.'<br /><br /></h3></center>                               
                 <footer>
                      <center>
                            <a type="button" class="mo_btn btn-primary" style="background-color: #007bff" href="https://www.miniorange.com/contact" target="_blank">Intereseted</a>
                            <!--Dont delete this-->
                           <!--<input type="button" class="mo_btn btn-primary" onclick="support_form_open(\'$message_keys\');"  value="Interested">-->
                      </center>
                 </footer>
                 <span class="cd-pricing-body">
                      <ul class="cd-pricing-features">
                          <li style="color:white;text-align: center;">'.$message_description.'</li>
                      </ul>
                 </span>
            </div>';
    }
    echo '</div></div><br>';
    return $displayMessage;
}

function group_mapping()
{
    $role_mapping = new Mo_saml_Local_Util();
    $role_mapping = $role_mapping->_load_db_values('#__miniorange_saml_role_mapping');
    $role_mapping_key_value = array();
    $attribute = new Mo_saml_Local_Util();
    $attribute = $attribute->_load_db_values('#__miniorange_saml_config');

    if ($attribute) {
        $group_attr = $attribute['grp'];
    } else {
        $group_attr = '';
    }
    if (isset($role_mapping['mapping_value_default'])) $mapping_value_default = $role_mapping['mapping_value_default'];
    else $mapping_value_default = "";
    $enable_role_mapping = 0;
    if (isset($role_mapping['enable_saml_role_mapping'])) $enable_role_mapping = $role_mapping['enable_saml_role_mapping'];
    ?>
    <div class="mo_boot_row  mo_boot_mr-1 mo_boot_p-3" style="background-color: #FFFF;border: 2px solid rgb(15, 127, 182) !important;">
        <div class="mo_boot_col-sm-6  mo_boot_mt-3">
            <h3>Group Mapping <sup><a href="https://docs.miniorange.com/documentation/5-2-role-mapping" target="_blank" style="font-size:12px;" >[Know More]</a></sup></h3>
        </div>
        <div class="mo_boot_col-sm-6  mo_boot_mt-3">    
            <input type="button" id="mo_sp_grp_end_tour" value="Start Tab Tour" onclick="restart_tourgrp();" style=" float: right;" class="mo_boot_btn mo_boot_btn-success"/>
        </div>
        <div class="mo_boot_col-sm-12  mo_boot_mt-1">
        <hr>
            <form action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.saveRolemapping'); ?>" method="post" name="adminForm" id="group_mapping_form">
                <div class="mo_boot_row mo_boot_mt-1">
                    <div class="mo_boot_col-sm-12">
                        <input id="mo_sp_grp_enable" class="mo_saml_custom_checkbox" type="checkbox" name="enable_role_mapping" value="1"  <?php if ($enable_role_mapping == 1) echo "checked"; ?> >&emsp;<b>Check this option if you want to enable Role Mapping</b><br>
                        <p class="mo_saml_custom_checkbox"><b style="color: chocolate">&emsp;&emsp;Note:</b> Enable this checkbox first before using any of the feature below.</p>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-3" id="mo_sp_grp_defaultgrp">
                    <div class="mo_boot_col-sm-4">
                        <p><b>Select default group for both new user and logged in users.</b></p>
                    </div>
                    <div class="mo_boot_col-sm-4">
                        <select class="mo_boot_form-control" name="mapping_value_default" style="width:100%" id="default_group_mapping">
                            <?php $noofroles = 0;

                                $db = JFactory::getDbo();
                                $db->setQuery($db->getQuery(true)
                                    ->select('*')
                                    ->from("#__usergroups"));
                                $groups = $db->loadRowList();
                                foreach ($groups as $group) {
                                    if ($group[4] != 'Super Users') {
                                        if ($mapping_value_default == $group[0]) echo '<option selected="selected" value = "' . $group[0] . '">' . $group[4] . '</option>';
                                        else echo '<option  value = "' . $group[0] . '">' . $group[4] . '</option>';
                                    }
                                }
                            ?>
                        </select><br><br>
                    </div>
                    <div class="mo_boot_col-sm-4">
                        <select style="display:none" id="wp_roles_list">
                            <?php
                                $db = JFactory::getDbo();
                                $db->setQuery('SELECT `title`' . ' FROM `#__usergroups`');
                                $groupNames = $db->loadColumn();
                                $noofroles = count($groupNames);
                                for ($i = 0; $i < $noofroles; $i++) {
                                    echo '<option  value = "' . $groupNames[$i] . '">' . $groupNames[$i] . '</option>';
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-1" style="background-color: #d9d9d9;padding:10px;">
                    <div class="mo_boot_col-sm-12">
                        <input type="checkbox" class="mo_saml_custom_checkbox" name="disable_update_existing_users_role" value="1" disabled>&emsp;Do not update existing user&#39;s roles, if roles are not mapped. <b> <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>[Premium</b></a>, <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>Enterprise]</b></a></b><br>
                        <input type="checkbox" class="mo_saml_custom_checkbox" name="disable_update_existing_users_role" value="1"  disabled>&emsp;Do not update existing user&#39;s roles and add newly mapped roles.<b> <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>[Premium</b></a>, <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>Enterprise]</b></a></b><br>
                        <input type="checkbox" class="mo_saml_custom_checkbox" name="disable_create_users" value="1"  disabled>&emsp;Do not auto create users if roles are not mapped. <b> <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>[Premium</b></a>, <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>Enterprise]</b></a></b><br>
                    </div>
                </div>
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-12 mo_boot_my-4 mo_boot_text-center">
                        <input id="mo_sp_grp_save" type="submit" class="mo_boot_btn  mo_boot_btn-success" value="Save"/>
                    </div>
                    <div class="mo_boot_col-sm-12">
                        <p class='alert alert-info' style="color: #151515;">NOTE: Customized group mapping options shown below are configurable in the <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>Premium</b></a>, <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>Enterprise</b></a> versions of plugin.</p>
                    </div>
                </div>
            </form>
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_table-responsive" style="padding: auto;padding: 25px;background: #bababa;font-size: 25px;font-weight: bold;color: white;">
            <table class="mo_saml_settings_table" id="saml_role_mapping_table">
                <tr>
                    <td><h3>Group</h3></td>
                    <td><input disabled class="mo_saml_table_textbox mo_boot_form-control" type="text" name="grp" value="<?php echo $group_attr; ?>" placeholder="Enter Attribute Name for Group"/></td>
                </tr>
                <tr>
                    <td style="width:20%"><h4><b>Group Name in Joomla</b></h4></td>
                    <td style="width:50%" class="mo_boot_text-center"><h4><b>Group Name from IDP</b></h4></td>
                </tr>
                <?php
                    $user_role = array();
                    $db = JFactory::getDbo();
                    $db->setQuery($db->getQuery(true)
                        ->select('*')
                        ->from("#__usergroups"));
                    $groups = $db->loadRowList();
                    if (empty($role_mapping_key_value)) {
                        foreach ($groups as $group) {
                            if ($group[4] != 'Super Users') {
                                echo '<tr><td><h5>' . $group[4] . '</h5></td><td><input type="text" name="saml_am_group_attr_values_' . $group[0] . '" value= "" placeholder="Semi-colon(;) separated Group/Role value for ' . $group[4] . '"  disabled class="mo_boot_form-control"' . ' /></td></tr>';
                            }
                        }
                ?>
                <?php
                    } else {
                        $j = 1;
                        foreach ($role_mapping_key_value as $mapping_key => $mapping_value) {
                ?>
                <tr>
                    <td>
                        <input class="mo_saml_table_textbox mo_boot_form-control" type="text" name="mapping_key_<?php echo $j; ?>" value="<?php echo $mapping_key; ?>" placeholder="cn=group,dc=domain,dc=com"/>
                    </td>
                    <td>
                        <select name="mapping_value_<?php echo $j; ?>" id="role" class="mo_boot_form-control">
                            <?php
                                $db = JFactory::getDbo();
                                $db->setQuery('SELECT `title`' . ' FROM `#__usergroups`');
                                $groupNames = $db->loadColumn();
                                $noofroles = count($groupNames);
                                for ($i = 0; $i < $noofroles; $i++) {
                                if ($mapping_value == $groupNames[$i]) echo '<option selected="selected" value = "' . $groupNames[$i] . '">' . $groupNames[$i] . '</option>';
                                else echo '<option value = "' . $groupNames[$i] . '">' . $groupNames[$i] . '</option>';
                                }
                            ?>
                       </select>
                    </td>
                </tr>
                <?php $j++;
                        }
                    }
                ?>
            </table>
        </div>   
    </div>
    <?php

}

//Don't delete the below function

/*function mo_sliding_support()
{
    $result = (new Mo_saml_Local_Util)->_load_db_values('#__miniorange_saml_customer_details');
    $admin_email = isset($result['email']) ? $result['email'] : '';
    $admin_phone = isset($result['admin_phone']) ? $result['admin_phone'] : '';
    */?><!--
    <div id="mosaml_support_button_sliding" class="mo_saml_sliding_support_btn">
        <input type="button" class="mo_boot_btn mo_boot_btn-primary" id="mo_support_btn" value="Feature Request" onclick="support_form_open();"/>
        <div id="Support_Section" class="mo_saml_table_layout_support_3">
            <form name="f" method="post" action="<?php /*echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.contactUs'); */?>">
                <h3>Feature/Add-On Request</h3>
                <div>Need any help?<br /><br /></div>
                <div>
                    <table class="mo_saml_settings_table">
                        <tr>
                            <td>
                                <input style="width: 100%; border: 1px solid #868383 !important;" type="email" class="mo_saml_table_textbox" name="query_email" value="<?php /*echo $admin_email; */?>" placeholder="Enter your email" required /><br><br>
                            </td>
                        </tr>
                        <tr><td>
                                <input style="width: 100%; border: 1px solid #868383 !important;" type="tel" class="mo_saml_table_textbox" name="query_phone" value="<?php /*echo $admin_phone; */?>" placeholder="Enter your phone"/><br><br>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <textarea id="mo_saml_query" name="mo_saml_query" class="mo_saml_settings_textarea" style="border-radius:4px;resize: vertical;width:100%; border: 1px solid #868383 !important;" cols="52" rows="7" onkeyup="mo_saml_valid(this)" onblur="mo_saml_valid(this)" onkeypress="mo_saml_valid(this)" required placeholder="Write your query here"></textarea>
                            </td>
                        </tr>
                    </table>
                </div>
                <input type="hidden" name="option1" value="mo_saml_login_send_query"/><br>
                <input type="submit" name="send_query" style ="margin-left: 23%" value="Submit Query" class="btn btn-medium btn-success" />
                <input type="button" onclick="window.open('https://faq.miniorange.com/kb/joomla-saml/')" target="_blank" value="FAQ's"  style= "margin-right: 25px; margin-left: 25px;" class="btn btn-medium btn-success" />
                <p><br>If you want custom features in the plugin, just drop an email to <a href="mailto:joomlasupport@xecurify.com"up> joomlasupport@xecurify.com</a> </p>
            </form>
        </div>
    </div>
    <div hidden id="mosaml-feedback-overlay"></div>
    <script>
        function support_form_open(event) {
            var qmessage = "Hi! I am interested in the \""+event+"\" addon, could you please tell me more about this addon?";

            var n = jQuery("#mosaml_support_button_sliding").css("right");

            if (n != "929") {
                jQuery("#mosaml-feedback-overlay-1").show();
                jQuery("#mosaml_support_button_sliding").animate({
                    "right": "929"
                });
            } else {
                jQuery("#mosaml-feedback-overlay-1").hide();
                jQuery("#mosaml_support_button_sliding").animate({
                    right: "-259"
                });
            }
        }
    </script>
    --><?php
/*}*/

function mo_sso_login()
{

    $siteUrl = JURI::root();
    $sp_base_url = $siteUrl;
    ?>
    <div class="mo_boot_row  mo_boot_mx-1 mo_boot_p-3" style="background-color: #FFFF;border: 2px solid rgb(15, 127, 182) !important;">
        <div class="mo_boot_col-sm-12 mo_boot_mt-3">
            <div class="mo_boot_row">
                <div class="mo_boot_col-lg-9 mo_boot_mt-1">
                    <h3>Login Settings: <sup><a href='https://docs.miniorange.com/documentation/4-redirection-sso-links-settings' target='_blank' style="font-size:12px">[Know More]</a></sup></h3>
                </div>
            </div>
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <hr>
                </div>
            </div>
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_mt-3">
            <div class="mo_boot_row">
                <div class="mo_boot_col-lg-9 mo_boot_mt-1">
                    <h4>SSO URL: Add a link or button on your site login page.</h4>
                </div>
            </div>
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_mt-2">
            <p class='alert alert-info' style="color: #151515;"><b>Note:</b> This link is used for Single Sign-On by end users. Add a button on your site login page with the following URL</p>
            <div class="mo_saml_highlight_background_url_note">
                <span id="sso_url" >
                    <a  href='<?php echo $sp_base_url . '?morequest=sso'; ?>' ><?php echo '<b>' . $sp_base_url . '?morequest=sso</b>'; ?></a>
                </span>
            </div>
            <i class="fa fa-lg fa-copy mo_copy" onclick="copyToClipboard('#sso_url');" style="color:red;margin-left: 1%;"> </i>

        </div>
        <div class="mo_boot_col-sm-12 mo_boot_mt-4" style="text-align: center;padding: 25px;background: #bababa;font-weight: bold;color: white;">
            <h2>License Version Features</h2>
        </div>
        <div class="mo_boot_col-sm-12 mo_saml_sso_link_style">
            <p><h4><input type="checkbox" style="margin-right:11px;margin-top:-1px;border: 1px solid #868383 !important;" disabled>
                Check this option if you want to disable auto creation of users if user does not exist.</h4>
                [Available in the <b><a href='#' class='premium' onclick="moSAMLUpgrade();">Standard</a>, <a href='#' class='premium' onclick="moSAMLUpgrade();">Premium</a>, <a href='#' class='premium' onclick="moSAMLUpgrade();">Enterprise</a></b> version]
            </p>
            <p class='alert alert-info' style="color: #151515;">NOTE: If you enable this feature new user's wont be created, only existing users can login using SSO.</p><br>

            <p><h4><input type="checkbox" style="margin-right:11px;margin-top:-1px;border: 1px solid #868383 !important;" disabled>Auto Redirect the user to your Identity Provider (IdP).</h4>
                [Available in the <b><a href='#' class='premium' onclick="moSAMLUpgrade();">Standard</a>, <a href='#' class='premium' onclick="moSAMLUpgrade();">Premium</a>, <a href='#' class='premium' onclick="moSAMLUpgrade();">Enterprise</a></b> version]
            </p>
            <p class='alert alert-info' style="color: #151515;">NOTE: Enable this if you want to restrict your site to only
                logged in users. Enabling this plugin will redirect the users to your IdP if logged in session is not found...<a href="https://docs.miniorange.com/documentation/4-redirection-sso-links-settings" target="_blank" title="Know More"><b>[Know More]</b></a>
            </p><br>

            <p><h4><input type="checkbox" style="margin-right:11px;margin-top:-1px;border: 1px solid #868383 !important;" disabled>Check this option if you want to <b>enable backdoor login</b>.</h4>
                [Available in the <b><a href='#' class='premium' onclick="moSAMLUpgrade();">Standard</a>, <a href='#' class='premium' onclick="moSAMLUpgrade();">Premium</a>, <a href='#' class='premium' onclick="moSAMLUpgrade();">Enterprise</a></b> version]
            </p>
            <p class='alert alert-info' style="color: #151515;">NOTE: Checking this option creates a backdoor to login to your website using Joomla credentials incase you get locked out of your IdP.</p><br>

            <p><h4><input type="checkbox" style="margin-right:11px;margin-top:-1px;border: 1px solid #868383 !important;" disabled>Enable Backend Login for Super Users during Single Sign On.</h4>
                [Available in the <b><a href='#' class='premium' onclick="moSAMLUpgrade();">Premium</a>, <a href='#' class='premium' onclick="moSAMLUpgrade();">Enterprise </a></b>version]
            </p>
            <p class='alert alert-info' style="color: #151515;">
                NOTE:Enable this feature if you want admin/super user to be logged into admin console after SSO instead of front end of site... <a href="https://docs.miniorange.com/documentation/4-redirection-sso-links-settings" target="_blank" title="Know More"><b>[Know More]</b></a>
            </p><br>

            <p><h4><input type="checkbox" style="margin-right:11px;margin-top:-1px;border: 1px solid #868383 !important;" disabled>Domain Restriction</h4>
                [Available in the <b> <a href='#' class='premium' onclick="moSAMLUpgrade();">Enterprise </a></b>version]
            </p>
            <p class='alert alert-info' style="color: #151515;">
                NOTE:Domain Restriction provides the functionality to allow or restrict the users of a particular domain to login or register. It includes two features allow users to login with specified domains and deny users to login with specified domains...<a href="https://docs.miniorange.com/documentation/domain-restriction" target="_blank" title="Know More"><b>[Know More]</b></a>
            </p><br>

            <p><h4><input type="checkbox" style="margin-right:11px;margin-top:-1px;border: 1px solid #868383 !important;" disabled>Domain Mapping</h4>
                [Available in the <b> <a href='#' class='premium' onclick="moSAMLUpgrade();">Enterprise </a></b>version]
            </p>
            <p class='alert alert-info' style="color: #151515;">
                NOTE:Map the domain in order to auto-redirect to a particular IDP when the user tries to login with domain email.
            </p><br>

            <p><h4><input type="checkbox" style="margin-right:11px;margin-top:-1px;border: 1px solid #868383 !important;" disabled>Error Handling</h4>
                [Available in the <b><a href='#' class='premium' onclick="moSAMLUpgrade();">Standard</a>, <a href='#' class='premium' onclick="moSAMLUpgrade();">Premium</a>, <a href='#' class='premium' onclick="moSAMLUpgrade();">Enterprise</a></b> version]
            </p>
            <p class='alert alert-info' style="color: #151515;">
                NOTE:Error Handling provides the functionality to allow custom error messages for duplicate users.
            </p>
            <hr>
        </div>
    </div>
    <?php
}

function attribute_mapping()
{
    ?>
    <div class="mo_boot_row  mo_boot_mx-1 mo_boot_p-3" style="background-color: #FFFF;border: 2px solid rgb(15, 127, 182) !important;">
        <div class="mo_boot_col-sm-12 mo_boot_mt-3">
            <div class="mo_boot_row">
                <div class="mo_boot_col-lg-9 mo_boot_mt-1">
                    <h3>Attribute Mapping <sup><a href='https://docs.miniorange.com/documentation/5-1-attribute-mapping' target='_blank' style="font-size:12px">[Know More]</a></sup></h3>
                </div>
                <div class="mo_boot_col-lg-3  mo_boot_mt-1">
                    <input type="button" id="mo_sp_attr_end_tour" value="Start Tab Tour" onclick="restart_touratt();" class="mo_boot_btn mo_boot_btn-success mo_boot_float-right" />
                </div>
            </div>
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <hr>
                </div>
            </div> 
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_mt-2">
            <form action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.saveConfig'); ?>" method="post" name="adminForm" id="attribute_mapping_form">
                <div class="mo_boot_row mo_boot_mt-1">
                    <div class="mo_boot_col-sm-12">
                        <table id="mo_saml_settings_table" class="mo_saml_settings_table"></table>
                    </div>    
                    <div class="mo_boot_col-sm-12">
                        <input type="checkbox" value="1" disabled class="mo_saml_custom_checkbox">&emsp;<b>Do not update existing user&#39;s attributes.</b> <b> <a href='#' class='premium' onclick="moSAMLUpgrade();">[Premium</a>, <a href='#' class='premium' onclick="moSAMLUpgrade();">Enterprise]</a></b>
                        <p class='alert alert-info mo_boot_mt-3' style="color: #151515;">NOTE: Use attribute name NameID if Identity is in the NameIdentifier element of the subject statement in SAML Response.</p>
                    </div>   
                </div>
                <div class="mo_boot_row mo_boot_mt-2"  id="mo_saml_uname" >
                    <div class="mo_boot_col-sm-3">
                        <b>Username</b>
                    </div>
                    <div class="mo_boot_col-sm-8">
                        <input disabled class="mo_saml_table_textbox mo_boot_form-control" type="text" name="username"required placeholder="NameID" value="NameID" />
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-1"  id="mo_saml_email">
                    <div class="mo_boot_col-sm-3">
                        <b>Email</b>
                    </div>
                    <div class="mo_boot_col-sm-8">
                        <input disabled class="mo_saml_table_textbox mo_boot_form-control" type="text" name="email"required placeholder="NameID" value="NameID" />
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-1" id="mo_sp_attr_name">
                    <div class="mo_boot_col-sm-3">
                        <b>Name</b>
                    </div>
                    <div class="mo_boot_col-sm-8">
                        <input  disabled class="mo_saml_table_textbox mo_boot_form-control" type="text" name="name"  placeholder="Enter Attribute Name for Name" />
                    </div>
                </div>
                <div class="mo_boot_row  mo_boot_mt-4 mo_boot_text-center" id="mo_sp_attr_save_attr">
                    <div class="mo_boot_col-sm-12">
                        <input disabled type="submit" class="mo_boot_btn mo_boot_btn-success" value="Save Attribute Mapping"/>
                    </div>
                </div>
            </form>
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_mt-5">
            <h3> 
                Map Joomla's User Profile Attributes
                <sup>
                <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>[Premium</b></a>, <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>Enterprise]</b></a>
                </sup>
                <input type="button" class="mo_boot_btn mo_boot_btn-primary" disabled value="+" />
                <input type="button" class="mo_boot_btn mo_boot_btn-danger" disabled value="-" />
            </h3>
            <hr>
            <p class="alert alert-info" style="color: #151515;">NOTE: During registration or login of the user, the value corresponding to 'Value from IDP' will be updated for the User Profile Attribute field in the User Profile table. Customized attribute mapping options shown above are configurable in the <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>Premium </a> </b>and <a href='#' class='premium' onclick="moSAMLUpgrade();"> <b>Enterprise</b></a> versions of plugin.</p>
        </div>
        <div class="mo_boot_col-sm-6 mo_boot_mt-1"">
            <div class="mo_boot_row mo_boot_mt-1">
                <div class="mo_boot_col-sm-12">
                    <b>Joomla's User Profile Attribute</b>
                </div>
                <div class="mo_boot_col-sm-12">
                    <input type="text" class="mo_boot_form-control" disabled="disabled"/>
                </div>
            </div>
        </div>
        <div class="mo_boot_col-sm-6 mo_boot_mt-1"">
            <div class="mo_boot_row mo_boot_mt-1"">
                <div class="mo_boot_col-sm-12">
                    <b>IDP Attribute</b>
                </div>
                <div class="mo_boot_col-sm-12">
                    <input type="text" class="mo_boot_form-control" disabled="disabled"/>
                </div>
            </div>
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_mt-5">
            <h3> 
                Map Joomla's Field Attributes
                <sup>
                <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>[Premium</b></a>, <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>Enterprise]</b></a>
                </sup>
                <input type="button" class="mo_boot_btn mo_boot_btn-primary" disabled value="+" />
                <input type="button" class="mo_boot_btn mo_boot_btn-danger" disabled value="-" />
            </h3>
            <hr>
            <div class="alert alert-info" >
                <p style="color: #151515;"><b style="color: brown;">NOTE:</b> During registration or login of the user, the value corresponding to User Profile Attributes Mapping Value from IDP will be updated for the User Field Attributes field in User field table.</p>
                <p><b style="color: black;">Joomla's User Field Attribute:</b> It is the Joomla's user attribute field whose value you want to set in site.</p>
                <p><b style="color: black;">IdP Attribute Name:</b> It is the name which you want to get from your IDP.</p>
            </div>
        </div>
        <div class="mo_boot_col-sm-6 mo_boot_mt-1"">
            <div class="mo_boot_row mo_boot_mt-1"">
                <div class="mo_boot_col-sm-12">
                    <b>Joomla's User Field Attribute</b>
                </div>
                <div class="mo_boot_col-sm-12">
                    <input type="text" class="mo_boot_form-control" disabled="disabled"/>
                </div>
            </div>
        </div>
        <div class="mo_boot_col-sm-6 mo_boot_mt-1"">
            <div class="mo_boot_row mo_boot_mt-1"">
                <div class="mo_boot_col-sm-12">
                    <b>IDP Attribute</b>
                </div>
                <div class="mo_boot_col-sm-12">
                    <input type="text" class="mo_boot_form-control" disabled="disabled"/>
                </div>
            </div>
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_text-center  mo_boot_mt-4">
            <input type="submit" class="mo_boot_btn mo_boot_btn-success" value="Save Attribute Mapping" disabled/>
        </div>
    </div>
    <style>
        .att li{
            list-style-type: disc ;
        }
    </style>
    <?php
}

function proxy_setup()
{
    $proxy = (new Mo_saml_Local_Util)->_load_db_values('#__miniorange_saml_proxy_setup');
    $proxy_host_name = isset($proxy['proxy_host_name']) ? $proxy['proxy_host_name'] : '';
    $port_number = isset($proxy['port_number']) ? $proxy['port_number'] : '';
    $username = isset($proxy['username']) ? $proxy['username'] : '';
    $password = isset($proxy['password']) ? base64_decode($proxy['password']) : '';
    ?>
    <div class="mo_boot_row mo_boot_p-3"> 
        <div class="mo_boot_col-sm-12"  id="mo_sp_proxy_config">
            <div class="mo_boot_row mo_boot_mt-2">
                <div class="mo_boot_col-sm-9">
                    <input type="hidden" name="option1" value="mo_saml_save_proxy_setting" />
                    <h3>Configure Proxy Server</h3>
                </div>
                <div class="mo_boot_col-sm-3">
                    <input type="button" class="mo_boot_float-right btn mo_boot_btn mo_boot_btn-danger" value="Cancel" onclick = "hide_proxy_form();"/>
                </div>
            </div>
            <hr>
            <form  action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.proxyConfig'); ?>" name="proxy_form" method="post">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-12">  
                        <p><b>If your organization dont allow you to connect to internet directly and if you need to login to your proxy server please configure following details.</b></p>
                        <p>Enter the information to setup the proxy server.</p>
                    </div>
                </div>
                <div class="mo_boot_row" id="mo_sp_proxy_host_name">
                    <div class="mo_boot_col-sm-3">
                        <strong>Proxy Host Name:<span style="color: #FF0000">*</span></strong>
                    </div>
                    <div class="mo_boot_col-sm-8">
                        <input type="text" name="mo_proxy_host" placeholder="Enter the host name" class="mo_saml_proxy_setup mo_boot_form-control" value="<?php echo $proxy_host_name ?>" required/>
                    </div>
                </div>
                <div class="mo_boot_row" id="mo_sp_port_number">
                    <div class="mo_boot_col-sm-3"><br>
                        <strong>Port Number:<span style="color: #FF0000">*</span></strong>
                    </div>
                    <div class="mo_boot_col-sm-8"><br>
                        <input type="number" name="mo_proxy_port" placeholder="Enter the port number of the proxy" class="mo_boot_form-control mo_saml_proxy_setup" value="<?php echo $port_number ?>" required/>
                    </div>
                </div>
                <div class="mo_boot_row" id="mo_sp_proxy_username">
                    <div class="mo_boot_col-sm-3"><br>
                        <strong>Username:</strong>
                    </div>
                    <div class="mo_boot_col-sm-8"><br>
                        <input type="text" name="mo_proxy_username" placeholder="Enter the username of proxy server" class="mo_boot_form-control mo_saml_proxy_setup" value="<?php echo $username ?>" />
                    </div>
                </div>
                <div class="mo_boot_row" id="mo_sp_proxy_password">
                    <div class="mo_boot_col-sm-3"><br>
                        <strong>Password:</strong>
                    </div>
                    <div class="mo_boot_col-sm-8"><br>
                        <input type="password" name="mo_proxy_password" placeholder="Enter the password of proxy server" class="mo_boot_form-control mo_saml_proxy_setup" value="<?php echo $password ?>">
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_text-center mo_boot_mt-3">
                    <div class="mo_boot_col-sm-12">
                        <input type="submit" style="width:100px;" value="Save" class="mo_boot_btn mo_boot_btn-success" />
                    </div>
                </div>
            </form>
            <div class="mo_boot_col-sm-12  mo_boot_text-center  mo_boot_mt-3">
                <form style="background-color:#FFFFFF; " action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.proxyConfigReset'); ?>" name="proxy_form1" method="post">
                    <input type="button" value="Reset" onclick='submit();' class="mo_boot_btn mo_boot_btn-success" />
                </form>
            </div>
        </div>
    </div>
    <?php
}

/* show custom certificare */
function customcertificate(){
    ?>
    <form action="" name="customCertificateForm" id="custom_certificate_form">
         
        <div class="mo_boot_row  mo_boot_mx-1 mo_boot_p-3" id="generate_certificate_form"   style="background-color: #FFFF;border: 2px solid rgb(15, 127, 182) !important;display:none">
                <div class="mo_boot_col-sm-12">
                    <div class="mo_boot_row  mo_boot_mt-2">
                        <div class="mo_boot_col-sm-10">
                            <h3>
                                Generate Custom Certificate
                            </h3>
                        </div>
                        <div class="mo_boot_col-sm-2">
                            <input type="button" class="mo_boot_btn mo_boot_btn-success" value="Back" onclick = "hide_gen_cert_form()"/>
                        </div>
                    </div>
                    <hr> 
                    <div class="mo_boot_row mo_boot_mt-2">
                        <div class="mo_boot_col-sm-3">
                            <b>Country code :<span style="color:red;">*</span></b>
                        </div>
                        <div class="mo_boot_col-sm-7">
                            <input class="mo_saml_table_textbox  mo_boot_form-control" type="text"  placeholder="Enter your country code" disabled>
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-2">
                        <div class="mo_boot_col-sm-3">
                            <b>State<span style="color:red;">*</span></b>
                        </div>
                        <div class="mo_boot_col-sm-7">
                            <input class="mo_saml_table_textbox mo_boot_form-control" type="text"  placeholder="Enter State Name" disabled />
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-2">
                        <div class="mo_boot_col-sm-3">
                            <b>Company<span style="color:red;">*</span></b>
                        </div>
                        <div class="mo_boot_col-sm-7">
                            <input  class="mo_saml_table_textbox mo_boot_form-control" type="text"  placeholder="Enter your Company Name" disabled />
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-2">
                        <div class="mo_boot_col-sm-3">
                            <b>Unit<span style="color:red;">*</span></b>
                        </div>
                        <div class="mo_boot_col-sm-7">
                            <input  class="mo_saml_table_textbox mo_boot_form-control" type="text" placeholder="Unit Name(eg. section) : Information Technology" disabled />
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-2">
                        <div class="mo_boot_col-sm-3">
                            <b>Common<span style="color:red;">*</span></b>
                        </div>
                        <div class="mo_boot_col-sm-7">
                            <input  class="mo_saml_table_textbox mo_boot_form-control type="text" placeholder="Common Name(eg. your name or your servers hostname)" disabled />
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-2">
                        <div class="mo_boot_col-sm-3">
                            <b>Digest Algorithm<span style="color:red;">*</span></b>
                        </div>
                        <div class="mo_boot_col-sm-7">
                            <select class="mo_saml_table_textbox mo_boot_form-control">                              
                                <option>SHA512</option>
                                <option>SHA384</option>
                                <option>SHA256</option>
                                <option>SHA1</option>                            
                            </select>
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-2">
                        <div class="mo_boot_col-sm-3">
                            <b>Bits to generate the private key:<span style="color:red;">*</span></b>
                        </div>
                        <div class="mo_boot_col-sm-7">
                            <select class="mo_saml_table_textbox mo_boot_form-control">                              
                                <option>2048 bits</option>
                                <option>1024 bits</option>                                                               
                            </select>
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-2">
                        <div class="mo_boot_col-sm-3">
                            <b>Valid days:<span style="color:red;">*</span></b>
                        </div>
                        <div class="mo_boot_col-sm-7">
                            <select class="mo_saml_table_textbox mo_boot_form-control">                              
                                <option>365 days</option>                                                                                               
                                <option>180 days</option>                                                                                               
                                <option>90 days</option>                                                                                               
                                <option>45 days</option>                                                                                               
                                <option>30 days</option>                                                                                               
                                <option>15 days</option>                                                                                               
                                <option>7 days</option>                                                                                               
                            </select>
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_text-center mo_boot_mt-2">
                        <div class="mo_boot_col-sm-12">
                        <input type="submit" value="Generate Self-Signed Certs" disabled class="mo_boot_btn mo_boot_btn-success"; />
                        </div>
                    </div>
                </div>
        </div>
        <div class="mo_boot_row  mo_boot_mx-1 mo_boot_p-3" id="mo_gen_cert"  style="background-color: #FFFF;border: 2px solid rgb(15, 127, 182) !important;">
                <div class="mo_boot_col-sm-12">
                    <input id="miniorange_saml_custom_certificate" type="hidden" name="cust_certificate_option" value="miniorange_saml_save_custom_certificate"/>
                    <h3>Generate Custom Certificate <sup><a href='https://docs.miniorange.com/documentation/custom-certificate' target='_blank' title="Know More" style="font-size:12px;" >[Know More]</a></sup></h3>
                    <hr>
                </div>
                <div class="mo_boot_col-sm-12 mo_boot_mt-3" id="customCertificateData">
                    <div class="mo_boot_row custom_certificate_table"  >
                        <div class="mo_boot_col-sm-3">
                            <b>
                                X.509 Public Certificate
                                <span style="color: #FF0000; font-size: large;">*</span>
                                <a href='#' class='premium' onclick="moSAMLUpgrade();">[Enterprise]</a>
                            </b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <textarea disabled="disabled" rows="5" cols="100" class="mo_saml_table_textbox mo_boot_w-100"></textarea>
                        </div>
                    </div>
                    <div class="mo_boot_row custom_certificate_table"  >
                        <div class="mo_boot_col-sm-3">
                            <b>
                                X.509 Private Certificate
                                <span style="color: #FF0000; font-size: large;">*</span>
                                <a href='#' class='premium' onclick="moSAMLUpgrade();">[Enterprise]</a>
                            </b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <textarea disabled="disabled" rows="5" cols="100" class="mo_saml_table_textbox mo_boot_w-100"></textarea>
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-3 custom_certificate_table"  id="save_config_element">
                        <div class="mo_boot_col-sm-12 mo_boot_text-center">
                            <input disabled="disabled" type="submit" name="submit" value="Upload" class="mo_boot_btn mo_boot_btn-success"/> &nbsp;&nbsp;
                            <input type="button" name="submit" value="Generate" class="mo_boot_btn  mo_boot_btn-success" onclick="show_gen_cert_form()"/>&nbsp;&nbsp;
                            <input disabled type="submit" name="submit" value="Remove" class="mo_boot_btn  mo_boot_btn-primary"/>
                        </div>
                    </div>
                </div>
        </div>
    </form>
    <?php
}

function requestfordemo()
{
    $current_user = JFactory::getUser();
	$result = new Mo_saml_Local_Util();
	$result = $result->_load_db_values('#__miniorange_saml_customer_details');
    $admin_email = isset($result['email']) ? $result['email'] : '';
    if ($admin_email == '') $admin_email = $current_user->email;
    ?>
    <div class="mo_boot_row mo_boot_p-3" style="border: 2px solid rgb(15, 127, 182);background-color:white">
        <div class="mo_boot_col-sm-12 mo_boot_text-center">
            <h3>Request for Trial / Demo</h3>
            <hr>
        </div>
        <div class="mo_boot_col-sm-12" style="background-color: #e2e6ea;border-radius:5px">
            <p>
                If you want to try the license version of the plugin then we can setup a demo Joomla site for you on our
                cloud and provide you with its credentials. You can configure it with your Identity Provider, test the SSO 
                and play around with the premium features.
            </p>
            <b>Note:</b> Please describe your use-case in the <b>Description</b> below.
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_mt-3">
            <form  name="demo_request" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.requestForDemoPlan');?>">
                <div class="mo_boot_row mo_saml_settings_table">
                    <div class="mo_boot_col-sm-12">
                        <p>
                            <strong>Email:<span style="color: red;">*</span> </strong>
                        </p>
                        <input style="border: 1px solid #868383 !important;" type="email" class="mo_saml_table_textbox mo_boot_form-control" name="email" value="<?php echo $admin_email; ?>" placeholder="person@example.com" required />
                    </div>
                    <div class="mo_boot_col-sm-12">
                        <p>
                            <strong>Request a demo for: </strong>
                        </p>
                        <select required class="mo_boot_form-control" style="border: 1px solid #868383 !important;" name="plan">
                            <option disabled selected style="text-align: center">----------------------- Select -----------------------</option>
                            <option value="Joomla SAML Standard Plugin">Joomla SAML SP Standard Plugin</option>
                            <option value="Joomla SAML Premium Plugin">Joomla SAML SP Premium Plugin</option>
                            <option value="Joomla SAML Enterprise Plugin">Joomla SAML SP Enterprise Plugin</option>
                            <option value="Not Sure">Not Sure</option>
                        </select>
                    </div>
                    <div class="mo_boot_col-sm-12">
                        <p>
                            <strong>Description:<span style="color: red;">*</span> </strong>
                        </p>
                        <textarea  name="description" class="mo_saml_settings_textarea" style="border-radius:4px;resize: vertical;width:100%; border: 1px solid #868383 !important;" cols="52" rows="7" onkeyup="mo_saml_valid(this)"
                            onblur="mo_saml_valid(this)" onkeypress="mo_saml_valid(this)" required placeholder="Need assistance? Write us about your requirement and we will suggest the relevant plan for you."></textarea>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_text-center">
                    <div class="mo_boot_col-sm-12">
                        <input type="hidden" name="option1" value="mo_saml_login_send_query"/><br>
                        <input  type="submit" name="submit" value="Submit" class="mo_boot_btn mo_boot_btn-primary"/>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php
}
/* End of Create Customer function */

function select_identity_provider()
{
    $attribute = new Mo_saml_Local_Util();
	$attribute = $attribute->_load_db_values('#__miniorange_saml_config');
    $idp_entity_id = "";
    $single_signon_service_url = "";
    $name_id_format = "";
    $certificate = "";
    $dynamicLink="Login with IDP";
    $siteUrl = JURI::root();
    $sp_base_url = $siteUrl;
    if (isset($attribute['idp_entity_id']))
    {
        $idp_entity_id = $attribute['idp_entity_id'];
        $single_signon_service_url = $attribute['single_signon_service_url'];
        $name_id_format = $attribute['name_id_format'];
        $certificate = $attribute['certificate'];
    }
    $isAuthEnabled = JPluginHelper::isEnabled('authentication', 'miniorangesaml');
    $isSystemEnabled = JPluginHelper::isEnabled('system', 'samlredirect');
    if (!$isSystemEnabled || !$isAuthEnabled)
    {
        ?>
        <div id="system-message-container">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <div class="alert alert-error">
                <h4 class="alert-heading">Warning!</h4>
                <div class="alert-message">
                    <h4>This component requires Authentication and System Plugin to be activated. Please activate the following 2 plugins to proceed further.</h4>
                    <ul>
                        <li>Authentication - miniOrange</li>
                        <li>System - Miniorange Saml Single Sign-On</li>
                    </ul>
                    <h4>Steps to activate the plugins.</h4>
                    <ul><li>In the top menu, click on Extensions and select Plugins.</li>
                        <li>Search for miniOrange in the search box and press 'Search' to display the plugins.</li>
                        <li>Now enable both Authentication and System plugin.</li></ul>
                </div>
            </div>
        </div>
        <?php
    } ?>
    <style>
        table.ex1 {
            border-collapse: separate;
            border-spacing: 15px;
        }
    </style>
    <div class="mo_boot_row mo_boot_mr-1 mo_boot_mt-3 mo_boot_py-3 mo_boot_px-2" id="upload_metadata_form" style="background-color:#FFFFFF;border:2px solid rgb(15, 127, 182); display:none ;">
        <div class="mo_boot_col-sm-12 mo_boot_mt-3">
            <h3>
                Upload IDP Metadata
                <span style="float:right;margin-right:25px;">
                    <input type="button" class="mo_boot_btn mo_boot_btn-danger" value="Cancel" onclick = "hide_metadata_form()"/>
                </span><hr>
            </h3>
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_mt-1">
            <form action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.handle_upload_metadata'); ?>" name="metadataForm" method="post" id="IDP_meatadata_form" enctype="multipart/form-data">
                <div class="mo_boot_row mo_boot_mt-2">
                    <div class="mo_boot_col-sm-3">
                        <input id="mo_saml_upload_metadata_form_action" type="hidden" name="option1" value="upload_metadata" />
                        <b>Upload Metadata  :</b>
                    </div>
                    <div class="mo_boot_col-sm-4">
                        <input type="hidden" name="action" value="upload_metadata" />
                        <input type="file" class="mo_boot_form-control-file" name="metadata_file" />
                    </div>
                    <div class="mo_boot_col-sm-4 mo_boot_pl-sm-4">
                        <input type="submit" class="mo_boot_btn mo_boot_btn-primary" name="option1" method="post" value="Upload"/>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-2">
                    <div class="mo_boot_col-sm-12">
                        <p style="font-size:13pt;text-align:center;"><b>OR</b></p>
                    </div>
                    <div class="mo_boot_col-lg-3">
                        <input type="hidden" name="action" value="fetch_metadata" />
                        <b>Enter metadata URL:</b>
                    </div>
                    <div class="mo_boot_col-lg-6">
                        <input type="url" name="metadata_url" placeholder="Enter metadata URL of your IdP." class="mo_boot_form-control"/>
                    </div>
                    <div class="mo_boot_col-lg-3 mo_boot_text-center">
                        <input type="submit" class=" mo_boot_float-lg-right mo_boot_btn mo_boot_btn-primary" name="option1" method="post" value="Fetch Metadata"/>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-sm-8 mo_boot_offset-lg-3">
                        <input type="checkbox" disabled>
                        <b>Update IdP settings by pinging metadata URL? <a href='#' class='premium' onclick="moSAMLUpgrade();">Enterprise feature</a></b>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-3" id="select_time_sync_metadata">
                    <div class="mo_boot_col-sm-4 mo_boot_offset-lg-3">
                        <span>Select how often you want to ping the IdP : </span>
                    </div>
                    <div class="mo_boot_col-sm-2">
                        <select name = "sync_interval" class="mo_boot_form-control">
                            <option value = "hourly">hourly</option>
                            <option value = "daily">daily</option>
                            <option value = "weekly">weekly</option>
                            <option value = "monthly">monthly</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="mo_boot_row  mo_boot_mr-1 mo_boot_mt-3  mo_boot_py-3 mo_boot_px-2" id="import_export_form" style="background-color:#FFFFFF; border:2px solid rgb(15, 127, 182);display:none ;">
        <div class="mo_boot_col-sm-12">
            <h3>
                Import /Export Configuration <sup><a href="https://docs.miniorange.com/documentation/import-export-configuration" target='_blank' style="font-size:12px;" >[Know More]</a></sup>
                <span style="float:right;margin-right:25px;">
                    <input type="button" class="mo_boot_btn mo_boot_btn-danger" value="Cancel" onclick="hide_import_export_form()"/>
                </span><hr>
            </h3>
            
        </div>
        <!-- <div class="mo_boot_col-sm-12">
            <p>This tab will help you to transfer your plugin configurations when you change your Joomla instance</p>
            <p>Example: When you switch from test environment to production. Follow these 3 simple steps to do that:</p>
            <ol class ="att">
                <li>Download plugin configuration file by clicking on the button given below.</li>
                <li>Install the plugin on new Joomla instance.</li>
                <li>Upload the configuration file in Import Plugin Configurations section.</li>
            </ol>
        </div> -->
        <div class="mo_boot_col-sm-12">
            <h3>Download configuration file</h3>
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_pl-sm-4">
            <form name="f" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.importexport'); ?>">
                <input id="mo_sp_exp_exportconfig" type="button" class="mo_boot_btn mo_boot_btn-primary" onclick="submit();" value= "Export Configuration" />
            </form>
        </div>


        <div class="mo_boot_col-sm-12"><br>
            <h3>Import Configurations</h3><hr>
            <p> This feature is available in the <a href='#' class='premium' onclick="moSAMLUpgrade();"><b>Standard</a>,<a href='#' class='premium' onclick="moSAMLUpgrade();">Premium</a>,<a href='#' class='premium' onclick="moSAMLUpgrade();">Enterprise</b></a> version of plugin.</p>
        </div>

        <div class="mo_boot_col-sm-4"><br>
            <input type="file" class="form-control-file mo_boot_d-inline" name="configuration_file" disabled="disabled">
        </div>
        <div class="mo_boot_col-sm-4 mo_boot_pl-sm-4"><br>
            <input id="mo_sp_exp_importconfig" type="submit" disabled="disabled" name="submit" class="mo_boot_btn mo_boot_btn-primary" value="Import"/>
        </div>



    </div>
    <div class="mo_boot_row mo_boot_mr-1  mo_boot_p-3 mo_boot_px-2" id="tabhead"  style="background-color:#FFFFFF; border:2px solid rgb(15, 127, 182);">
        <div class="mo_boot_col-sm-12">
            <form action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.saveConfig'); ?>" method="post" name="adminForm" id="identity_provider_settings_form">
                <div class="mo_boot_row mo_boot_mt-3" >
                    <div class="mo_boot_col-lg-9 mo_boot_mt-1">
                        <h3>Service Provider Setup <sup><a href="https://docs.miniorange.com/documentation/service-provider-setup" target="_blank" style="font-size:12px;" title="Know More">[Know More]</a></sup></h3>
                    </div>
                    <div class="mo_boot_col-lg-3 mo_boot_mt-1">
                        <input id="mo_saml_local_configuration_form_action" type="hidden" name="option1" value="mo_saml_save_config" />
                        <input type="button" id="idp_end_tour" value="Start Tab Tour" onclick="restart_touridp();" style= " float: right;" class="mo_boot_btn mo_boot_btn-success" />
                    </div>
                    <div class="mo_boot_col-sm-12">
                        <hr>
                        <div>
                            <b>Enter the information gathered from your Identity Provider OR  </b>
                            <input id="sp_upload_metadata" type="button" class='mo_boot_btn mo_boot_btn-primary' onclick='show_metadata_form();' value="Upload IDP Metadata"/>
                            <a href="https://plugins.miniorange.com/step-by-step-guide-for-joomla-single-sign-on-sso" target="_blank" class='mo_boot_btn mo_boot_btn-danger'>Setup Guide</a>

                            <span style="margin-right: 3%;box-shadow: 7px 4px 6px #ccc;">
                                <img src="<?php echo JUri::base();?>/components/com_miniorange_saml/assets/images/youtube.png" style="width: 39px">
                                <a href="https://www.youtube.com/results?search_query=miniorange+joomla+sso" target="_blank" title="Setup Guide Videos" class='mo_saml_icon'><span class="link-text">Guide Videos</span></a>
                            </span>
                        </div>


                    </div>
                </div> 
                <div id="idpdata" class="ex1">
                    <div class="mo_boot_row mo_boot_mt-3" id="sp_select_idp">
                        <div class="mo_boot_col-sm-3">
                            <b>Select your Identity Provider for Guide:</b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <select name="idp_guides" id="idp_guides" class="mo_boot_form-control" style="border: 1px solid #868383 !important;">
                                <option value=""> Select Identity Provider</option>
                                <option value="https://plugins.miniorange.com/guide-joomla-single-sign-sso-using-adfs-idp">ADFS</option>
                                <option value="https://plugins.miniorange.com/joomla-single-sign-sso-using-azure-ad-idp">Azure AD</option>
                                <option value="https://plugins.miniorange.com/joomla-single-sign-sso-using-google-apps-idp">Google Apps</option>
                                <option value="https://plugins.miniorange.com/joomla-single-sign-sso-using-okta-idp">Okta</option>
                                <option value="https://plugins.miniorange.com/saml-single-sign-on-sso-for-joomla-using-office-365-as-idp">Office 365</option>
                                <option value="https://plugins.miniorange.com/joomla-single-sign-sso-using-salesforce-idp">SalesForce</option>
                                <option value="https://plugins.miniorange.com/joomla-single-sign-sso-using-onelogin-idp">OneLogin</option>
                                <option value="https://plugins.miniorange.com/saml-single-sign-on-sso-for-joomla-using-simplesaml-as-idp">SimpleSAML</option>
                                <option value="https://plugins.miniorange.com/joomla-single-sign-sso-using-miniorange-idp">Miniorange</option>
                                <option value="https://plugins.miniorange.com/joomla-single-sign-on-sso-using-centrify-as-idp">Centrify</option>
                                <option value="https://plugins.miniorange.com/joomla-single-sign-sso-using-bitium-idp">Bitium</option>
                                <option value="https://plugins.miniorange.com/guide-to-configure-lastpass-as-an-idp-saml-sp">LastPass</option>
                                <option value="https://plugins.miniorange.com/guide-for-pingfederate-as-idp-with-joomla">Ping Federate</option>
                                <option value="https://plugins.miniorange.com/guide-for-joomla-single-sign-on-sso-using-rsa-securid-as-idp">RSA SecureID</option>
                                <option value="https://plugins.miniorange.com/guide-for-openam-as-idp-with-joomla">OpenAM</option>
                                <option value="https://plugins.miniorange.com/guide-for-auth0-as-idp-with-joomla">Auth0</option>
                                <option value="https://plugins.miniorange.com/joomla-single-sign-sso-using-authanvil-ias-dp">Auth Anvil</option>
                                <option value="https://plugins.miniorange.com/guide-to-setup-shibboleth2-as-idp-with-joomla">Shibboleth 2</option>
                                <option value="https://plugins.miniorange.com/guide-to-setup-shibboleth3-as-idp-with-joomla">Shibboleth 3</option>
                                <option value="https://plugins.miniorange.com/oracle-access-manager-as-idp-and-joomla-as-sp">Oracle Access Manager</option>
                                <option value="https://plugins.miniorange.com/saml-single-sign-sso-joomla-using-wso2">WSO2</option>
                                <option value="https://plugins.miniorange.com/joomla-single-sign-sso-using-pingone-idp">PingOne</option>
                                <option value="http://plugins.miniorange.com/joomla-single-sign-on-sso-using-jboss-keycloak-idp">JBoss Keycloak</option>
                                <option value="https://plugins.miniorange.com/step-by-step-guide-for-joomla-single-sign-on-sso">Custom IDP</option>
                            </select>
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-3" id="sp_entity_id_idp">
                        <div class="mo_boot_col-sm-3">
                            <b>IdP Entity ID<span style="color:red">*</span></b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <input type="text" class="mo_boot_form-control" name="idp_entity_id" style="border: 1px solid #868383 !important;" placeholder="Identity Provider Entity ID or Issuer" value="<?php echo $idp_entity_id; ?>" required />
                            <b>Note :</b> You can find the EntityID in Your Identity Provider-Metadata XML file enclosed in <code>EntityDescriptor</code> tag having attribute as <code>entityID</code>
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-3" id="sp_nameid_format_idp">
                        <div class="mo_boot_col-sm-3">
                            <b>NameID Format</b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <select class="mo_boot_form-control" id="name_id_format" name="name_id_format" style="border: 1px solid #868383 !important;">
                                <option value="urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress"
                                    <?php if ($name_id_format == "urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress") echo 'selected = "selected"' ?>>
                                    urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress
                                </option>
                                <option value="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified"
                                    <?php if ($name_id_format == "urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified") echo 'selected = "selected"' ?>>
                                    urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified
                                </option>
                            </select>
                            <b>Note: </b>If you are using ADFS as IdP then the NameID Format should be <code>unspecified</code>.
                        </div>
                    </div>
                    
                    <div class="mo_boot_row mo_boot_mt-3" id="sp_sso_url_idp">
                        <div class="mo_boot_col-sm-3">
                            <b>Single Sign-On Service URL<span style="color:red">*</span></b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <input class="mo_saml_table_textbox mo_boot_form-control" type="url" placeholder="Single Sign-On Service URL (Http-Redirect) binding of your IdP" name="single_signon_service_url" style="border: 1px solid #868383 !important;" value="<?php echo $single_signon_service_url; ?>" required />
                            <b>Note :</b> You can find the SAML Login URL in Your Identity Provider-Metadata XML file enclosed in <code>SingleSignOnService</code> tag (Binding type: HTTP-Redirect)
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-3" id="sp_certificate_idp">
                        <div class="mo_boot_col-sm-3">
                            <b>X.509 Certificate</b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <textarea rows="5" cols="80" name="certificate" style="width: 100%; border: 1px solid #868383 !important;" placeholder="Copy and Paste the content from the downloaded certificate or copy the content enclosed in 'X509Certificate' tag (has parent tag 'KeyDescriptor use=signing') in IdP-Metadata XML file"><?php echo $certificate; ?></textarea>
                            <span><b>NOTE:</b> Format of the certificate:<br>
                                -----BEGIN CERTIFICATE-----<br>
                                XXXXXXXXXXXXXXXXXXXXXXXXXXX<br>
                                -----END CERTIFICATE-----</span>
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-3" id="sp_slo_idp">
                        <div class="mo_boot_col-sm-3">
                            <b>Single Logout Service URL</b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <input class="mo_saml_table_textbox mo_boot_form-control" type="text" name="single_logout_url" placeholder="Single Logout Service URL" disabled>
                            <b>Note :</b> You can find the SAML Logout URL in Your Identity Provider-Metadata XML file enclosed in <code>SingleLogoutService</code> tag <b><a href='#' class='premium' onclick="moSAMLUpgrade();">[Premium</a>, <a href='#' class='premium' onclick="moSAMLUpgrade();">Enterprise]</a></b>
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-3">
                        <div class="mo_boot_col-sm-3">
                            <b>Signature Algorithm</b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <select class="mo_boot_form-control" style="border: 1px solid #868383 !important;" disabled>
                                <option>sha256</option>
                            </select>
                            <b>Note: </b>Algorithm used in the signing process. (Algorithm eg. sha256, sha384, sha512, sha1 etc) <b><a href='#' class='premium' onclick="moSAMLUpgrade();">[Enterprise]</a></b>
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-3" id="sp_binding_type">
                        <div class="mo_boot_col-sm-3">
                            <b>Select Biniding type:</b>
                        </div>
                        <div class="mo_boot_col-sm-9">
                            <input type="radio" name="miniorange_saml_idp_sso_binding" value="HttpRedirect" checked=1 aria-invalid="false" disabled> <span>Use HTTP-Redirect Binding for SSO</span><br>
                            <input type="radio"  name="miniorange_saml_idp_sso_binding" value="HttpPost" aria-invalid="false" disabled> 
                            <span>Use HTTP-POST Binding for SSO <b><a href='#' class='premium' onclick="moSAMLUpgrade();">[Standard</a>, <a href='#' class='premium' onclick="moSAMLUpgrade();">Premium</a>, <a href='#' class='premium' onclick="moSAMLUpgrade();">Enterprise]</a></b></span>
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-3" id="sp_saml_request_idp">
                        <div class="mo_boot_col-sm-3">
                            <b>Sign SSO and SLO request</b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <input type="checkbox" name="saml_request_sign" style="border: 1px solid #868383 !important;" disabled>
                            <b> 
                                <a href='#' class='premium' onclick="moSAMLUpgrade();">[Standard,</a>
                                <a href='#' class='premium' onclick="moSAMLUpgrade();">Premium,</a>
                                <a href='#' class='premium' onclick="moSAMLUpgrade();">Enterprise]</a>
                            </b>
                        </div>
                    </div><br>
                    <div class="mo_boot_row mo_boot_mt-3" id="saml_login">
                        <div class="mo_boot_col-sm-3">
                            <b>Enable Login with SAML</b>
                        </div>
                        <div class="mo_boot_col-sm-8">
                            <input type="checkbox" id ="login_link_check" name="login_link_check" class="mo_saml_custom_checkbox" onclick="showLink()" value="1"
                                    <?php 
                                        $count = isset($attribute['login_link_check']) ? $attribute['login_link_check'] : "";
                                        $dynamicLink=isset($attribute['dynamic_link']) && !empty($attribute['dynamic_link']) ? $attribute['dynamic_link'] : "";
                                        if($count ==1)                        
                                            echo 'checked="checked"';                           
                                        else
                                            $dynamicLink="Login with your IDP";
                                    ?>
                            >
                            <span> &nbsp;Add SSO link on login page </span>
                            <input type="text" id="dynamicText" name="dynamic_link" placeholder="Enter your IDP Name" value="<?php echo $dynamicLink; ?>" class="mo_boot_form-control mo_boot_mt-3" >
                            <?php
                                if($count!=1)
                                {
                                    echo '<script>document.getElementById("dynamicText").style.display="none"</script>';
                                }
                            ?>
                        </div><br><br><br>
                        <div class="mo_boot_col-sm-3">
                            <b>SSO URL:</b>
                        </div>
                        <div class="mo_boot_col-sm-8 mo_saml_highlight_background_url_note">
                            <span id="sso_url" >
                                <a style="cursor: pointer;"><?php echo '<b>' . $sp_base_url . '?morequest=sso</b>'; ?></a>
                            </span>
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-5">
                        <div class="mo_boot_col-sm-12 mo_boot_text-center">
                            <input type="submit" class="mo_boot_btn mo_boot_btn-primary" value="Save"/>
                            <input  type="button" id='test-config' <?php if ($idp_entity_id) echo "enabled";else echo "disabled"; ?> title='You can only test your configuration after saving your Identity Provider Settings.' class="mo_boot_btn mo_boot_btn-primary" onclick='showTestWindow()' value="Test Configuration">
                            <input type="button" class="mo_boot_btn mo_boot_btn-primary" onclick="show_import_export()" value="Import/Export"/>
                        </div>
                    </div>
                </div>
            </form>
        </div>
     </div>
    <?php
}