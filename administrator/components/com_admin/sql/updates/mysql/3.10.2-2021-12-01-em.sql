UPDATE `jos_securitycheckpro_storage` SET storage_value = REPLACE(storage_value, '"session_protection_active":"1"', '"session_protection_active":"0"') WHERE storage_key like "pro_plugin";
