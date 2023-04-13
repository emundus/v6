<?php


defined("\x5f\112\105\x58\105\103") or die;
include "\x42\x61\163\x69\x63\105\x6e\x75\155\x53\101\115\114\x2e\x70\x68\x70";
class mo_idp_info extends BasicEnumSAML
{
    const idp_entity_id = "\151\144\160\137\145\156\x74\151\x74\x79\137\151\x64";
    const binding = "\x62\x69\156\144\x69\x6e\147";
    const single_signon_service_url = "\163\151\156\147\x6c\x65\137\163\151\x67\x6e\x6f\x6e\137\x73\x65\x72\166\x69\x63\x65\137\x75\x72\154";
    const certificate = "\143\x65\162\x74\x69\146\x69\143\141\164\x65";
    const single_logout_url = "\163\151\x6e\x67\154\x65\137\x6c\157\147\x6f\x75\x74\137\x75\162\x6c";
    const idp_name = "\151\x64\x70\x5f\x6e\x61\155\x65";
    const domain_mapping = "\x64\157\x6d\141\x69\x6e\x5f\x6d\x61\x70\160\151\156\147";
    const public_certificate = "\x70\x75\142\154\x69\x63\137\x63\145\162\164\x69\146\151\x63\141\x74\145";
    const private_certificate = "\x70\x72\x69\166\141\x74\145\137\x63\145\162\x74\151\146\x69\x63\x61\164\145";
    const saml_request_sign = "\x73\141\x6d\x6c\x5f\162\x65\161\165\x65\x73\164\137\x73\x69\147\x6e";
    const name_id_format = "\x6e\x61\155\145\137\x69\144\x5f\146\x6f\x72\x6d\141\164";
    const miniorange_saml_idp_slo_binding = "\x6d\x69\156\151\x6f\162\x61\x6e\147\145\x5f\163\141\x6d\x6c\x5f\151\144\x70\137\163\x6c\157\x5f\142\x69\x6e\144\x69\156\147";
    const AuthnContextClassRef = "\x41\x75\164\x68\x6e\103\x6f\156\x74\x65\x78\164\x43\x6c\141\x73\163\x52\145\x66";
}
class mo_attribute_mapping extends BasicEnumSAML
{
    const idp_id = "\151\144\160\137\x69\144";
    const username = "\165\x73\x65\x72\x6e\141\x6d\145";
    const email = "\x65\155\141\x69\154";
    const name = "\x6e\x61\155\145";
    const first_name = "\x66\x69\x72\163\x74\137\156\141\155\x65";
    const last_name = "\x6c\x61\x73\164\x5f\x6e\141\155\145";
    const user_profile_attributes = "\x75\x73\145\x72\x5f\160\162\157\x66\x69\x6c\145\137\141\164\x74\162\x69\142\x75\x74\x65\163";
    const user_field_attributes = "\165\x73\x65\x72\x5f\146\151\x65\x6c\144\x5f\x61\164\x74\x72\151\x62\x75\x74\x65\x73";
    const user_contact_attributes = "\x75\163\x65\162\x5f\143\x6f\156\x74\141\x63\164\137\x61\x74\164\x72\151\142\165\x74\145\x73";
    const user_cw_attributes = "\165\163\145\x72\137\x63\x77\137\141\x74\x74\162\151\142\165\x74\145\163";
    const disable_update_existing_customer_attributes = "\x64\x69\163\x61\142\x6c\x65\x5f\165\x70\144\x61\164\x65\137\x65\x78\x69\163\164\151\x6e\x67\137\143\165\163\164\157\x6d\145\x72\x5f\141\164\x74\162\151\142\x75\x74\x65\163";
}
class mo_role_mapping extends BasicEnumSAML
{
    const idp_id = "\x69\x64\160\137\151\144";
    const role_mapping_count = "\162\157\154\145\137\x6d\x61\x70\x70\x69\x6e\x67\x5f\143\157\165\x6e\164";
    const mapping_memberof_attribute = "\x6d\x61\160\x70\151\156\147\x5f\x6d\145\155\x62\145\162\x6f\x66\x5f\x61\x74\x74\x72\x69\142\x75\x74\145";
    const role_mapping_key_value = "\x72\157\154\x65\x5f\x6d\x61\x70\x70\151\156\147\137\x6b\x65\171\137\166\x61\154\x75\x65";
    const do_not_auto_create_users = "\x64\x6f\x5f\156\157\x74\x5f\141\x75\x74\157\x5f\x63\x72\x65\141\x74\145\137\x75\x73\x65\x72\x73";
    const enable_saml_role_mapping = "\145\156\141\142\154\x65\x5f\163\141\155\154\137\x72\x6f\154\x65\137\x6d\x61\160\160\151\x6e\147";
    const mapping_value_default = "\155\x61\x70\x70\151\x6e\x67\x5f\x76\141\154\165\145\137\x64\145\x66\x61\x75\154\164";
    const disable_existing_users_role_update = "\144\x69\x73\x61\x62\154\x65\137\145\170\x69\163\x74\151\156\147\x5f\165\x73\145\x72\163\137\x72\157\154\x65\x5f\x75\x70\x64\x61\x74\145";
    const update_existing_users_role_without_removing_current = "\165\x70\x64\x61\164\145\137\x65\x78\x69\163\164\x69\156\x67\x5f\x75\x73\x65\x72\163\x5f\x72\157\x6c\145\137\x77\151\164\150\157\x75\x74\137\x72\x65\x6d\157\166\x69\156\x67\137\x63\x75\x72\162\x65\156\164";
    const grp = "\x67\x72\160";
    const role_based_redirect_key_value = "\x72\x6f\x6c\145\137\142\x61\x73\x65\x64\137\162\x65\144\x69\x72\x65\143\x74\x5f\153\x65\x79\137\166\x61\x6c\165\x65";
}
class mo_proxy extends BasicEnumSAML
{
    const proxy_host_name = "\x70\x72\157\170\171\x5f\x68\x6f\163\x74\x5f\x6e\141\x6d\x65";
    const port_number = "\160\157\162\164\137\156\x75\155\x62\145\x72";
    const username = "\165\163\x65\x72\x6e\141\155\145";
    const password = "\x70\141\163\163\167\x6f\162\144";
}
class mo_login_setting extends BasicEnumSAML
{
    const idp_id = "\x69\144\x70\137\151\x64";
    const enable_redirect = "\x65\156\141\x62\x6c\x65\137\x72\145\144\x69\x72\145\143\164";
    const user_login_for_other_domains = "\x75\163\x65\162\137\x6c\x6f\147\x69\156\137\146\x6f\x72\137\157\x74\x68\x65\x72\137\x64\157\155\141\151\156\x73";
    const idp_link_page = "\151\144\160\x5f\x6c\151\x6e\x6b\x5f\160\141\x67\145";
    const mo_idp_list_link_page = "\x6d\x6f\x5f\151\144\x70\137\154\x69\163\x74\x5f\154\x69\156\x6b\137\160\141\x67\145";
    const ignore_special_characters = "\151\x67\156\x6f\x72\145\x5f\163\x70\145\x63\151\141\154\x5f\143\x68\141\x72\x61\143\164\x65\162\163";
    const enable_manager_login = "\145\x6e\x61\x62\154\x65\137\155\x61\156\141\147\145\162\137\154\x6f\147\151\x6e";
    const enable_admin_redirect = "\x65\156\x61\x62\x6c\145\137\141\144\155\x69\156\x5f\x72\x65\144\x69\x72\x65\143\164";
    const mo_admin_idp_list_link_page = "\x6d\157\137\141\144\x6d\151\156\137\x69\144\x70\137\x6c\151\163\164\x5f\154\x69\x6e\153\x5f\160\x61\x67\x65";
    const enable_email = "\x65\x6e\141\x62\x6c\145\137\x65\155\x61\151\154";
    const enable_admin_child_login = "\x65\156\141\142\154\145\137\141\x64\x6d\x69\x6e\137\143\x68\x69\x6c\144\137\154\x6f\x67\x69\x6e";
    const enable_manager_child_login = "\x65\x6e\141\x62\x6c\145\137\155\141\156\x61\147\145\162\x5f\x63\150\x69\154\144\137\154\157\x67\151\x6e";
    const enable_do_not_auto_create_users = "\145\156\x61\142\154\145\x5f\144\x6f\x5f\x6e\x6f\x74\137\141\x75\x74\157\137\x63\162\x65\141\164\x65\137\x75\x73\x65\162\163";
}
