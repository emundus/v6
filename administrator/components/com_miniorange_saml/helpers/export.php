<?php


defined("\x5f\112\105\130\105\x43") or die;
include "\x42\x61\x73\x69\x63\105\156\x75\155\123\101\115\x4c\56\160\150\x70";
class mo_idp_info extends BasicEnumSAML
{
    const idp_entity_id = "\x69\x64\x70\137\145\156\x74\151\164\x79\x5f\x69\144";
    const binding = "\x62\x69\x6e\x64\x69\x6e\x67";
    const single_signon_service_url = "\x73\151\x6e\147\154\145\137\163\x69\x67\156\157\x6e\x5f\163\x65\162\x76\151\x63\x65\137\x75\x72\x6c";
    const certificate = "\x63\x65\x72\x74\x69\x66\x69\x63\x61\164\x65";
    const single_logout_url = "\x73\151\156\x67\154\x65\137\154\x6f\x67\x6f\x75\x74\137\x75\x72\x6c";
    const idp_name = "\x69\144\160\137\156\x61\x6d\145";
    const domain_mapping = "\144\157\155\x61\x69\156\137\155\x61\160\160\x69\x6e\x67";
    const public_certificate = "\x70\165\142\154\151\143\x5f\143\x65\162\x74\x69\146\x69\143\141\x74\145";
    const private_certificate = "\x70\162\x69\166\141\x74\x65\137\x63\x65\x72\x74\x69\x66\x69\x63\x61\x74\145";
    const saml_request_sign = "\x73\x61\x6d\154\x5f\x72\145\x71\165\x65\163\164\137\x73\151\147\156";
    const name_id_format = "\156\141\155\145\x5f\151\x64\137\x66\157\162\x6d\x61\x74";
    const miniorange_saml_idp_slo_binding = "\155\151\156\151\157\x72\141\156\147\x65\x5f\x73\x61\x6d\154\137\x69\x64\160\x5f\163\x6c\x6f\137\142\x69\156\x64\x69\156\147";
}
class mo_attribute_mapping extends BasicEnumSAML
{
    const idp_id = "\x69\x64\160\x5f\151\x64";
    const username = "\165\x73\145\162\156\x61\x6d\x65";
    const email = "\145\155\141\151\154";
    const name = "\x6e\141\x6d\145";
    const user_profile_attributes = "\165\163\x65\162\137\160\162\x6f\x66\x69\154\145\137\141\x74\x74\x72\151\142\165\164\145\163";
    const user_field_attributes = "\x75\x73\145\162\x5f\x66\151\x65\154\144\x5f\141\164\x74\162\151\x62\165\x74\145\163";
    const disable_update_existing_customer_attributes = "\144\x69\163\141\142\x6c\x65\137\165\x70\144\x61\164\x65\x5f\x65\170\151\x73\x74\151\x6e\147\137\143\x75\x73\164\157\x6d\x65\x72\137\141\164\164\162\x69\x62\165\x74\145\163";
}
class mo_role_mapping extends BasicEnumSAML
{
    const idp_id = "\x69\144\x70\x5f\151\144";
    const role_mapping_count = "\162\157\x6c\x65\137\155\141\160\160\151\156\147\137\143\157\x75\156\164";
    const mapping_memberof_attribute = "\155\x61\x70\x70\x69\x6e\147\x5f\x6d\x65\x6d\x62\145\x72\157\x66\x5f\x61\x74\164\162\x69\142\165\164\x65";
    const role_mapping_key_value = "\162\x6f\x6c\145\137\155\141\x70\160\x69\x6e\147\137\153\145\171\x5f\166\x61\154\165\x65";
    const do_not_auto_create_users = "\144\157\x5f\156\157\164\x5f\141\165\164\157\x5f\x63\x72\x65\x61\164\145\x5f\x75\x73\x65\x72\x73";
    const enable_saml_role_mapping = "\x65\x6e\x61\x62\154\145\x5f\x73\141\x6d\x6c\137\162\x6f\154\x65\137\155\x61\x70\160\151\x6e\x67";
    const mapping_value_default = "\x6d\x61\160\x70\x69\156\x67\137\166\x61\x6c\x75\145\137\x64\145\146\x61\165\x6c\x74";
    const disable_existing_users_role_update = "\x64\151\x73\141\142\154\x65\137\145\x78\x69\x73\x74\x69\156\147\137\x75\x73\x65\x72\163\x5f\162\x6f\154\145\x5f\165\x70\144\141\164\x65";
    const update_existing_users_role_without_removing_current = "\165\x70\x64\141\x74\145\137\145\170\x69\163\x74\x69\x6e\147\137\165\163\x65\162\163\137\x72\157\x6c\145\137\167\151\x74\x68\x6f\165\164\137\x72\145\155\x6f\x76\x69\156\147\137\143\x75\162\162\145\x6e\164";
    const grp = "\147\x72\160";
    const role_based_redirect_key_value = "\x72\157\154\145\137\142\x61\x73\145\x64\x5f\x72\x65\144\151\162\x65\143\164\137\x6b\145\171\137\x76\141\154\x75\145";
}
class mo_proxy extends BasicEnumSAML
{
    const proxy_host_name = "\x70\x72\x6f\170\171\137\150\157\163\164\x5f\156\x61\155\x65";
    const port_number = "\x70\x6f\162\164\x5f\x6e\x75\155\x62\x65\162";
    const username = "\165\x73\x65\x72\x6e\141\155\145";
    const password = "\160\141\163\163\167\x6f\162\x64";
}
class mo_login_setting extends BasicEnumSAML
{
    const idp_id = "\x69\x64\x70\x5f\151\x64";
    const enable_redirect = "\x65\x6e\x61\142\x6c\x65\137\162\145\144\x69\x72\x65\143\x74";
    const user_login_for_other_domains = "\165\163\x65\x72\x5f\x6c\157\147\151\x6e\137\x66\157\162\x5f\x6f\164\150\145\x72\137\144\157\x6d\x61\x69\x6e\163";
    const idp_link_page = "\x69\144\160\x5f\154\x69\156\153\x5f\160\141\x67\x65";
    const mo_idp_list_link_page = "\x6d\157\x5f\x69\144\160\x5f\154\151\163\164\137\x6c\151\156\153\x5f\160\141\147\145";
    const ignore_special_characters = "\151\x67\156\x6f\162\145\x5f\x73\x70\x65\x63\x69\x61\x6c\x5f\x63\x68\x61\162\141\x63\164\x65\x72\x73";
    const enable_manager_login = "\x65\x6e\x61\142\x6c\145\x5f\x6d\141\156\x61\x67\145\x72\137\x6c\157\x67\x69\156";
    const enable_admin_redirect = "\x65\156\x61\x62\154\x65\x5f\141\144\155\151\156\x5f\162\145\144\151\162\x65\x63\164";
    const mo_admin_idp_list_link_page = "\155\157\137\x61\x64\155\x69\156\x5f\151\144\160\137\154\151\163\x74\x5f\x6c\151\x6e\x6b\137\x70\141\147\x65";
}
