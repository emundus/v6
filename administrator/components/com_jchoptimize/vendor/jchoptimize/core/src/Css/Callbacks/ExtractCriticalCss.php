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


use JchOptimize\Core\Css\Parser;
use JchOptimize\Core\Helper;
use CodeAlfa\RegexTokenizer\Debug\Debug;


class ExtractCriticalCss extends CallbackBase
{
	use Debug;

	public $sHtmlAboveFold;
	public $sFullHtml;
	public $oXPath;
	public $sPostCss = '';
	public $bPostProcessing = false;
	protected $sCriticalCss = '';

	function processMatches( $aMatches, $sContext )
	{
		$this->_debug( $aMatches[0], '', 'beginExtractCriticalCss' );

		if ( $sContext == 'font-face' || $sContext == 'keyframes' )
		{
			if ( ! $this->bPostProcessing )
			{//If we're not processing font-face or keyframes yet let's just save them for later until after we've done getting all the
				// critical css

				$this->sPostCss .= $aMatches[0];

				return '';
			}
			else
			{
				if ( $sContext == 'font-face' )
				{
					preg_match( '#font-family\s*+:\s*+[\'"]?(' . Parser::STRING_VALUE() . '|[^;}]++)[\'"]?#i', $aMatches[0], $aM );

					//Only include fonts in the critical CSS that are being used above the fold
					//@TODO prevent duplication of fonts in critical css
					if ( ! empty( $aM[1] ) && stripos( $this->sCriticalCss, $aM[1] ) !== false /*&& ! in_array( $aM[1], $this->aFonts )*/ )
					{
						//$this->aFonts[] = $aM[1];
						return $aMatches[0];
					}
					else
					{
						return '';
					}
				}

				$sRule = preg_replace( '#@[^\s{]*+\s*+#', '', $aMatches[2] );

				if ( ! empty( $sRule ) && stripos( $this->sCriticalCss, $sRule ) !== false )
				{
					return $aMatches[0];
				}
				else
				{
					return '';
				}
			}
		}

		//We're only interested in global and conditional css
		if ( ! in_array( $sContext, array( 'global', 'media', 'supports', 'document' ) ) )
		{
			return '';
		}

		//we're inside a conditional group rule or global css
		//Let's just add the :root pseudo-selector to the critical css
		if ( preg_match( '#^:root\s*+(?:,|$)#', trim( $aMatches[2] ) ) )
		{
			$this->appendToCriticalCss( $aMatches[0] );

			return $aMatches[0];
		}

		if ( JCH_PRO )
		{
			if ( \JchOptimize\Core\DynamicSelectors::getDynamicSelectors( $this, $aMatches ) )
			{
				return $aMatches[0];
			}
		}

		//remove pseudo-selectors
		$sSelectorGroup = preg_replace( '#::?[a-zA-Z0-9-]++(?:\([^)]++\))?#', '', $aMatches[2] );
		//Split selector groups into individual selector chains
		$aSelectorChains      = array_filter( explode( ',', $sSelectorGroup ) );
		$aFoundSelectorChains = array();

		//Iterate through each selector chain
		foreach ( $aSelectorChains as $sSelectorChain )
		{
			//If Selector chain is already in critical css just go ahead and add this group
			if ( strpos( $this->sCriticalCss, $sSelectorChain ) !== false )
			{
				$this->appendToCriticalCss( $aMatches[0] );

				//Retain matched CSS in combined CSS
				return $aMatches[0];
			}

			//Check CSS selector chain against HTMl above the fold to find a match
			if ( $this->checkCssAgainstHtml( $sSelectorChain, $this->sHtmlAboveFold ) )
			{
				//Match found, add selector chain to array
				$aFoundSelectorChains[] = $sSelectorChain;
			}
		}

		//If no valid selector chain was found in the group then we don't add this selector group to the critical CSS
		if ( empty( $aFoundSelectorChains ) )
		{
			$this->_debug( $sSelectorGroup, '', 'afterSelectorNotFound' );

			//Don't add to critical css
			return '';
		}

		//Group the found selector chains
		$sFoundSelectorGroup = implode( ',', array_unique( $aFoundSelectorChains ) );
		//remove any backslash used for escaping
		//$sFoundSelectorGroup = str_replace('\\', '', $sFoundSelectorGroup);

		$this->_debug( $sFoundSelectorGroup, '', 'afterSelectorFound' );

		//Convert the selector group to Xpath
		$sXPath = $this->convertCss2XPath( $sFoundSelectorGroup );

		$this->_debug( $sXPath, '', 'afterConvertCss2XPath' );

		if ( $sXPath )
		{
			$aXPaths = array_unique( explode( ' | ', str_replace( '\\', '', $sXPath ) ) );

			foreach ( $aXPaths as $sXPathValue )
			{
				$oElement = $this->oXPath->query( $sXPathValue );

//                                if ($oElement === FALSE)
//                                {
//                                        echo $aMatches[1] . "\n";
//                                        echo $sXPath . "\n";
//                                        echo $sXPathValue . "\n";
//                                        echo "\n\n";
//                                }

				//Match found! Add to critical CSS
				if ( $oElement !== false && $oElement->length )
				{
					$this->appendToCriticalCss( $aMatches[0] );

					$this->_debug( $sXPathValue, '', 'afterCriticalCssFound' );

					return $aMatches[0];
				}

				$this->_debug( $sXPathValue, '', 'afterCriticalCssNotFound' );
			}
		}

		//No match found for critical CSS.
		return '';
	}

