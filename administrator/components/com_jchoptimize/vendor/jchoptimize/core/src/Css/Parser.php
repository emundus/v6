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

namespace JchOptimize\Core\Css;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

use CodeAlfa\RegexTokenizer\Css;

class Parser
{
	use Css;

	protected $aExcludes = array();

	/** @var CssSearchObject */
	protected $oCssSearchObject;

	protected $bBranchReset = true;

	protected $sParseTerm = '\s*+';

	public function __construct()
	{
		$this->aExcludes = array(
			self::BLOCK_COMMENT(),
			self::LINE_COMMENT(),
			self::CSS_RULE_CP(),
			self::CSS_AT_RULES(),
			self::CSS_NESTED_AT_RULES_CP(),
			//Custom exclude
			'\|"(?>[^"{}]*+"?)*?[^"{}]*+"\|',
			self::CSS_INVALID_CSS()
		);
	}

	//language=RegExp
	public static function CSS_RULE_CP( $bCaptureValue = false, $sCriteria = '' )
	{
		$sCssRule = '<<(?<=^|[{}/\s;|])[^@/\s{}]' . self::parseNoStrings() . '>>\{' . $sCriteria . '<<' . self::parse() . '>>\}';

		return self::prepare( $sCssRule, $bCaptureValue );
	}

	//language=RegExp
	protected static function parseNoStrings()
	{
		return '(?>(?:[^{}/]++|/)(?>' . self::BLOCK_COMMENT() . ')?)*?';
	}

	//language=RegExp
	protected static function parse( $sInclude = '', $bNoEmpty = false )
	{
		$sRepeat = $bNoEmpty ? '+' : '*';

		return '(?>(?:[^{}"\'/' . $sInclude . ']++|/)(?>' . self::BLOCK_COMMENT() . '|' . self::STRING_CP() . ')?)' . $sRepeat . '?';
	}

	//language=RegExp
	public static function CSS_AT_RULES()
	{
		return '@\w++\b\s++(?:' . self::CSS_IDENT() . ')?' . '(?:' . self::STRING_CP() . '|' . self::CSS_URL_CP() . ')[^;]*+;';
	}

	//language=RegExp
	public static function CSS_NESTED_AT_RULES_CP( $aAtRules = array(), $bCV = false, $bEmpty = false )
	{
		$sAtRules = ! empty( $aAtRules ) ? '(?>' . implode( '|', $aAtRules ) . ')' : '';

		$iN     = $bCV ? 2 : 1;
		$sValue = $bEmpty ? '\s*+' : '(?>' . self::parse( '', true ) . '|(?-' . $iN . '))*+';

		$sAtRules = '<<@(?:-[^-]++-)??' . $sAtRules . '[^{};]*+>>(\{<<' . $sValue . '>>\})';

		return self::prepare( $sAtRules, $bCV );
	}

	/**
	 * @param   string  $sInclude
	 * @param   false   $bNoEmpty
	 *
	 * @return string
	 *
	 */
	//language=RegExp
	public static function CSS_INVALID_CSS()
	{
		return '[^;}@\r\n]*+[;}@\r\n]';
	}

	//language=RegExp
	public static function CSS_AT_IMPORT_CP( $bCV = false )
	{
		$sAtImport = '@import\s++<<<' . self::STRING_CP( $bCV ) . '|' . self::CSS_URL_CP( $bCV ) . '>>><<[^;]*+>>;';

		return self::prepare( $sAtImport, $bCV );
	}

	//language=RegExp
	public static function CSS_AT_FONT_FACE_CP( $sCaptureValue = false )
	{
		return self::CSS_NESTED_AT_RULES_CP( array( 'font-face' ), $sCaptureValue );
	}

	//language=RegExp
	public static function CSS_AT_MEDIA_CP( $sCaptureValue = false )
	{
		return self::CSS_NESTED_AT_RULES_CP( array( 'media' ), $sCaptureValue );
	}

	//language=RegExp
	public static function CSS_AT_CHARSET_CP( $sCaptureValue = false )
	{
		return '@charset\s++' . self::STRING_CP( $sCaptureValue ) . '[^;]*+;';
	}

	//language=RegExp
	public static function CSS_AT_NAMESPACE()
	{
		return '@namespace\s++' . '(?:' . self::CSS_IDENT() . ')?' . '(?:' . self::STRING_CP() . '|' . self::CSS_URL_CP() . ')[^;]*+;';
	}

	//language=RegExp
	public static function CSS_STATEMENTS()
	{
		return '(?:' . self::CSS_RULE_CP() . '|' . self::CSS_AT_RULES() . '|' . self::CSS_NESTED_AT_RULES_CP() . ')';
	}

	//language=RegExp
	public static function CSS_MEDIA_TYPES()
	{
		return '(?>all|screen|print|speech|aural|tv|tty|projection|handheld|braille|embossed)';
	}

	//language=RegExp
	protected static function _parseCss( $sInclude = '', $bNoEmpty = false )
	{
		return self::parse( $sInclude, $bNoEmpty );
	}

	public function disableBranchReset()
	{
		$this->bBranchReset = false;
	}

	public function setExcludesArray( $aExcludes )
	{
		$this->aExcludes = $aExcludes;
	}

