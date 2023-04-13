
function show_idp_link_box()
{
    if(jQuery('#enable_admin_redirect').prop("checked") ||jQuery('#enable_manager_child_login').prop("checked") || jQuery('#enable_manager_login').prop("checked") || jQuery('#enable_admin_child_redirect').prop("checked"))
    {
        jQuery('#mo_admin_idp_link_page').show();
    }
    else
    {
        jQuery('#mo_admin_idp_link_page').hide();
    }
}
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

function show_import_export_form(){
    jQuery("#idp_settings_add_new_form").hide();
   jQuery("#idp_list_table").hide();
   jQuery("#mo_saml_import_export_id").show();
}
function hide_import_export_form(){
    jQuery("#idp_settings_add_new_form").show();
    jQuery("#idp_list_table").show();
    jQuery("#mo_saml_import_export_id").hide();
}

function add_css_tab(element) {
    jQuery(".mo_nav_tab_active ").removeClass("mo_nav_tab_active").removeClass("active");
    jQuery(element).addClass("mo_nav_tab_active");
}

function copyToClipboard(element) {
    jQuery(".selected-text").removeClass("selected-text");
    var temp = jQuery("<input>");
    jQuery("body").append(temp);
    jQuery(element).addClass("selected-text");
    temp.val(jQuery(element).text().trim()).select();
    document.execCommand("copy");
    temp.remove();
}


function show_gen_cert_form() {
    jQuery('#generate_certificate_form').show();
    jQuery('#mo_gen_cert').hide();
    jQuery('#mo_gen_tab').hide();
}

function hide_gen_cert_form() {
    jQuery('#generate_certificate_form').hide();
    jQuery('#mo_gen_cert').show();
    jQuery('#mo_gen_tab').show();
}
function back_to_multiple_idp_page(){
    jQuery("#mo_saml_idp_cancel_form").submit();
}
function show_metadata_form() {
    jQuery("#upload_metadata_form").show();
    jQuery("#idpdata").hide();
    jQuery("#identity_provider_settings_form").hide();
}
function hide_metadata_form() {
    jQuery("#upload_metadata_form").hide();
    jQuery("#idpdata").show();
    jQuery("#identity_provider_settings_form").show();
}

function showTestWindow(idp) {
    var testconfigurl = window.location.href;
    testconfigurl = testconfigurl.substr(0,testconfigurl.indexOf("administrator")) + "?morequest=sso&q=test_config&idp=" + idp; 
    var myWindow = window.open(testconfigurl, "TEST SAML IDP", "scrollbars=1 width=800, height=600");	
}

function remove_role(id) {
    var res = id.replace('mo_role_','row_');
    jQuery('#'+res).remove();
}

function removedomainmappingval(entryid) {
    jQuery("#moremovedomainmapping").val(entryid);
    jQuery("#removemappeddomain").submit();
}