	public function appendToCriticalCss( $sCss )
	{
		$this->sCriticalCss .= $sCss;
	}

	/**
	 * Do a preliminary simple check to see if a CSS declaration is used by the HTML
	 *
	 * @param   string  $sSelectorChain
	 * @param   string  $sHtml
	 *
	 * @return   boolean   True is all parts of the CSS selector is found in the HTML, false if not
	 */
	protected function checkCssAgainstHtml( $sSelectorChain, $sHtml )
	{
		//Split selector chain into simple selectors
		$aSimpleSelectors = preg_split( '#[^\[ >+]*+(?:\[[^\]]*+\])?\K(?:[ >+]*+|$)#', trim( $sSelectorChain ), -1, PREG_SPLIT_NO_EMPTY );

		//We'll do a quick check first if all parts of each simple selector is found in the HTML
		//Iterate through each simple selector
		foreach ( $aSimpleSelectors as $sSimpleSelector )
		{
			//Match the simple selector into its components
			$sSimpleSelectorRegex = '#([a-z0-9]*)(?:([.\#]((?:[_a-z0-9-]|\\\\[^\r\n\f0-9a-z])+))|(\[((?:[_a-z0-9-]|\\\\[^\r\n\f0-9a-z])+)(?:[~|^$*]?=(?|"([^"\]]*+)"|\'([^\'\]]*+)\'|([^\]]*+)))?\]))*#i';
			if ( preg_match( $sSimpleSelectorRegex, $sSimpleSelector, $aS ) )
			{
				//Elements
				if ( ! empty( $aS[1] ) )
				{
					$sNeedle = '<' . $aS[1];
					//Just include elements that will be generated by the browser
					$aDynamicElements = array( '<tbody' );

					if ( in_array( $sNeedle, $aDynamicElements ) )
					{
						continue;
					}

					if ( ! empty( $sNeedle ) && strpos( $sHtml, $sNeedle ) === false )
					{
						//Element part of selector not found,
						//abort and check next selector chain
						return false;
					}
				}

				//Attribute selectors
				if ( ! empty( $aS[4] ) )
				{
					//If the value of the attribute is set we'll look for that
					//otherwise just look for the attribute
					$sNeedle = ! empty( $aS[6] ) ? $aS[6] : $aS[5];// . '="';

					if ( ! empty( $sNeedle ) && strpos( $sHtml, str_replace( '\\', '', $sNeedle ) ) === false )
					{
						//Attribute part of selector not found,
						//abort and check next selector chain
						return false;
					}
				}

				//Ids or Classes
				if ( ! empty( $aS[2] ) )
				{
					$sNeedle = ' ' . $aS[3] . ' ';

					if ( ! empty( $sNeedle ) && strpos( $sHtml, str_replace( '\\', '', $sNeedle ) ) === false )
					{
						//Id or class part of selector not found,
						//abort and check next selector chain
						return false;
					}
				}

				//we found this Selector so let's remove it from the chain in case we need to check it
				//against the HTML below the fold
				str_replace( $sSimpleSelector, '', $sSelectorChain );
			}

		}
		//If we get to this point then we've found a simple selector that has all parts in the
		//HTML. Let's save this selector chain and refine its search with Xpath.
		return true;
	}

