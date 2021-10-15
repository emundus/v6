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

defined( '_JEXEC' ) or die;

use JchOptimize\Platform\Plugin;
use Joomla\CMS\HTML\HTMLHelper;

include_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/autoload.php';
include_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/version.php';
include_once dirname( __FILE__ ) . '/exclude.php';

class JFormFieldExcludecss extends JFormFieldExclude
{

	public $type = 'excludecss';
	public $filetype = 'css';
	public $filegroup = 'file';

	/**
	 *
	 * @return type
	 */
	protected function getInput()
	{
		$this->first_field = true;

		return parent::getInput();
	}

}