jQuery(document).ready(function() {
    
    

    var userprofileattr = jQuery('input[name^=user_profile_attr_value]');
    var countUserAttributes = userprofileattr.length;

    jQuery('#add_user_attribute').click(function(){
        var sel = "<div class=\'row userAttr\' style=\'padding-bottom:1%;\' id=\'uparow_" + countUserAttributes + "\'><div style=\'width:30%;display:inline-block;\'><input type=\'text\' class=\'mo_boot_form-control\' name=\'user_profile_attr_name[" + countUserAttributes + "]\' value=\'\' /></div><div style=\'width:30%;display:inline-block;margin-left:5px;\'><input class=\'mo_boot_form-control\' type=\'text\' name=\'user_profile_attr_value[" + countUserAttributes + "]\' value=\'\' /></div></div>";

        if(countUserAttributes !== 0){ 
             jQuery(sel).insertAfter(jQuery("#uparow_" +(countUserAttributes-1)));
             countUserAttributes += 1;
        }
        else{
            jQuery(sel).insertAfter(jQuery('#before_attr_list_upa'));
            countUserAttributes += 1;
        }
      
    });

    jQuery('#remove_user_attibute').click(function(){
        if(countUserAttributes !== 0){
            countUserAttributes -= 1;
            jQuery("#userProfileAttrDiv .userAttr:last").remove();
        }
    });
  

    var userfieldattr = jQuery('input[name^=user_field_attr_name]');
    var countUserFieldAttributes = userfieldattr.length;
    jQuery('#add_user_field_attibute').click(function(){
        var sel = "<div class=\'row userAttr\' style=\'padding-bottom:1%;\' id=\'uparow1_" + countUserFieldAttributes + "\'><div style=\'width:30%;display:inline-block;\'><input type=\'text\' class=\'mo_boot_form-control\' name=\'user_field_attr_name[" + countUserFieldAttributes + "]\' value=\'\' /></div><div style=\'width:30%;display:inline-block;margin-left:5px;\'><input class=\'mo_boot_form-control\' type=\'text\' name=\'user_field_attr_value[" + countUserFieldAttributes + "]\' value=\'\' /></div></div>";

        if(countUserFieldAttributes !== 0){ 
            jQuery(sel).insertAfter(jQuery("#uparow1_" +(countUserFieldAttributes-1)));
            countUserFieldAttributes += 1;
        }
        else{
            jQuery(sel).insertAfter(jQuery('#before_attr_list_upa1'));
            countUserFieldAttributes += 1;
        }
    });

    jQuery('#remove_user_field_attibute').click(function(){
        if(countUserFieldAttributes !== 0){
            countUserFieldAttributes -= 1;
            jQuery("#userProfileAttrDiv1 .userAttr:last").remove();
        }
    });


    var usercontactattr = jQuery('input[name^=user_contact_attr_name]');
    var countUserContactAttributes = usercontactattr.length;

    jQuery('#add_user_contact_attibute').click(function(){
        var sel = "<div class=\'row userAttr\' style=\'padding-bottom:1%;\' id=\'uparow2_" + countUserContactAttributes + "\'><div style=\'width:30%;display:inline-block;\'><input type=\'text\' class=\'mo_boot_form-control\' name=\'user_contact_attr_name[" + countUserContactAttributes + "]\' value=\'\' /></div><div style=\'width:30%;display:inline-block;margin-left:5px;\'><input class=\'mo_boot_form-control\' type=\'text\' name=\'user_contact_attr_value[" + countUserContactAttributes + "]\' value=\'\' /></div></div>";

        if(countUserContactAttributes !== 0){ 
            jQuery(sel).insertAfter(jQuery("#uparow2_" +(countUserContactAttributes-1)));
            countUserContactAttributes += 1;
        }
        else{
            jQuery(sel).insertAfter(jQuery('#before_attr_list_upa2'));
            countUserContactAttributes += 1;
        }
    });


    jQuery('#remove_user_contact_attibute').click(function(){

        if(countUserContactAttributes !== 0){
            countUserContactAttributes -= 1;
            jQuery("#userProfileAttrDiv2 .userAttr:last").remove();
        }
    });




    jQuery(".premium").click(function () {
        jQuery(".nav-tabs a[href=#licensing-plans]").tab("show");
    });

    if(jQuery("#enable_redirect").prop("checked")){
        jQuery('#mo_idp_link_page').show();
    }else{
        jQuery('#mo_idp_link_page').hide();
    }

    jQuery(".enable_redirect").change(function() {
        if(jQuery(this).is(":checked")) {
            
            jQuery(this).siblings('.idp_link').show();
        }
        else {
            jQuery(this).siblings('.idp_link').hide();
        }
    });

    jQuery("#click_redirect").click(function(){
        jQuery("#auto-redirect").slideToggle("fast");
    });

    jQuery("#click_admin_redirect").click(function(){
        jQuery("#auto-admin-redirect").slideToggle("fast");
    });

    jQuery("#login_method_info").click(function(){
        jQuery("#login_method").slideToggle("fast");
    });

    jQuery("#do_not_auto_creation_info").click(function(){
        jQuery("#do_not_auto_creation").slideToggle("fast");
    });

    if(jQuery("#show_login_link").prop("checked")){
        jQuery("#mo_idp_link_page").show();
    }
    else
    {
        jQuery("#mo_idp_list_link_page").hide();
    }

    jQuery("input[type=radio]").change(function(){
        if(jQuery(this).val()=="SHOW_IDP_LINK")
        {
            jQuery("#mo_idp_list_link_page").show();
        }
        else
        {
            jQuery("#mo_idp_list_link_page").hide();
        }
    });

    if(jQuery('#enable_admin_redirect').prop("checked") ||jQuery('#enable_manager_child_login').prop("checked") || jQuery('#enable_manager_login').prop("checked") || jQuery('#enable_admin_child_redirect').prop("checked"))
    {
        jQuery('#mo_admin_idp_link_page').show();
    }

    jQuery('.copy_sso_url').click(function(){
        var id=jQuery(this).siblings('.sso_url').attr('id');
        var prefix='#';
        var sso_url_id=prefix.concat(id);
        jQuery(".selected-text").removeClass("selected-text");
        var temp = jQuery("<input>");
        jQuery("body").append(temp);
        jQuery(sso_url_id).addClass("selected-text");
        temp.val(jQuery(sso_url_id).text().trim()).select();
        document.execCommand("copy");
        temp.remove();
                            
    });

    var homepath = window.location.href;
    var homepath = homepath.substr(0, homepath.indexOf('administrator'));
    basepath = homepath + 'plugins/authentication/miniorangesaml/';
    jQuery(document).ready(function () {
        jQuery('#cert-link').attr('href', homepath + '?morequest=download_cert');
        jQuery('#cust-cert-link').attr('href', homepath + '?morequest=download_cert');
    });


    jQuery('#upload_metadata_file').click(function(){
        var file = document.getElementById("metadata_uploaded_file");
        var idp_name=jQuery('#idp_name').val();
        if(file.files.length != 0 && idp_name!=''){
            jQuery('#IDP_meatadata_form').submit();
        } else if(idp_name=='')
        {
            alert("Please enter IDP name"); 
        }else {
            alert("Please upload the metadata file");
            jQuery('#metadata_uploaded_file').attr('required','true');
            jQuery('#metadata_url').attr('required','false');
        }
       
    });

    jQuery('#fetch_metadata').click(function(){
        var url = jQuery("#metadata_url").val();
        var idp_name=jQuery('#idp_name').val();
        if(url!='' && idp_name!='')
        {
            jQuery('#IDP_meatadata_form').submit(); 
        }
        else if(idp_name=='')
        {
            alert("Please enter IDP name"); 
        }
        else{
            alert("Please enter the metadata URL");
            jQuery('#metadata_url').attr('required','true');
            jQuery('#metadata_uploaded_file').attr('required','false');
        }
        
    });

    jQuery('#attribute_mapping_info').click(function(){
        jQuery('#info1').slideToggle('fast');
    });
    
    jQuery('#attribute_profile_mapping_info').click(function(){
        jQuery('#profile_mapping_info').slideToggle('fast');
    });

    jQuery('#attribute_field_mapping_info').click(function(){
        jQuery('#field_mapping_info').slideToggle('fast');
    });

    jQuery('#attribute_contact_mapping_info').click(function(){
        jQuery('#contact_mapping_info').slideToggle('fast');
    });

    jQuery("#back_btn").click(function () {
        jQuery("#mo_saml_cancel_form").submit();
    });

    jQuery('#idpguide').on('change', function () {
        var selectedIdp = jQuery(this).find('option:selected').val();
        window.open(selectedIdp, '_blank');
    });
    var existingVariable2 = jQuery('input[name^=mapping_key_]');
    var count = existingVariable2.length;

    jQuery('#add_mapping').click(function(){
        var dropdown = jQuery('#roles_list').html();
        var new_row = '<tr id="row_'+count+'"><td><input class="mo_saml_table_textbox_role_mapping mo_boot_form-control" type="text" name="mapping_key_[]" /></td><td><select class="mo_saml_dropdown mo_boot_form-control" name="mapping_value_[]" style="width:80%" id="role">'+dropdown+'</select></td><td><input type="button" id="mo_role_'+count+'" class="mo_role_delete" style="width:120%;height:24px;" value="-" onclick="remove_role(this.id);" /></td></tr>';

        jQuery('#saml_role_mapping_table').append(new_row);
        count+=1;
    });
   
    jQuery('.group_mapping').click(function()
    {
        if(jQuery(this).prop("checked") == true)
        {
            jQuery('.group_mapping_enable').prop('disabled',false);
        }
        else
        {
            jQuery('.group_mapping_enable').prop('disabled',true);
        }
        
    });

    jQuery('.role_base_redirection').click(function()
    {
        if(jQuery(this).prop("checked") == true)
        {
            jQuery('.enable_role_base_redirection').prop('disabled',false);
        }
        else
        {
            jQuery('.enable_role_base_redirection').prop('disabled',true);
        }
        
    });
    jQuery('.group_mapping_select_option').click(function()
    {
        if(jQuery(this).prop("checked") == true)
        {
            jQuery(this).siblings('.group_mapping_select_option').prop('checked',false);
        }
    });
    

});
