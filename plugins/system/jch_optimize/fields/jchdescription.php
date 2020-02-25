<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optmized downloads
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license GNU/GPLv3, See LICENSE file
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
defined('_JEXEC') or die('No direct access');

class JFormFieldJchdescription extends JFormField
{

        public $type = 'jchdescription';

        protected function getLabel()
        {
                return '';
        }

        protected function getInput()
        {
                $attributes = $this->element->attributes();

                $html = '';

                switch ($attributes['section'])
                {
                        case 'features':
                                $header      = JText::_('JCH_HEADER_MAJOR_FEATURES');
                                $pro_only    = ' <small style="color:red"><em>' . JText::_('JCH_FEATURES_PRO_ONLY') . '</em></small>';
                                $description = '<ul>'
                                        . '<li>' . JText::_('JCH_FEATURES_COMBINE_FILES') . '</li>'
                                        . '<li>' . JText::_('JCH_FEATURES_MINIFY_FILES') . '</li>'
                                        . '<li>' . JText::_('JCH_FEATURES_SPRITE_GENERATOR') . '</li>'
                                        . '<li>' . JText::_('JCH_FEATURES_PRO_CDN') . $pro_only . '</li>'
                                        . '<li>' . JText::_('JCH_FEATURES_PRO_LAZY_LOAD') . $pro_only . '</li>'
                                        . '<li>' . JText::_('JCH_FEATURES_PRO_OPTIMIZE_CSS_DELIVERY') . $pro_only . '</li>'
                                        . '<li>' . JText::_('JCH_FEATURES_PRO_OPTIMIZE_IMAGES') . $pro_only . '</li>'
                                        . '</ul>';

                                break;
                        case 'support':
                                $header      = JText::_('JCH_HEADER_SUPPORT');
                                $description = '<p>' . JText::sprintf('JCH_SUPPORT_DOCUMENTATION', 'https://www.jch-optimize.net/documentation.html') . '</p>'
                                        . '<p>' . JText::sprintf('JCH_SUPPORT_REQUESTS', 'https://www.jch-optimize.net/subscribe/levels.html') . '</p>';
                               
                                break;
                        
                        case 'feedback':
                                $header      = JText::_('JCH_HEADER_FEEDBACK');
                                $description = '<p>' . JText::sprintf('JCH_FEEDBACK_DESCRIPTION', 'http://extensions.joomla.org/extensions/extension/core-enhancements/performance/jch-optimize') . '</p>';
                                break;
                        
                        case 'version':
                                $header = '';
                                $description = '<h4>(Version 6.0.0)</h4>';
                                break;
                        
                        default:
                                break;
                }


                if (version_compare(JVERSION, '3.0', '>='))
                {
                        $html .= '</div></div>';

                        $html .= '<div>';
                        $html .= $header == '' ? '' : '<h3>' . $header . '</h3>';
                        $html .= $description;
                        $html .= '</div>';

                        $html .= '<div><div>';
                }

                return $html;
        }

}
