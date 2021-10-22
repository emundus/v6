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

namespace JchOptimize\Core\Html;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

use CodeAlfa\RegexTokenizer\Html;
use JchOptimize\Core\Html\Callbacks\CallbackBase;

class Parser
{
	use Html;

	/** @var string        Regex criteria of search */
	protected $sCriteria = '';

	/** @var array         Array of regex of excludes in search */
	protected $aExcludes = array();

	/** @var array          Array of ElementObjects containing criteria for elements to search for */
	protected $aElementObjects = array();


	public function __construct()
	{
	}

	//language=RegExp
	public static function HTML_BODY_ELEMENT()
	{
		return self::HTML_HEAD_ELEMENT() . '\K.*+$';
	}

	//language=RegExp
	public static function HTML_HEAD_ELEMENT()
	{
		$aExcludes = array( self::HTML_ELEMENT( 'script' ), self::HTML_COMMENT() );

		return '<head\b' . self::parseHtml( $aExcludes ) . '</head\b\s*+>';
	}

	//language=RegExp
	protected static function parseHtml( $aExcludes = array() )
	{
		$aExcludes[] = '<';
		$aExcludes   = '(?:' . implode( '|', $aExcludes ) . ')?';

		return '(?>[^<]*+' . $aExcludes . ')*?[^<]*+';
	}

	//language=RegExp
	public static function HTML_END_HEAD_TAG()
	{
		return '</head\s*+>(?=' . self::parseHtml( array( self::HTML_COMMENT() ) ) . '(?:<body|$))';
	}

	//language=RegExp
	public static function HTML_END_BODY_TAG()
	{
		return '</body\s*+>(?=' . self::parseHtml( array( self::HTML_COMMENT() ) ) . '(?:</html\s*+>|$))';
	}

	public function addElementObject( ElementObject $oElementObject )
	{
		$this->aElementObjects[] = $oElementObject;
	}

	public function addExclude( $sExclude )
	{
		$this->aExcludes[] = $sExclude;
	}

	public function processMatchesWithCallback( $sHtml, CallbackBase $oCallbackObject )
	{
		$sRegex = $this->getHtmlSearchRegex();
		$oCallbackObject->sRegex = $sRegex;

		$sProcessedHtml = preg_replace_callback( '#' . $sRegex . '#six', array(
			$oCallbackObject,
			'processMatches'
		), $sHtml );

		self::throwExceptionOnPregError( 'JchOptimize\Core\Exception' );

		return $sProcessedHtml;
	}

	protected function getHtmlSearchRegex()
	{
		$this->setCriteria();
		//language=RegExp
		$regex = self::parseHtml( $this->getExcludes() ) . '\K(?:' . $this->getCriteria() . '|$)';

		return $regex;
	}

