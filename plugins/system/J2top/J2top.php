<?php
/**
 * Copyright (C) 2014  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

defined('_JEXEC') or die('Restricted access');

class PlgSystemJ2top extends JPlugin {

    private $document;
    private $backend;
    private $backgroundImage = '';
    private $backgroundImageCss = '';
    private $backgroundImageHover = '';
    private $backgroundImageHoverCss = '';

    public function onBeforeRender() {
        $doc = JFactory::getDocument();
        $doc->addStyleSheet(JURI::root() . "plugins/system/J2top/style/J2top.css");
        $this->document = JFactory::getDocument();
        $this->backend = $this->params->get('backend', '1');

        // only proceed in HTML output
        if ((JFactory::getApplication()->getClientId() == 1 && !$this->backend) || $this->document->getType() !== 'html') {
            return;
        }

        // prepare background images
        if ($this->params->get('useBackgroundImage', '1')) {
            if ($this->params->get('backgroundImage', '') == '') {
                $this->backgroundImage = JURI::root() . 'media/J2top/images/arrow.gif';
            } else {
                $this->backgroundImage = JURI::root() . 'images/' . $this->params->get('backgroundImage');
            }
            $this->backgroundImageCss = 'background: url(' . $this->backgroundImage . ') no-repeat scroll 0px 0px;';
        }
        if ($this->params->get('useBackgroundImageHover', '1')) {
            if ($this->params->get('backgroundImageHover', '') == '') {
                $this->backgroundImageHover = JURI::root() . 'media/J2top/images/arrow_active.gif';
            } else {
                $this->backgroundImageHover = JURI::root() . 'images/' . $this->params->get('backgroundImageHover');
            }
            $this->backgroundImageHoverCss = 'background: url(' . $this->backgroundImageHover . ') no-repeat scroll 0px 0px;';
        }

        $this->document->addScriptDeclaration('!function($){
                $(document).ready(function(){
                    $("#gototop").click(function(e){
                        $("html, body").animate({scrollTop : 0}, ' . $this->params->get('animationSpeed', '300') . ');
                    });
                    $(window).scroll(function(){
                        if ($(this).scrollTop() > ' . $this->params->get('fadePosition', '200') . ') {
                            $("#gototop").fadeIn(' . $this->params->get('fadeSpeed', '400') . ');
                        } else {
                            $("#gototop").fadeOut(' . $this->params->get('fadeSpeed', '400') . ');
                        }
                    });
                });
            }(jQuery);');
        $this->document->addStyleDeclaration('
            #gototop {
                display: block;
                width: ' . $this->params->get('width', '95px') . ';
                height: ' . $this->params->get('height', '30px') . ';
                position: fixed;
                ' . (($this->params->get('position', 2) == 1) ? 'left' : 'right') . ': ' . $this->params->get('borderDistance', '3px') . ';
                bottom: ' . $this->params->get('bottomDistance', '3px') . ';
                z-index: 1000000;
            }
            #gototop div {
                cursor: pointer;
                width: ' . $this->params->get('width', '95px') . ';
                height: ' . $this->params->get('height', '30px') . ';
                ' . $this->backgroundImageCss . '
                background-color: ' . $this->params->get('backgroundColor', 'transparent') . ';
                padding-top: ' . $this->params->get('paddingTop', '7px') . ';
                padding-right: ' . $this->params->get('paddingRight', '0') . ';
                padding-bottom: ' . $this->params->get('paddingBottom', '0') . ';
                padding-left: ' . $this->params->get('paddingLeft', '7px') . ';
                font-size: ' . $this->params->get('fontSize', '14px') . ';
                color: ' . $this->params->get('fontColor', '#676767') . ';
                text-align: center;
            }
            #gototop div:hover,
            #gototop div:focus,
            #gototop div:active {
                color: ' . $this->params->get('fontColorHover', '#4D87C7') . ';
                ' . $this->backgroundImageHoverCss . '
                background-color: ' . $this->params->get('backgroundColorHover', 'transparent') . '
            }');
    }

    public function onAfterRender()	{
        // only proceed in HTML output
        if ((JFactory::getApplication()->getClientId() == 1 && !$this->backend) || $this->document->getType() !== 'html') {
            return;
        }

		$lang = JFactory::getLanguage();
        $lang->load('plg_system_J2top', JPATH_ADMINISTRATOR, 'en-GB', false, true);
        $lang->load('plg_system_J2top', JPATH_ADMINISTRATOR);

		// text may contain translation tags for various languages
		$text = trim($this->params->get('text', 'J2TOP_TOP_OF_PAGE'));
		if (strpos($text, '>>') === false) {
            $text = JText::_($text);
        } else {
			$lines = explode("\n", $text);
            $translationFound = false;
			foreach ($lines as $line) {
				list($key, $value) = explode('>>', $line);
				if ($key == $lang->get('tag')) {
					$text = JText::_($value);
                    $translationFound = true;
                    break;
				}
			}
			if (!$translationFound) {
				$text = JText::_('J2TOP_TOP_OF_PAGE');
			}
		}

        $preloadImages = '';
        if ($this->backgroundImage) {
            $preloadImages = '<img src="' .  $this->backgroundImage . '" alt="" />';
        }
        if ($this->backgroundImageHover) {
            $preloadImages .= '<img src="' .  $this->backgroundImageHover . '" alt="" />';
        }

		// retrieve the page body
		$body = JResponse::getBody();

		// inject the go-to-top button at the end of the page
		$insert = '<div style="display: none;">' . $preloadImages . '</div>
			<div id="gototop" style="display: none"><div title="' . $text . '">' . $text . '</div></div>
            </body>';
		$body = str_ireplace('</body>', $insert, $body);

        // set our modified body
		JResponse::setBody($body);
	}
}