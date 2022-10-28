<?php
/**
 * Created by PhpStorm.
 * User: miniorange
 * Date: 07-10-2018
 * Time: 02:53
 */
 defined('_JEXEC') or die;
/*
 * @package    miniOrange
 * @subpackage Plugins
 * @license    GNU/GPLv3
 * @copyright  Copyright 2015 miniOrange. All Rights Reserved.
*/

include "BasicEnum.php";

class mo_idp_info extends BasicEnum{
	
    const idp_entity_id = "idp_entity_id";
	const name_id_format="name_id_format";
	const binding ="binding";
	const single_signon_service_url = "single_signon_service_url";
    const certificate = 'certificate';
}

class mo_attribute_mapping extends BasicEnum{
	
	const enable_email = "enable_email";
	const name = "name";
		
}

class mo_role_mapping extends BasicEnum{
	
	const enable_saml_role_mapping = "enable_saml_role_mapping";
	const mapping_value_default ="mapping_value_default";
	
}

class mo_proxy extends BasicEnum{
	
	const proxy_host_name = "proxy_host_name";
	const port_number = "port_number";
	const username ="username";
	const password = "password";	
	
}
 
