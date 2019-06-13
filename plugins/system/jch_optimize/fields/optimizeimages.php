<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optmized downloads
 *
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2014 Samuel Marshall
 * @license GNU/GPLv3, See LICENSE file
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die;

if (version_compare(PHP_VERSION, '5.3.0', '<'))
{

        class JFormFieldOptimizeimages extends JFormField
        {

                public $type = 'optimizeimages';

                protected function getInput()
                {
                        
                }

        }

}
else
{
        include_once dirname(__FILE__) . '/auto.php';

        class JFormFieldOptimizeimages extends JFormFieldAuto
        {

                public $type = 'optimizeimages';

                /**
                 * 
                 * @return type
                 */
                protected function getInput()
                {
                        $curl_enabled = function_exists('curl_version') && curl_version();
                       // $allow_url_fopen = (bool) ini_get('allow_url_fopen');
                        
                        if ($curl_enabled)// && $allow_url_fopen)
                        {
                                if (JFactory::getApplication()->input->get('jchtask') == 'optimizeimages')
                                {
                                        $this->optimizeImages();
                                }

                                $field = '<div id="optimize-images-container" >'
                                        . '<div id="file-tree-container"></div>';
                                
                                $field .= '<div id="files-container"></div>';

                                $field .= parent::getInput();
                                $field .= '<div style="clear:both"></div>';
                                $field .= '</div>';
                        }
                        else
                        {
                                $header  = JText::_('Error');
                                //$message = !$allow_url_fopen ? JText::_('JCH_OPTIMIZE_IMAGE_NO_URL_FOPEN_MESSAGE') : '';
                                $message = !$curl_enabled ? JText::_('JCH_OPTIMIZE_IMAGE_NO_CURL_MESSAGE'): $message;

                                if (version_compare(JVERSION, '3.0', '<'))
                                {
                                        $field = '<dl id="system-message">
<dt class="message">' . $header . '</dt>
<dd class="message warning">
	<ul>
		<li>' . $message . '</li>
	</ul>
</dd>
</dl>';
                                }
                                else
                                {
                                        $field = '<div class="alert">
<h4 class="alert-heading">' . $header . '</h4>
		<p>' . $message . '</p>
</div>';
                                }
                        }

                        return $field;
                }

                /**
                 * 
                 * @return string
                 */
                protected function getButtons()
                {
                        $page = JURI::getInstance()->toString() . '&jchtask=optimizeimages';

                        $aButton              = array();
                        $aButton[0]['link']   = '';
                        $aButton[0]['icon']   = 'fa-compress';
                        $aButton[0]['color']  = '#278EB1';
                        $aButton[0]['text']   = JchPlatformUtility::translate('Optimize Images');
                        $aButton[0]['script'] = 'onclick="jchOptimizeImages(\'' . $page . '\'); return false;"';
                        $aButton[0]['class']  = 'enabled';

                        return $aButton;
                }

                /**
                 * 
                 */
                protected function optimizeImages()
                {
                        $arr = JFactory::getApplication()->input->getArray(
                                array('dir' => 'string', 'cnt' => 'int', 'status' => 'string', 'msg' => 'string'));

                        $oController = new JControllerLegacy();

                        if ($arr['status'] == 'fail')
                        {
                                $oController->setMessage(JText::_('The Optimize Image function failed with message "' . $arr['msg'] . '"'),
                                                                          'error');
                        }
                        else
                        {
                                $dir = JchPlatformUtility::decrypt($arr['dir']);

                                $oController->setMessage(sprintf(JText::_('%1$d images optimized in %2$s'), $arr['cnt'], $dir));
                        }

                        $this->display($oController);
                }

        }

}

?>
