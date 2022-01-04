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

defined( '_JEXEC' ) or die( 'Restricted access' );


class JFormFieldSmartCombineValues extends JFormFieldList
{
	protected $type = 'SmartCombineValues';

	protected function getOptions()
	{
		$aOptions = array();

		$aValueArray = $this->value;

		if ( ! empty( $aValueArray ) )
		{
			foreach ( $aValueArray as $sValue )
			{
				$tmp           = new stdClass();
				$tmp->value    = $sValue;
				$tmp->text     = '';
				$tmp->disable  = '';
				$tmp->class    = '';
				$tmp->selected = true;
				$tmp->checked  = '';
				$tmp->onclick  = '';
				$tmp->onchange = '';

				$aOptions[] = $tmp;
			}
		}

		return $aOptions;
	}
}