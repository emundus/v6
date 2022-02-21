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

class CssSearchObject
{
	protected $aCssRuleCriteria = array();

	protected $aCssAtRuleCriteria = array();

	protected $aCssNestedRuleNames = array();

	protected $aCssCustomRule = array();

	protected $bIsCssCommentSet = false;


	public function setCssRuleCriteria( $sCriteria )
	{
		$this->aCssRuleCriteria[] = $sCriteria;
	}

	public function getCssRuleCriteria()
	{
		return $this->aCssRuleCriteria;
	}

	public function setCssAtRuleCriteria( $sCriteria )
	{
		$this->aCssAtRuleCriteria[] = $sCriteria;
	}

	public function getCssAtRuleCriteria()
	{
		return $this->aCssAtRuleCriteria;
	}

	public function setCssNestedRuleName( $sNestedRule, $bRecurse = false, $bEmpty = false )
	{
		$this->aCssNestedRuleNames[] = array(
			'name'        => $sNestedRule,
			'recurse'     => $bRecurse,
			'empty-value' => $bEmpty
		);
	}

	public function getCssNestedRuleNames()
	{
		return $this->aCssNestedRuleNames;
	}

	public function setCssCustomRule( $sCssCustomRule )
	{
		$this->aCssCustomRule[] = $sCssCustomRule;
	}

	public function getCssCustomRule()
	{
		return $this->aCssCustomRule;
	}

	public function setCssComment()
	{
		$this->bIsCssCommentSet = true;
	}

	public function getCssComment()
	{
		if ($this->bIsCssCommentSet)
		{
			return Parser::BLOCK_COMMENT();
		}

		return false;
	}
}