	public function processMatchesWithCallback( $sCss, $oCallback, $sContext = 'global' )
	{
		$sRegex = $this->getCssSearchRegex();

		$sProcessedCss = preg_replace_callback( '#' . $sRegex . '#six', function ( $aMatches ) use ( $oCallback, $sContext ) {

			if ( empty( trim( $aMatches[0] ) ) )
			{
				return $aMatches[0];
			}

			if ( substr( $aMatches[0], 0, 1 ) == '@' )
			{
				$sContext = $this->getContext( $aMatches[0] );

				foreach ( $this->oCssSearchObject->getCssNestedRuleNames() as $aAtRule )
				{
					if ( $aAtRule['name'] == $sContext )
					{
						if ( $aAtRule['recurse'] )
						{
							return $aMatches[2] . '{' . $this->processMatchesWithCallback( $aMatches[4], $oCallback, $sContext ) . '}';
						}
						else
						{
							return $oCallback->processMatches( $aMatches, $sContext );
						}
					}
				}
			}

			return $oCallback->processMatches( $aMatches, $sContext );

		}, $sCss );

		self::throwExceptionOnPregError( 'JchOptimize\Core\Exception' );

		return $sProcessedCss;
	}

	protected function getCssSearchRegex()
	{
		$sRegex = $this->parseCss( $this->getExcludes() ) . '\K(?:' . $this->getCriteria() . '|$)';

		return $sRegex;
	}

	protected function parseCSS( $aExcludes = array() )
	{
		if ( ! empty( $aExcludes ) )
		{
			$aExcludes = '(?>' . implode( '|', $aExcludes ) . ')?';
		}
		else
		{
			$aExcludes = '';
		}

		return '(?>' . $this->sParseTerm . $aExcludes . ')*?' . $this->sParseTerm;
	}

	protected function getExcludes()
	{
		return $this->aExcludes;
	}

	protected function getCriteria()
	{
		/** @var CssSearchObject $oObj */
		$oObj = $this->oCssSearchObject;

		$aCriteria = array();

		//We need to add Nested Rules criteria first to avoid trouble with recursion and branch capture reset
		$aNestedRules = $oObj->getCssNestedRuleNames();

		if ( ! empty( $aNestedRules ) )
		{
			if ( count( $aNestedRules ) == 1 && $aNestedRules[0]['empty-value'] == true )
			{
				$aCriteria[] = self::CSS_NESTED_AT_RULES_CP( array( $aNestedRules[0]['name'] ), false, true );
			}
			elseif ( count( $aNestedRules ) == 1 && $aNestedRules[0]['name'] == '*' )
			{
				$aCriteria[] = self::CSS_NESTED_AT_RULES_CP( array() );
			}
			else
			{
				$aCriteria[] = self::CSS_NESTED_AT_RULES_CP( array_column( $aNestedRules, 'name' ), true );
			}
		}

		$aAtRules = $oObj->getCssAtRuleCriteria();

		if ( ! empty( $aAtRules ) )
		{
			$aCriteria[] = '(' . implode( '|', $aAtRules ) . ')';
		}

		$aCssRules = $oObj->getCssRuleCriteria();

		if ( ! empty( $aCssRules ) )
		{
			if ( count( $aCssRules ) == 1 && $aCssRules[0] == '.' )
			{
				$aCriteria[] = self::CSS_RULE_CP( true );
			}
			elseif ( count( $aCssRules ) == 1 && $aCssRules[0] == '*' )
			{
				//Array of nested rules we don't want to recurse in
				$aNestedRules = array(
					'font-face',
					'keyframes',
					'page',
					'font-feature-values',
					'counter-style',
					'viewport',
					'property'
				);
				$aCriteria[]  = '(?:(?:' . self::CSS_RULE_CP() . '\s*+|' . self::BLOCK_COMMENT() . '\s*+|' . self::CSS_NESTED_AT_RULES_CP( $aNestedRules ) . '\s*+)++)';
			}
			else
			{
				$sStr = self::getParseStr( $aCssRules );

				$sRulesCriteria = '(?=(?>[' . $sStr . ']?[^{}' . $sStr . ']*+)*?(' . implode( '|', $aCssRules ) . '))';

				$aCriteria[] = self::CSS_RULE_CP( true, $sRulesCriteria );
			}
		}

		$aCssCustomRules = $oObj->getCssCustomRule();

		if ( ! empty( $aCssCustomRules ) )
		{
			$aCriteria[] = '(' . implode( '|', $aCssCustomRules ) . ')';
		}

		return ( $this->bBranchReset ? '(?|' : '(?:' ) . implode( '|', $aCriteria ) . ')';
	}

	//language=RegExp
	protected static function getParseStr( $aExcludes )
	{
		$aStr = array();

		foreach ( $aExcludes as $sExclude )
		{
			$sSubStr = substr( $sExclude, 0, 1 );

			if ( ! in_array( $sSubStr, $aStr ) )
			{
				$aStr[] = $sSubStr;
			}
		}

		return implode( '', $aStr );
	}

	protected function getContext( $sMatch )
	{
		preg_match( '#^@(?:-[^-]+-)?([^\s{(]++)#i', $sMatch, $aMatches );

		return ! empty( $aMatches[1] ) ? strtolower( $aMatches[1] ) : 'global';
	}

	//language=RegExp
	public function replaceMatches( $sCss, $sReplace )
	{
		$sProcessedCss = preg_replace( '#' . $this->getCssSearchRegex() . '#i', $sReplace, $sCss );

		self::throwExceptionOnPregError( 'JchOptimize\Core\Exception' );

		return $sProcessedCss;
	}

	public function setCssSearchObject( CssSearchObject $oCssSearchObject )
	{
		$this->oCssSearchObject = $oCssSearchObject;
	}

	//language=RegExp
	public function setExcludes( $aExcludes )
	{
		$this->aExcludes = $aExcludes;
	}

	public function setParseTerm( $sParseTerm )
	{
		$this->sParseTerm = $sParseTerm;
	}
}
