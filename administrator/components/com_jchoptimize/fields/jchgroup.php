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

                $class = isset($attributes['class']) !== false ? 'class="' . $attributes['class'] . '" ' : '';

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
