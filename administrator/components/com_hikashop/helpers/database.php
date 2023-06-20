<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopDatabaseHelper {
	protected $db = null;
	public static $check_results = null;
	public $createTable = array();
	public $structure = array();

	public function __construct() {
		$this->db = JFactory::getDBO();
	}

	function addColumns($table, $columns) {
		if(!is_array($columns))
			$columns = array($columns);

		$query = 'ALTER TABLE `'.hikashop_table($table).'` ADD '.implode(', ADD', $columns).';';
		$this->db->setQuery($query);
		$err = false;

		try {
			$this->db->execute();
		}catch(Exception $e) {
			$err = true;
		}
		if(!$err)
			return true;

		if($err && count($columns) > 1) {

			foreach($columns as $col) {
				$query = 'ALTER TABLE `'.hikashop_table($table).'` ADD '.$col.';';

				$this->db->setQuery($query);
				$err = 0;

				try {
					$this->db->execute();
				}catch(Exception $e) {
					$err++;
				}
			}

			if($err < count($columns))
				return true;
		}

		return false;
	}

	function removeColumn($table, $column) {

		$query = 'ALTER TABLE `'.hikashop_table(str_replace('`', '',$table)).'` DROP `'.str_replace('`', '', $column).'`;';
		$this->db->setQuery($query);
		$err = false;

		try {
			$this->db->execute();
		}catch(Exception $e) {
			$app = JFactory::getApplication();
			$app->enqueueMessage($e->getMessage(), 'error');
			$err = true;
		}

		return !$err;
	}

	public function parseTableFile($filename, &$createTable, &$structure) {
		$queries = file_get_contents($filename);
		$tables = explode('CREATE TABLE IF NOT EXISTS', $queries);

		foreach($tables as $oneTable) {
			$fields = explode("\n\t", trim($oneTable));

			$tableNameTmp = substr($oneTable, strpos($oneTable, '`') + 1, strlen($oneTable) - 1);
			$tableName = substr($tableNameTmp, 0, strpos($tableNameTmp, '`'));
			if(empty($tableName))
				continue;

			foreach($fields as $oneField) {
				$oneField = trim($oneField);

				$pattern='/`(.*)`.*AUTO_INCREMENT/msU';
				preg_match($pattern, $oneField , $ai_matches);
				if(isset($ai_matches[1])){
					$structure[$tableName]['AUTO_INCREMENT'][] = $ai_matches[1];
				}

				$pattern='/PRIMARY KEY \(`(.*)`\)/msU';
				preg_match($pattern, $oneField , $pm_matches);
				if(isset($pm_matches[1])){
					$structure[$tableName]['PRIMARY_KEY'] = $pm_matches[1];
				}

				if(substr($oneField,0,1) != '`' || substr($oneField, strlen($oneField) - 1, strlen($oneField)) != ',')
					continue;

				if(empty($structure[$tableName]))
					$structure[$tableName] = array();

				$fieldNameTmp = substr($oneField,strpos($oneField,'`') + 1, strlen($oneField) - 1);
				$fieldName = substr($fieldNameTmp, 0, strpos($fieldNameTmp, '`'));
				$structure[$tableName][$fieldName] = trim($oneField, ',');
			}

			$createTable[$tableName] = 'CREATE TABLE IF NOT EXISTS ' . trim($oneTable);
		}
	}

	public function loadStructure() {
		$this->createTable = array();
		$this->structure = array();

		$this->parseTableFile(HIKASHOP_BACK . 'tables.sql', $this->createTable, $this->structure);

		try{
			$this->db->setQuery("SELECT * FROM #__hikashop_field");
			$custom_fields = $this->db->loadObjectList();
		} catch(Exception $e) {
			$custom_fields = array();
			$msg = $e->getMessage();
		}

		$ret = array();

		ob_start();

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$obj =& $this;
		$app->triggerEvent('onHikashopBeforeCheckDB', array(&$this->createTable, &$custom_fields, &$this->structure, &$obj));

		$html = ob_get_clean();
		if(!empty($html))
			$ret[] = $html;


		if(!empty($custom_fields)){
			foreach($custom_fields as $custom_field) {
				if(@$custom_field->field_type == 'customtext')
					continue;
				if(substr($custom_field->field_table, 0, 4) == 'plg.')
					continue;

				switch($custom_field->field_table) {
					case 'contact':
						break;
					case 'item':
						$table = '#__hikashop_cart_product';
						if(!isset($this->structure[$table][$custom_field->field_namekey]))
							$this->structure[$table][$custom_field->field_namekey] = '`'.$custom_field->field_namekey.'` TEXT NULL';
						$table = '#__hikashop_order_product';
						if(!isset($this->structure[$table][$custom_field->field_namekey]))
							$this->structure[$table][$custom_field->field_namekey] = '`'.$custom_field->field_namekey.'` TEXT NULL';
						break;
					default:
						$table = '#__hikashop_'.$custom_field->field_table;
						if(!isset($this->structure[$table][$custom_field->field_namekey]))
							$this->structure[$table][$custom_field->field_namekey] = '`'.$custom_field->field_namekey.'` TEXT NULL';
						break;
				}
			}
		}
		return $ret;
	}

	public function checkdb() {
		$app = JFactory::getApplication();
		$ret = $this->loadStructure();

		$tableName = array_keys($this->structure);
		$structureDB = array();

		foreach($tableName as $oneTableName) {
			$msg = '';
			$fields2 = null;
			try{
				$this->db->setQuery('SHOW COLUMNS FROM ' . $oneTableName);
				$fields2 = $this->db->loadObjectList();
			} catch(Exception $e) {
				$fields2 = null;
				$msg = $e->getMessage();
			}

			$table_name = str_replace('#__', '', $oneTableName);

			if($fields2 == null) {
				if(empty($msg))
					$msg = substr(strip_tags($this->db->getErrorMsg()), 0, 200);

				$ret[] = array(
					'info',
					sprintf('Could not load columns from the table "%s" : %s', $table_name, $msg)
				);

				$msg = '';
				try {
					$this->db->setQuery($this->createTable[$oneTableName]);
					$isError = $this->db->execute();
				} catch(Exception $e) {
					$isError = null;
					$msg = $e->getMessage();
				}

				if($isError == null) {
					if(empty($msg))
						$msg = substr(strip_tags($this->db->getErrorMsg()), 0, 200);
					$ret[] = array(
						'error',
						sprintf('Could not create the table "%s"', $table_name)
					);
					$ret[] = array('error_msg', $msg);
				} else {
					$ret[] = array(
						'success',
						sprintf('Problem solved - table "%s" created', $table_name)
					);

					$fields2 = null;
					try{
						$this->db->setQuery('SHOW COLUMNS FROM ' . $oneTableName);
						$fields2 = $this->db->loadObjectList();
					} catch(Exception $e) {
						$fields2 = null;
						$msg = $e->getMessage();
					}
				}
			}

			if(!empty($fields2)) {
				foreach($fields2 as $oneField) {
					if(empty($structureDB[$oneTableName]))
						$structureDB[$oneTableName] = array();

					$structureDB[$oneTableName][$oneField->Field] = $oneField->Field;
				}
			}
		}

		foreach($tableName as $oneTableName) {
			$t = array();
			if(!empty($structureDB[$oneTableName]))
				$t = $structureDB[$oneTableName];

			if(!empty($this->structure[$oneTableName])) {
				$resultCompare[$oneTableName] = array_diff(array_keys($this->structure[$oneTableName]), $t, array('AUTO_INCREMENT','PRIMARY_KEY'));
			}
			$table_name = str_replace('#__', '', $oneTableName);

			if(empty($resultCompare[$oneTableName])) {
				$ret[] = array(
					'success',
					sprintf('Table "%s" checked', $table_name)
				);
				continue;
			}

			foreach($resultCompare[$oneTableName] as $oneField) {
				if($oneField == 'AUTO_INCREMENT' || $oneField == 'PRIMARY_KEY')
					continue;

				$ret[] = array(
					'info',
					sprintf('Field "%s" missing in %s', $oneField, $table_name)
				);

				$msg = '';

				$query = 'ALTER TABLE ' . $oneTableName . ' ADD ';
				if(strpos($this->structure[$oneTableName][$oneField], $oneField) === false) {
					$query .= '`'.$oneField.'` ';
				}
				$query .= $this->structure[$oneTableName][$oneField];
				try{
					$this->db->setQuery($query);
					$isError = $this->db->execute();
				} catch(Exception $e) {
					$isError = null;
					$msg = $e->getMessage();
				}

				if($isError == null) {
					if(empty($msg))
						$msg = substr(strip_tags($this->db->getErrorMsg()), 0, 200);

					$ret[] = array(
						'error',
						sprintf('Could not add the field "%s" in the table "%s"', $oneField, $table_name)
					);
					$ret[] = array('error_msg', $msg.' for the MySQL query : '.$query);
				} else {
					$ret[] = array(
						'success',
						sprintf('Field "%s" added in the table "%s"', $oneField, $table_name)
					);
				}
			}
		}

		foreach($tableName as $oneTableName) {
			$msg = '';
			$fields2 = null;
			try{
				$this->db->setQuery('SHOW COLUMNS FROM ' . $oneTableName);
				$fields2 = $this->db->loadObjectList();
			} catch(Exception $e) {
				$fields2 = null;
				$msg = $e->getMessage();
			}
			$table_name = str_replace('#__', '', $oneTableName);

			if(empty($fields2))
				continue;

			$primary_keys = array();
			if(isset($this->structure[$oneTableName]['PRIMARY_KEY'])){
				if(strpos($this->structure[$oneTableName]['PRIMARY_KEY'], "`,`") !== false)
					$primary_keys = explode( "`,`", $this->structure[$oneTableName]['PRIMARY_KEY']);
				else
					$primary_keys[] = $this->structure[$oneTableName]['PRIMARY_KEY'];
			}

			$auto_increments = array();
			if(isset($this->structure[$oneTableName]['AUTO_INCREMENT'])){
				$auto_increments = $this->structure[$oneTableName]['AUTO_INCREMENT'];
			}

			$deletePrimary = false;
			foreach($fields2 as $oneField) {
				if(in_array($oneField->Field, $primary_keys) && (empty($oneField->Key) || $oneField->Key != 'PRI')) {
					$deletePrimary = true;
					break;
				}
			}

			if(!empty($deletePrimary)) {
				$query = 'SELECT '. implode(',',$primary_keys).', count(*) AS counter FROM '. $oneTableName .
						' GROUP BY '. implode(',',$primary_keys) .
						' HAVING counter > 1';
				try{
					$this->db->setQuery($query);
					$duplication_primarykeys = $this->db->loadObjectList();
				} catch(Exception $e) {
					$ret[] = array(
						'error',
						$e->getMessage()
					);
					$duplication_primarykeys = false;
				}

				if(is_array($duplication_primarykeys) && !empty($duplication_primarykeys) && count($duplication_primarykeys)) {
					$where = ' WHERE ';
					$where_conditions = array();
					foreach($duplication_primarykeys as $duplication_primarykey){
						$first_duplication = true;
						$where_condition = '';
						foreach($duplication_primarykey as $duplication_key => $duplication_value){
							if($duplication_key == 'counter')
								continue;

							if(!$first_duplication)
								$where_condition .= ' AND ';
							else
								$first_duplication = false;

							$where_condition .= $duplication_key . ' = ' . $duplication_value;
						}
						$where_conditions[] = $where_condition;
					}

					if(!empty($where_conditions)){
						$where .= implode(' OR ',$where_conditions);

						$query = 'DELETE FROM '.$oneTableName . $where.';';
						try{
							$this->db->setQuery($query);
							$result = $this->db->execute();
						} catch(Exception $e) {
							$ret[] = array(
								'error',
								$e->getMessage()
							);
							$result = false;
						}

						if($result) {
							$ret[] = array(
								'info',
								'Element(s) of the table "'.$table_name.'" with the same ID deleted'
							);
						}
					}
				}

				try{
					$query = 'ALTER TABLE '.$oneTableName.' ADD PRIMARY KEY('.implode(',',$primary_keys).')';
					$this->db->setQuery($query);
					$result = $this->db->execute();
				} catch(Exception $e) {
					$ret[] = array(
						'error',
						$table_name.': '.$e->getMessage()
					);
					$ret[] = array(
						'error',
						'<pre>'.$query.'</pre>'
					);
					$result = false;
				}
				if($result){
					$ret[] = array(
						'info',
						'Primary key(s) for the table "'.$table_name.'" fixed'
					);
				}
			}

			foreach($fields2 as $oneField) {
				if(!in_array($oneField->Field, $auto_increments) || $oneField->Extra == 'auto_increment')
					continue;

				try{
					$query = 'ALTER TABLE '.$oneTableName.' MODIFY COLUMN '.$oneField->Field . ' ';

					if(!empty($oneField->Type))
						$query.= $oneField->Type . ' ';

					$query.= 'auto_increment';
					$this->db->setQuery($query);
					$result = $this->db->execute();
				} catch(Exception $e) {
					$ret[] = array(
						'error',
						$table_name.': '.$e->getMessage()
					);
					$ret[] = array(
						'error',
						'<pre>'.$query.'</pre>'
					);
					$result = false;
				}
				if($result){
					$ret[] = array(
						'info',
						'Auto increments for the table "'.$table_name.'" fixed'
					);
				}
			}
		}

		$query = 'SELECT category_id FROM `#__hikashop_category` WHERE category_type = ' . $this->db->Quote('root') . ' AND category_parent_id = 0';
		try {
			$this->db->setQuery($query);
			$root = $this->db->loadResult();
		} catch(Exception $e) {
			$root = 0;
		}
		if(empty($root)) {
			$query = 'INSERT IGNORE INTO `#__hikashop_category` '.
				'(`category_id`, `category_parent_id`, `category_type`, `category_name`, `category_description`, `category_published`, `category_ordering`, `category_left`, `category_right`, `category_depth`, `category_namekey`) VALUES '.
				"(1, 0, 'root', 'ROOT', '', 0, 0, 1, 22, 0, 'root'),".
				"(2, 1, 'product', 'product category', '', 1, 1, 2, 3, 1, 'product'),".
				"(3, 1, 'tax', 'taxation category', '', 1, 2, 4, 7, 1, 'tax')";
			try {
				$this->db->setQuery($query);
				$result = $this->db->execute();
			} catch(Exception $e) {
				$result = -1;
			}

			if($result) {
				$ret[] = array(
					'info',
					'Missing core categories fixed'
				);

				$root = null;
				$categoryClass = hikashop_get('class.category');
				$categoryClass->rebuildTree($root,0,1);
			}
		}

		$query = 'SELECT count(p.product_id) as result FROM `#__hikashop_product` AS p ' .
				' LEFT JOIN `#__hikashop_product_category` AS pc ON p.product_id = pc.product_id ' .
				' WHERE p.product_type = ' . $this->db->Quote('main') . ' AND pc.category_id IS NULL;';
		try {
			$this->db->setQuery($query);
			$result = $this->db->loadResult();
		} catch(Exception $e) {
			$result = -1;
		}

		if($result > 0) {
			$ret[] = array(
				'info',
				sprintf('Found %d product(s) without category', $result)
			);

			$product_category_id = 'product';
			$categoryClass = hikashop_get('class.category');
			$categoryClass->getMainElement($product_category_id);

			$query = 'INSERT INTO `#__hikashop_product_category` (category_id, product_id, ordering) ' .
					' SELECT '.$product_category_id.', p.product_id, 1 FROM `#__hikashop_product` AS p ' .
					' LEFT JOIN `#__hikashop_product_category` AS pc ON p.product_id = pc.product_id ' .
					' WHERE p.product_type = ' . $this->db->Quote('main') . ' AND pc.category_id IS NULL;';

			$msg = '';
			try {
				$this->db->setQuery($query);
				$isError = $this->db->execute();
			} catch(Exception $e) {
				$isError = null;
				$msg = $e->getMessage();
			}

			if($isError == null) {
				if(empty($msg))
					$msg = substr(strip_tags($this->db->getErrorMsg()), 0, 200);

				$ret[] = array(
					'error',
					'Could not retrieve the missing products'
				);
				$ret[] = array('error_msg', $msg);
			} else {
				$ret[] = array(
					'success',
					sprintf('Add %d product(s) in the main product category', $result)
				);
			}
		} else if($result < 0) {
			$ret[] = array(
				'error',
				'Could not check for missing products'
			);
		} else {
			$ret[] = array(
				'success',
				'Product categories checked'
			);
		}

		$query = 'UPDATE `#__hikashop_product` set product_parent_id=0 WHERE product_type=\'main\';';
		try {
			$this->db->setQuery($query);
			$result = $this->db->execute();
		} catch(Exception $e) {
			$ret[] = array(
				'error',
				$e->getMessage()
			);
		}

		$query = 'SELECT characteristic_id FROM `#__hikashop_characteristic`;';
		try {
			$this->db->setQuery($query);
			$characteristic_ids = $this->db->loadColumn();

			$where = '';
			if(is_array($characteristic_ids) && count($characteristic_ids)){
				$where = ' WHERE variant_characteristic_id NOT IN('.implode(',',$characteristic_ids).')';
			}
			$query = 'DELETE FROM `#__hikashop_variant`'.$where.';';
			$this->db->setQuery($query);
			$result = $this->db->execute();
			if($result){
				$ret[] = array(
					'success',
					'Variants orphan links cleaned'
				);
			}
		} catch(Exception $e) {
			$ret[] = array(
				'error',
				$e->getMessage()
			);
		}

		$query = 'SELECT COUNT(*) FROM `#__hikashop_orderstatus`';
		try{
			$this->db->setQuery($query);
			$result = $this->db->loadResult();

			if(empty($result)) {
				$query = 'INSERT IGNORE INTO `#__hikashop_orderstatus` (orderstatus_name, orderstatus_description, orderstatus_published, orderstatus_ordering, orderstatus_namekey) '.
						' SELECT category_name, category_description, category_published, category_ordering, category_namekey FROM `#__hikashop_category` AS c '.
						' WHERE c.category_type = \'status\' AND c.category_depth > 1';
				$this->db->setQuery($query);
				$result = $this->db->execute();

				if($result){
					$ret[] = array(
						'success',
						'Order statuses imported'
					);
				}
			}
		} catch(Exception $e) {
			$ret[] = array(
				'error',
				$e->getMessage()
			);
		}

		$query = 'SELECT DISTINCT order_status COLLATE utf8_bin FROM `#__hikashop_order` WHERE order_type = \'sale\'';
		try{
			$this->db->setQuery($query);
			$statuses_in_orders = $this->db->loadColumn();

			$query = 'SELECT * FROM `#__hikashop_orderstatus`';
			$this->db->setQuery($query);
			$order_statuses = $this->db->loadObjectList();
		} catch(Exception $e) {
		}
		if(!empty($statuses_in_orders) && !empty($order_statuses)) {
			$moves = array();
			$invalids = array();
			foreach($statuses_in_orders as $status_in_orders) {
				$f = false;
				foreach($order_statuses as $order_status) {
					if($order_status->orderstatus_namekey == $status_in_orders) {
						$f = true;
						break;
					}
					if($order_status->orderstatus_name == $status_in_orders) {
						$f = $order_status->orderstatus_namekey;
					}
				}
				if($f === false) {
					$invalids[] = $status_in_orders;
				} elseif($f !== true) {
					$moves[$status_in_orders] = $f;
				}
			}
			foreach($moves as $old => $new) {
				try{
					$query = 'UPDATE `#__hikashop_order` SET order_status = ' . $this->db->Quote($new).' WHERE order_status = '.$this->db->Quote($old);
					$this->db->setQuery($query);
					$this->db->execute();
					$ret[] = array(
						'warning',
						'Orders with order statuses `'.$old.'` changed to `'.$new.'`'
					);
				} catch(Exception $e) {
					$ret[] = array(
						'error',
						$e->getMessage()
					);
				}
			}
			foreach($invalids as $invalid) {
				if(empty($invalid))
					continue;
				$ret[] = array(
					'error',
					'The order status `'.$invalid.'` is not found but orders with that status exist'
				);
			}
		}

		$query = 'INSERT IGNORE INTO `#__hikashop_user` (`user_email`,`user_cms_id`,`user_created`) SELECT `email`, `id`,'.time().' FROM `#__users`';
		$this->db->setQuery($query);
		try{
			$result = $this->db->execute();
			if($result){
				$ret[] = array(
					'success',
					'Joomla users synchronized'
				);
			}
		} catch(Exception $e) {
			$ret[] = array(
				'error',
				$e->getMessage()
			);
		}

		$query = 'UPDATE `#__hikashop_user` AS hku LEFT JOIN `#__users` AS ju ON hku.`user_email`=ju.`email` SET hku.`user_cms_id`=ju.`id` WHERE hku.`user_cms_id`!=ju.`id`';
		$this->db->setQuery($query);
		try{
			$result = $this->db->execute();
			if($result){
				$ret[] = array(
					'success',
					'User email addresses synchronized'
				);
			}
		} catch(Exception $e) {
			$ret[] = array(
				'error',
				$e->getMessage()
			);
		}

		$app->triggerEvent('onHikashopAfterCheckDB', array(&$ret));

		$config = hikashop_config();
		$cfgVersion = $config->get( 'version');
		$manifestVersion  =  $this->getVersion_NumberOnly();
		if ( version_compare( $manifestVersion, $cfgVersion) > 0) {
			$query = "UPDATE `#__hikashop_config` SET `config_value` = ".$this->db->Quote($manifestVersion)." WHERE config_namekey = 'version'";
			$this->db->setQuery($query);
			$this->db->execute();
		}

		self::$check_results = $ret;
		return $ret;
	}

	public function getVersion()
	{
		static $instance;

		if ( isset( $instance)) { return $instance; }

		jimport('joomla.application.helper');
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$version = "unknown";

		$folder  = dirname(dirname(__FILE__));
		$pattern = '^' . substr( basename( dirname(dirname(__FILE__)),'.php'), 4) . '.*' . '\.xml$';
		$xmlFilesInDir = JFolder::files( $folder, $pattern);
		if ( !empty( $xmlFilesInDir)) {
			foreach ($xmlFilesInDir as $xmlfile) {
				if ($data = JInstaller::parseXMLInstallFile($folder.DS.$xmlfile)) {
					if ( isset( $found_version)) {
						if ( version_compare( $data['version'], $found_version) >= 0) {
							$found_version = $data['version'];
						}
					}
					else {
						$found_version = $data['version'];
					}
				}
			}
			if ( !empty( $found_version)) {
				$version = $found_version;
			}
		}
		else {
			$filename = dirname(dirname(__FILE__)) .DS. substr( basename( dirname(dirname(__FILE__)),'.php'), 4).'.xml';
			if (file_exists($filename) && $data = JInstaller::parseXMLInstallFile($filename)) {
				if (isset($data['version']) && !empty($data['version'])) {
					$version = $data['version'];
				}
			}
		}

		$instance = $version;
		return $instance;
	}

	public function getVersion_NumberOnly($verString = null) {
		 if(empty($verString)) {
			$verString = $this->getVersion();
		 }

		 if ( preg_match( '#[A-Za-z0-9\.\s]+#i', $verString, $match)) {
				$result = $match[0];
		 }
		 else {
				$parts = explode( '-', $verString);
				$result = $parts[0];
		 }

		 $result = str_replace( ' ', '.', trim( $result));
		 return $result;
	}

	public function getCheckResults() {
		return self::$check_results;
	}


	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$fullLoad = false;
		$displayFormat = !empty($options['displayFormat']) ? $options['displayFormat'] : @$typeConfig['displayFormat'];

		$start = (int)@$options['start']; // TODO
		$limit = (int)@$options['limit'];
		$page = (int)@$options['page'];
		if($limit <= 0)
			$limit = 50;

		$table = @$options['table'];

		$db = JFactory::getDBO();
		if(!HIKASHOP_J30){
			$columnsTable = $db->getTableFields(hikashop_table($table));
			$columnsArray = reset($columnsTable);
		} else {
			$columnsArray = $db->getTableColumns(hikashop_table($table));
		}

		ksort($columnsArray);

		if(!empty($search)) {
			$results = array();
			foreach($columnsArray as $k => $t) {
				if(strpos($k, $search)!==false)
					$results[$k] = $t;
			}
			$columnsArray = $results;
		}

		foreach($columnsArray as $k => $t) {
			$obj = new stdClass();
			$obj->column_name = $k;
			$obj->column_type = $t;
			$ret[0][$k] = $obj;
		}

		if(count($ret[0]) < $limit)
			$fullLoad = true;

		if(!empty($value)) {
			if($mode == hikashopNameboxType::NAMEBOX_SINGLE && isset($ret[0][$value])) {
				$ret[1][$value] = $ret[0][$value];
			} elseif($mode == hikashopNameboxType::NAMEBOX_MULTIPLE && is_array($value)) {
				foreach($value as $v) {
					if(isset($ret[0][$v])) {
						$ret[1][$v] = $ret[0][$v];
					}
				}
			}
		}
		return $ret;
	}
}
