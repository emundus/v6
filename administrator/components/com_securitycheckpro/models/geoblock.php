<?php
/**
* Geoblock Model para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

if(!class_exists('JoomlaCompatModel')) {
	if(interface_exists('JModel')) {
		abstract class JoomlaCompatModel extends JModelLegacy {}
	} else {
		class JoomlaCompatModel extends JModel {}
	}
}

/**
* Modelo Securitycheck
*/
class SecuritycheckprosModelGeoblock extends JoomlaCompatModel
{

	
/* Definimos las variables */
var $defaultConfig = array(
	'geoblockcountries'	=> '',
	'geoblockcontinents'	=> '',
);
	
	private $config = null;

/* Obtiene el valor de una opción de configuración de 'htaccess protection' */
public function getValue($key, $default = null, $key_name = 'geoblock')
{
	if(is_null($this->config)) $this->load($key_name);
	
	if(version_compare(JVERSION, '3.0', 'ge')) {
		return $this->config->get($key, $default);
	} else {
		return $this->config->getValue($key, $default);
	}
}

/* Establece el valor de una opción de configuración de 'htaccess protection' */
public function setValue($key, $value, $save = false, $key_name = 'geoblock')
{
	if(is_null($this->config)) {
		$this->load($key_name);
	}
		
	if(version_compare(JVERSION, '3.0', 'ge')) {
		$x = $this->config->set($key, $value);
	} else {
		$x = $this->config->setValue($key, $value);
	}
	if($save) $this->save($key_name);
	return $x;
}

/* Hace una consulta a la tabla #__securitycheckpro_storage, que contiene la configuración de 'htaccess protection' */
public function load($key_name)
{
	$db = JFactory::getDBO();
	$query = $db->getQuery(true);
	$query 
		->select($db->quoteName('storage_value'))
		->from($db->quoteName('#__securitycheckpro_storage'))
		->where($db->quoteName('storage_key').' = '.$db->quote($key_name));
	$db->setQuery($query);
	$res = $db->loadResult();
	
	if(version_compare(JVERSION, '3.0', 'ge')) {
		$this->config = new JRegistry();
	} else {
		$this->config = new JRegistry('securitycheckpro');
	}
	if(!empty($res)) {
		$res = json_decode($res, true);		
		 $this->config->loadArray($res);		
	}
	
}

/* Guarda la configuración de 'htaccess protection' con a la tabla #__securitycheckpro_storage */
public function save($key_name)
{
	if(is_null($this->config)) {
		$this->load($key_name);
	}
		
	$db = JFactory::getDBO();
	$query = $db->getQuery(true);
	
	$data = $this->config->toArray();
	$data = json_encode($data);
		
	$query
		->delete($db->quoteName('#__securitycheckpro_storage'))
		->where($db->quoteName('storage_key').' = '.$db->quote($key_name));
	$db->setQuery($query);
	$db->execute();
		
	$object = (object)array(
		'storage_key'		=> $key_name,
		'storage_value'		=> $data
	);
	$db->insertObject('#__securitycheckpro_storage', $object);
}

/* Obtiene la configuración de los parámetros de la opción 'Protection' */
function getConfig()
{
	$config = array();
	foreach($this->defaultConfig as $k => $v) {
		$config[$k] = $this->getValue($k, $v);
	}
	
	return $config;
}

/* Guarda la modificación de los parámetros de la opción 'Protection' */
function saveConfig($newParams, $key_name = 'geoblock')
{
	foreach($newParams as $key => $value)
	{
		$this->setValue($key,$value,'',$key_name);
	}

	$this->save($key_name);
}

/* Función para descargar la bbdd de Maxmind 2 */
function update_geoblock_database() {
		// Ruta donde se encuentra el fichero
		$datFile = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR .'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'GeoLite2-Country.mmdb';
					
		// Sanity check
		if(!function_exists('gzinflate')) {
			return JText::_('COM_SECURITYCHECKPRO_ERR_NOGZSUPPORT');
		}

		// Try to download the package, if I get any exception I'll simply stop here and display the error
		try
		{
			$compressed = $this->downloadDatabase();
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}

		// Write the downloaded file to a temporary location
		$tmpdir = JPATH_SITE . '/tmp';

		$target = $tmpdir.'/GeoLite2-Country.mmdb.gz';

		$ret = JFile::write($target, $compressed);

		if ($ret === false)
		{
			return JText::_('COM_SECURITYCHECKPRO_ERR_WRITEFAILED');
		}

		unset($compressed);

		// Decompress the file
		$uncompressed = '';

		$zp = @gzopen($target, 'rb');

		if($zp !== false)
		{
			while(!gzeof($zp))
			{
				$uncompressed .= @gzread($zp, 102400);
			}

			@gzclose($zp);

			if (!@unlink($target))
			{
				JFile::delete($target);
			}
		}
		else
		{
			return JText::_('COM_SECURITYCHECKPRO_ERR_CANTUNCOMPRESS');
		}


		// Double check if MaxMind can actually read and validate the downloaded database
		try
		{
			// The Reader want a file, so let me write again the file in the temp directory
			JFile::write($target, $uncompressed);			
		}
		catch(\Exception $e)
		{
			JFile::delete($target);
			// MaxMind could not validate the database, let's inform the user
			return JText::_('COM_SECURITYCHECKPRO_ERR_INVALIDDB');
		}

		JFile::delete($target);


		// Check the size of the uncompressed data. When MaxMind goes into overload, we get crap data in return.
		if (strlen($uncompressed) < 1048576)
		{
			return JText::_('COM_SECURITYCHECKPRO_ERR_MAXMINDRATELIMIT');
		}

		// Check the contents of the uncompressed data. When MaxMind goes into overload, we get crap data in return.
		if (stristr($uncompressed, 'Rate limited exceeded') !== false)
		{
			return JText::_('COM_SECURITYCHECKPRO_ERR_MAXMINDRATELIMIT');
		}

		// Remove old file
		JLoader::import('joomla.filesystem.file');

		if (JFile::exists($datFile))
		{
			if(!JFile::delete($datFile))
			{
				return JText::_('COM_SECURITYCHECKPRO_ERR_CANTDELETEOLD');
			}
		}

		// Write the update file
		if (!JFile::write($datFile, $uncompressed))
		{
			return JText::_('COM_SECURITYCHECKPRO_ERR_CANTWRITE');
		}
		
		// Actualizamos la fecha de la última descarga del fichero Geoipv2
		$this->update_latest_download();
		
		// Actualizamos la variable que controla si se muestra el popup de actualización
		$mainframe = JFactory::getApplication();
		$mainframe->SetUserState("update_run",true);		
		
		return JText::_('COM_SECURITYCHECKPRO_DATABASE_UPDATED_OK');
}

/* Función para descargar el archivo Geoipv2 de la web de Maxmind */
private function downloadDatabase()
	{
		// Download the latest MaxMind GeoCountry Lite2 database
		$url = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz';
		
		$http = JHttpFactory::getHttp();

		// Let's bubble up the exception, we will take care in the caller
		$response   = $http->get($url);
		$compressed = $response->body;
		
		// Generic check on valid HTTP code
		if($response->code > 299) {
			throw new Exception(JText::_('COM_SECURITYCHECKPRO_ERR_MAXMIND_GENERIC') . " (" . $response->code . ")" );
		}
		

		// An empty file indicates a problem with MaxMind's servers
		if (empty($compressed))	{
			throw new Exception(JText::_('COM_SECURITYCHECKPRO_ERR_EMPTYDOWNLOAD'));
		}

		// Sometimes you get a rate limit exceeded
		if (stristr($compressed, 'Rate limited exceeded') !== false) {
			throw new Exception(JText::_('COM_SECURITYCHECKPRO_ERR_MAXMINDRATELIMIT'));
		}

		return $compressed;
	}

/* Función que actualiza la fecha de la última descarga del fichero Geoipv2 */
function update_latest_download() {
	
	$db = JFactory::getDBO();
	
	$query = $db->getQuery(true)
		->delete($db->quoteName('#__securitycheckpro_storage'))
		->where($db->quoteName('storage_key').' = '.$db->quote('geoip_database_update'));
	$db->setQuery($query);
	$db->execute();
	
	$this->get_latest_database_update();
}
	
/* Función que devuelve el número de días desde la última actualización de la bbdd de Maxmind */
function get_latest_database_update() {
	
	// Inicializamos variables
	$days_since_last_update=0;
	
	$now = array(
	"date" => date('Y-m-d')
	);
	
	$db = JFactory::getDBO();
	$query = $db->getQuery(true);
	$query 
		->select($db->quoteName('storage_value'))
		->from($db->quoteName('#__securitycheckpro_storage'))
		->where($db->quoteName('storage_key').' = '.$db->quote('geoip_database_update'));
	$db->setQuery($query);
	$latest = $db->loadResult();
	
	// Si no hay ningún valor establecemos la fecha actual
	if ( empty($latest) ) {
		$params = utf8_encode(json_encode($now));			
		$object = (object)array(
			'storage_key'		=> 'geoip_database_update',
			'storage_value'		=> $params
		);
			
		try {
			$result = $db->insertObject('#__securitycheckpro_storage', $object);			
		} catch (Exception $e) {				
		}
	} else {
		$latest = json_decode($latest, true);			
		
		$last_check = new DateTime(date('Y-m-d H:i:s',strtotime($latest['date'])));
		$now = new DateTime(date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s'))));
		$diff = $now->diff($last_check);
		$days_since_last_update = $diff->days;
	}
			
	return $days_since_last_update;
	
}
}