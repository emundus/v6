<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Html\Callbacks;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

use JchOptimize\Core\Html\Processor;
use JchOptimize\Platform\Settings;

abstract class CallbackBase
{
	/** @var Settings        Plugin parameters */
	public $oParams;
	/** @var $sRegex        Regex used to process HTML */
	public $sRegex;
	/** @var array          Array of excludes parameters */
	protected $aExcludes;
	/** @var Processor      Processor object */
	protected $oProcessor;

	public function __construct( $oProcessor )
	{
		$this->oProcessor = $oProcessor;
		$this->oParams    = $oProcessor->oParams;
	}

	abstract function processMatches( $aMatches );

	protected function getMValue( $sValue )
	{
		return ! empty( $sValue ) ? $sValue : false;
	}
}
