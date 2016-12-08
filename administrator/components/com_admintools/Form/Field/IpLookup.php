<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Form\Field;

use Akeeba\AdminTools\Admin\Helper\Storage;
use FOF30\Form\Field\Text;

defined('_JEXEC') or die;

class IpLookup extends Text
{
	public function getRepeatable()
	{
		$ip      = htmlspecialchars($this->value, ENT_COMPAT);
		$cparams = Storage::getInstance();
		$iplink  = $cparams->getValue('iplookupscheme', 'http') . '://' . $cparams->getValue('iplookup', 'ip-lookup.net/index.php?ip={ip}');

		$link = str_replace('{ip}', $ip, $iplink);

		$html = '<a href="'.$link.'" target="_blank" class="btn btn-mini btn-info"><i class="icon-search icon-white"></i></a>&nbsp;';
		$html .= $ip;

		return $html;
	}
}