<?php
/**
 * @version		3.0
 * @package		Joomla
 * @subpackage	Falang
 * @author      Stéphane Bouey
 * @copyright	Copyright (C) 2012 Faboba
 * @license		GNU/GPL, see LICENSE.php
 */

if (FALANG_J30) {
	class LegacyView extends JViewLegacy {}
} else {
	jimport( 'joomla.application.component.view' );
	class LegacyView extends JView {}
}