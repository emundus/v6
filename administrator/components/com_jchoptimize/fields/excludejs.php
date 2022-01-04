<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die;

include_once dirname(__FILE__) . '/exclude.php';

class JFormFieldExcludejs extends JFormFieldExclude
{

        public $type = 'excludejs';
	public $filetype = 'js';
	public $filegroup = 'file';

}
