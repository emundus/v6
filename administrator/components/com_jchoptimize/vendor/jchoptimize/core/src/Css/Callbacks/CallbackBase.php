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

namespace JchOptimize\Core\Css\Callbacks;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );


abstract class CallbackBase
{
	public $oParams;

	protected $aUrl;

	public function __construct( $oParams, $aUrl )
	{
		$this->oParams = $oParams;
		$this->aUrl    = $aUrl;
	}

	abstract function processMatches( $aMatches, $sContext );
}
