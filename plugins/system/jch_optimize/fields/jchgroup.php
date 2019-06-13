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

class JFormFieldJchgroup extends JFormField
{

        public $type = 'jchgroup';

        protected function getLabel()
        {
                return '';
        }

        protected function getInput()
        {
                $attributes = $this->element->attributes();

                $html = '';

                $header      = isset($attributes['label']) ? '<h4>' . JText::_($attributes['label']) . '<span class="fa"></span></h4>' : '';
                $description = isset($attributes['description']) ? '<p><em>' . JText::_($attributes['description']) . '</em></p>' : '';
                $section     = $attributes['section'];
                $name        = $attributes['name'];

                $class = strpos($name, 'auto') !== FALSE ? 'class="collapsible" ' : '';

                $collapsible = '<div ' . $class . '>';
                $collapsible .= $header;
                $collapsible .= $description . '<br>';
                $collapsible .= '</div><div>';

                if (version_compare(JVERSION, '3.0', '>='))
                {

                        $html .= '</div></div>';

                        if ($section == 'start')
                        {
                                $html .= '<div class="well well-small">';
                                $html .= $collapsible;
                        }
                        else
                        {
                                $html .= '</div></div>';
                        }

                        $html .= '<div><div>';
                }
                else
                {
                        if ($section == 'start')
                        {
                                $html .= '<div class="jchgroup">';
                                $html .= $collapsible;
                                $html .= '<ul class="adminformlist">';
                        }
                        else
                        {
                                $html .= '</ul></div></div>';
                        }
                }

                return $html;
        }

}