	//language=RegExp
	protected function setCriteria( $bBranchReset = true )
	{
		$aCriteria = array();

		/** @var ElementObject $oElement */
		foreach ( $this->aElementObjects as $oElement )
		{
			$sRegex = '<';

			$aNames = implode( '|', $oElement->getNamesArray() );

			$sRegex .= '(' . $aNames . ')\b\s*+';

			$sRegex .= $this->compileCriteria( $oElement );

			$aCaptureAttributes = $oElement->getCaptureAttributesArray();

			if ( ! empty ( $aCaptureAttributes ) )
			{
				$mValueCriteria = $oElement->getValueCriteriaRegex();

				if ( is_string( $mValueCriteria ) )
				{
					$aValueCriteria = array( '.' => $mValueCriteria );
				}
				else
				{
					$aValueCriteria = $mValueCriteria;
				}

				foreach ( $aCaptureAttributes as $sCaptureAttribute )
				{

					foreach ( $aValueCriteria as $sRegexKey => $sValueCriteria )
					{
						if ( $sValueCriteria != '' && preg_match( '#' . $sRegexKey . '#i', $sCaptureAttribute ) )
						{
							//If criteria is specified for attribute it must match
							$sRegex .= '(?=' . $this->parseAttributes() .
							           '(' . self::HTML_ATTRIBUTE_CP( $sCaptureAttribute, true, true, $sValueCriteria ) . '))';
						}
						else
						{
							//If no criteria specified matching is optional
							$sRegex .= '(?=(?:' . $this->parseAttributes() .
							           '(' . self::HTML_ATTRIBUTE_CP( $sCaptureAttribute, true, true ) . '))?)';
						}
					}
				}
			}

			$sRegex .= $this->parseAttributes();
			$sRegex .= '/?>';

			if ( ! $oElement->bSelfClosing )
			{
				if ( $oElement->bCaptureContent )
				{
					$sRegex .= $oElement->getValueCriteriaRegex() . '(' . self::parseHtml() . ')';
				}
				else
				{
					$sRegex .= self::parseHtml();
				}

				$sRegex .= '</(?:' . $aNames . ')\s*+>';
			}

			$aCriteria[] = $sRegex;
		}

		$sCriteria = implode( '|', $aCriteria );

		if ( $bBranchReset )
		{
			$this->sCriteria = '(?|' . $sCriteria . ')';
		}
		else
		{
			$this->sCriteria = $sCriteria;
		}
	}

	//language=RegExp
	protected function compileCriteria( ElementObject $oElement )
	{
		$sCriteria = '';

		$aAttrNegCriteria = $oElement->getNegAttrCriteriaArray();

		if ( ! empty( $aAttrNegCriteria ) )
		{
			foreach ( $aAttrNegCriteria as $sAttrNegCriteria )
			{
				$sCriteria .= $this->processNegCriteria( $sAttrNegCriteria );
			}
		}

		$aAttrPosCriteria = $oElement->getPosAttrCriteriaArray();

		if ( ! empty( $aAttrPosCriteria ) )
		{
			foreach ( $aAttrPosCriteria as $sAttrPosCriteria )
			{
				$sCriteria .= $this->processPosCriteria( $sAttrPosCriteria );
			}
		}

		if ( $oElement->bNegateCriteria )
		{
			$sCriteria = '(?!' . $sCriteria . ')';
		}

		return $sCriteria;
	}

	//language=RegExp
	protected function processNegCriteria( $sCriteria )
	{
		return '(?!' . $this->processCriteria( $sCriteria ) . ')';
	}

	protected function processCriteria( $sCriteria )
	{
		return $this->parseAttributes() . '(?:' . str_replace( '==', '\s*+=\s*+', $sCriteria ) . ')';
	}

	//language=RegExp
	protected function parseAttributes()
	{
		return '(?>' . self::HTML_ATTRIBUTE_CP() . '\s*+)*?';
	}

	//language=RegExp
	protected function processPosCriteria( $sCriteria )
	{
		return '(?=' . $this->processCriteria( $sCriteria ) . ')';
	}

	protected function getExcludes()
	{
		return $this->aExcludes;
	}

	protected function getCriteria()
	{
		return $this->sCriteria;
	}

	public function findMatches( $sHtml, $iFlags = PREG_PATTERN_ORDER )
	{
		preg_match_all( '#' . $this->getHtmlSearchRegex() . '#six', $sHtml, $aMatches, $iFlags );

		self::throwExceptionOnPregError( 'JchOptimize\Core\Exception' );

		//Last array will always be an empty string so let's remove that
		if ( $iFlags == PREG_PATTERN_ORDER )
		{
			return array_map( function ( $a ) {
				return array_slice( $a, 0, - 1 );
			}, $aMatches );
		}
		elseif ( $iFlags == PREG_SET_ORDER )
		{
			array_pop( $aMatches );

			return $aMatches;
		}
		else
		{
			return $aMatches;
		}
	}

	public function getElementWithCriteria()
	{
		$this->setCriteria( false );

		return $this->sCriteria;
	}
}