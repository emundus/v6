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

use JchOptimize\Core\Admin\Helper;

defined( '_JEXEC' ) or die( 'Restricted access' );


class JFormFieldSmartCombine extends JFormFieldRadio
{
	protected $type = 'SmartCombine';

	protected function getInput()
	{
		if ( ! JCH_PRO )
		{
			return Helper::proOnlyField();
		}
		else
		{
			return '<div id="div-' . $this->fieldname . '">' . parent::getInput() . '<img id="img-' . $this->fieldname . '" src="' . JUri::root() . 'media/com_jchoptimize/images/exclude-loader.gif" style="display: none;"/> <button id="btn-' . $this->fieldname . '" type="button" class="btn btn-sm btn-secondary"  style="display: none;">Reprocess Smart Combine</button>
</div>';
		}
//</div>
//</div>
//<div class="control-group" style="display: none;">
//<div class="control-label"></div>
//<div class="controls">
//<select id="jform_params_pro_smart_combine_values" name="jform[params][pro_smart_combine_values][]" style="display: none;" multiple="multiple" //></select>
//</div>
//</div>
//<div class="control-group" style="display: none;">
//<div class="control-label">
//';
	}
}