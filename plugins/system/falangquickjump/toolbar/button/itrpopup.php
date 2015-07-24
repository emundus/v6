<?php
/**
 * SYNC WITH ITRLIB
 *
 * @package Falang
 * @link http://joomla.itronic.at
 * @copyright	Copyright (C) 2011 ITronic Harald Leithner. All rights reserved.
 * @license GNU General Public License v3
 *
 * This file is part of Falang.
 *
 * jDiction is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3 of the License.
 *
 * jDiction is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with jDiction.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('JPATH_PLATFORM') or die;

class JToolbarButtonItrPopup extends JToolbarButton {
	/**
	 * Button type
	 *
	 * @var    string
	 */
	protected $_name = 'ItrPopup';

  /**
   * @var    array  Array containing information for loaded files
   * @since  3.0
   */
  protected static $loaded = array();


	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string   $type     Unused string, formerly button type.
	 * @param   string   $name     Modal name, used to generate element ID
	 * @param   string   $text     The link text
	 * @param   string   $url      URL for popup
	 * @param   integer  $width    Width of popup
	 * @param   integer  $height   Height of popup
	 * @param   string   $onClose  JavaScript for the onClose event.
	 * @param   string   $title    The title text
     * @param   string   $flag     The flag to add to object
     * @param   string   $class    The class to add to object
     * @param   string   $publish  The icon publish class
	 *
	 * @return  string  HTML string for the button
	 *
	 * @since   3.0
	 */
	public function fetchButton($type = 'Modal', $name = '', $text = '', $url = '', $width = 640, $height = 'function(){ return $(window).height() - 165; }', $top = 0, $left = 0,
		$onClose = '', $title = '',$flag = '',$class='',$publish='')	{
		// If no $title is set, use the $text element
		if (strlen($title) == 0)
		{
			$title = $text;
		}

		$text = JText::_($text);
		$title = JText::_($title);
		$doTask = $this->_getCommand($url);

		$html = "<button class=\"btn btn-small modal " . $class . "\" data-toggle=\"modal\" data-target=\"#modal-" . $name . "\" id=\"#modal-" . $name . "-btn\"";
        $html .= " style=\"background: url(../media/mod_falang/images/".$flag.".gif) no-repeat center;width:26px;height:24px;display:inline-block\">\n";
        $html .= "<span class=\"".$publish." falang-status\"/>";
        //TODO put text in params
		//$html .= "$text\n";

		$html .= "</button>\n";

		// Build the options array for the modal
		$params = array();
		$params['title']  = $title;
		$params['url']    = $doTask;
		$params['height'] = $height;
		$params['width']  = $width;
        $html .= JHtml::_('bootstrap.renderModal', 'modal-' . $name, $params);

        $html .= "<script>\n";
        $html .="jQuery(\"#toolbar-popup-".$name."\").css('float', 'right');\n";
        $html .="jQuery(\"#modal-".$name." .modal-body\").css('overflow', 'auto');\n";
        $html .="jQuery(\"#modal-".$name." .modal-body\").css('height',function(){ return (jQuery(window).height() - 110); });\n";
        $html .="jQuery(\"#modal-".$name." .modal-body\").css('max-height','none');\n";
        $html .="jQuery(\"#modal-".$name." div.modal.fade.in\").css('top','10px');\n";
        $html .= "</script>\n";

		return $html;
	}

  /**
   * Add javascript support for Bootstrap modals
   *
   * @param   string  $selector  The ID selector for the modal.
   * @param   array   $params    An array of options for the modal.
   *                             Options for the modal can be:
   *                             - backdrop  boolean  Includes a modal-backdrop element.
   *                             - keyboard  boolean  Closes the modal when escape key is pressed.
   *                             - show      boolean  Shows the modal when initialized.
   *                             - remote    string   An optional remote URL to load
   *
   * @return  void
   *
   * @since   3.0
   */
  /**
   * Method to render a Bootstrap modal
   *
   * @param   string  $selector  The ID selector for the modal.
   * @param   array   $params    An array of options for the modal.
   * @param   string  $footer    Optional markup for the modal footer
   *
   * @return  string  HTML markup for a modal
   *
   * @since   3.0
   */
//  public function renderModal($selector = 'modal', $params = array(), $footer = '') {
//    // Ensure the behavior is loaded
//    //$this->modal($selector, $params);
//
//    $html = "<div class=\"modal shadow itrmodal hide fade\" id=\"" . $selector . "\">\n";
//    $html .= "<div class=\"modal-header\">\n";
//    $html .= "<button type=\"button\" class=\"close\" data-dismiss=\"modal\">×</button>\n";
//    $html .= "<h3>" . $params['title'] . "</h3>\n";
//    $html .= "</div>\n";
//    $html .= "<div id=\"" . $selector . "-container\">\n";
//    $html .= "</div>\n";
//    $html .= "</div>\n";
//
//
//    $html .= "<script>";
//    $html .= "jQuery('#" . $selector . "').on('show', function () {\n";
//    $html .= "document.getElementById('" . $selector . "-container').innerHTML = '<div class=\"modal-body\"><iframe class=\"iframe\" src=\"" . $params['url'] . "\" height=\"99%\" width=\"99%\" style=\"border:0\"></iframe></div>" . $footer . "';\n";
//    $html .= "});\n";
//    $html .= "</script>";
//
//    return $html;
//  }

	/**
	 * Get the button id
	 *
	 * @param   string  $type  Button type
	 * @param   string  $name  Button name
	 *
	 * @return  string	Button CSS Id
	 *
	 * @since   3.0
	 */
	public function fetchId($type, $name) {
		return $this->_parent->getName() . '-' . "popup-$name";
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @param   string  $url  URL for popup
	 *
	 * @return  string  JavaScript command string
	 *
	 * @since   3.0
	 */
	private function _getCommand($url) {
		if (substr($url, 0, 4) !== 'http')
		{
			$url = JURI::base() . $url;
		}

		return $url;
	}
}