	/**
	 *
	 * @param   string  $sSelector
	 *
	 * @return boolean
	 */
	public function convertCss2XPath( $sSelector )
	{
		$sSelector = preg_replace( '#\s*([>+~,])\s*#', '$1', $sSelector );
		$sSelector = trim( $sSelector );
		$sSelector = preg_replace( '#\s+#', ' ', $sSelector );


		if ( ! $sSelector )
		{
			return false;
		}

		$sSelectorRegex = '#(?!$)'
			. '([>+~, ]?)' //separator
			. '([*a-z0-9]*)' //element
			. '(?:(([.\#])((?:[_a-z0-9-]|\\\\[^\r\n\f0-9a-z])+))(([.\#])((?:[_a-z0-9-]|\\\\[^\r\n\f0-9a-z])+))?|'//class or id
			. '(\[((?:[_a-z0-9-]|\\\\[^\r\n\f0-9a-z])+)(([~|^$*]?=)["\']?([^\]"\']+)["\']?)?\]))*' //attribute
			. '#i';

		return preg_replace_callback( $sSelectorRegex, array( $this, '_tokenizer' ), $sSelector ) . '[1]';
	}

	/**
	 *
	 * @param   array  $aM
	 *
	 * @return string
	 */
	protected function _tokenizer( $aM )
	{
		$sXPath = '';

		switch ( $aM[1] )
		{
			case '>':
				$sXPath .= '/';

				break;
			case '+':
				$sXPath .= '/following-sibling::*';

				break;
			case '~':
				$sXPath .= '/following-sibling::';

				break;
			case ',':
				$sXPath .= '[1] | descendant-or-self::';

				break;
			case ' ':
				$sXPath .= '/descendant::';

				break;
			default:
				$sXPath .= 'descendant-or-self::';
				break;
		}

		if ( $aM[1] != '+' )
		{
			$sXPath .= $aM[2] == '' ? '*' : $aM[2];
		}

		if ( isset( $aM[3] ) || isset( $aM[9] ) )
		{
			$sXPath .= '[';

			$aPredicates = array();

			if ( isset( $aM[4] ) && $aM[4] == '.' )
			{
				$aPredicates[] = "contains(@class, ' " . $aM[5] . " ')";
			}

			if ( isset( $aM[7] ) && $aM[7] == '.' )
			{
				$aPredicates[] = "contains(@class, ' " . $aM[8] . " ')";
			}

			if ( isset( $aM[4] ) && $aM[4] == '#' )
			{
				$aPredicates[] = "@id = ' " . $aM[5] . " '";
			}

			if ( isset( $aM[7] ) && $aM[7] == '#' )
			{
				$aPredicates[] = "@id = ' " . $aM[8] . " '";
			}

			if ( isset( $aM[9] ) )
			{
				if ( ! isset( $aM[11] ) )
				{
					$aPredicates[] = '@' . $aM[10];
				}
				else
				{
					switch ( $aM[12] )
					{
						case '=':
							$aPredicates[] = "@{$aM[10]} = ' {$aM[13]} '";

							break;
						case '|=':
							$aPredicates[] = "(@{$aM[10]} = ' {$aM[13]} ' or "
								. "starts-with(@{$aM[10]}, ' {$aM[13]}'))";
							break;
						case '^=':
							$aPredicates[] = "starts-with(@{$aM[10]}, ' {$aM[13]}')";
							break;
						case '$=':
							$aPredicates[] = "substring(@{$aM[10]}, string-length(@{$aM[10]})-"
								. strlen( $aM[13] ) . ") = '{$aM[13]} '";
							break;
						case '~=':
							$aPredicates[] = "contains(@{$aM[10]}, ' {$aM[13]} ')";
							break;
						case '*=':
							$aPredicates[] = "contains(@{$aM[10]}, '{$aM[13]}')";
							break;
						default:
							break;
					}
				}
			}

			if ( $aM[1] == '+' )
			{
				if ( $aM[2] != '' )
				{
					$aPredicates[] = "(name() = '" . $aM[2] . "')";
				}

				$aPredicates[] = '(position() = 1)';
			}

			$sXPath .= implode( ' and ', $aPredicates );
			$sXPath .= ']';
		}

		return $sXPath;
	}
}