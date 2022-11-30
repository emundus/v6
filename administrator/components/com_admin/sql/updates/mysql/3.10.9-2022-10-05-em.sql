delete from jos_securitycheckpro_whitelist where ip = '92.154.69.34';
delete from jos_securitycheckpro_blacklist where ip = '192.99.4.63';
delete from jos_securitycheckpro_blacklist where ip = '69.163.169.133';

update jos_extensions set manifest_cache = json_replace(manifest_cache,'$.version','2.4.0') where element in ('com_securitycheckpro');
