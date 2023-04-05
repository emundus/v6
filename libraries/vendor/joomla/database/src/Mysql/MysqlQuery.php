<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Mysql;

use Joomla\Database\Pdo\PdoQuery;
use Joomla\Database\Query\MysqlQueryBuilder;

/**
 * MySQL Query Building Class.
 *
 * @since  1.0
 */
class MysqlQuery extends PdoQuery
{
	use MysqlQueryBuilder;

	/**
	 * The list of zero or null representation of a datetime.
	 *
	 * @var    array
	 * @since  2.0.0
	 */
	protected $nullDatetimeList = ['0000-00-00 00:00:00', '1000-01-01 00:00:00'];
}
