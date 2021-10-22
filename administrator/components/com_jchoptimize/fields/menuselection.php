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

defined( '_JEXEC' ) or die( 'No direct access' );

use Joomla\CMS\HTML\HTMLHelper;

require_once JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php';

class JFormFieldMenuselection extends JFormField
{

	public $type = 'menuselection';


	public function setup( SimpleXMLElement $element, $value, $group = null )
	{
		$script_options = array( 'framework' => false, 'relative' => true );

		if ( version_compare( JVERSION, '3.99.99', '<' ) )
		{
			JHtml::script( 'jui/jquery.min.js' );
			JHtml::_( 'script', 'jui/treeselectmenu.jquery.min.js', false, true );
		}
		else
		{
			HTMLHelper::_( 'script', 'vendor/jquery/jquery.min.js', $script_options );
			$oDocument = JFactory::getDocument();
			$oDocument->addScript( JUri::root( true ) . '/media/legacy/js/treeselectmenu.js' );
		}

		if ( ! is_array( $value ) )
		{
			$value = array();
		}

		return parent::setup( $element, $value, $group );
	}


	protected function getInput()
	{
		$menuTypes = MenusHelper::getMenuLinks();

		$html = '';

		if ( ! empty( $menuTypes ) ) :
			$id = 'jform_menuselect';

			$html .= '<div>
			<div class="form-inline">
				<span class="small">' . JText::_( 'JSELECT' ) . ':
					<a id="treeCheckAll" href="javascript://">' . JText::_( 'JALL' ) . '</a>,
					<a id="treeUncheckAll" href="javascript://">' . JText::_( 'JNONE' ) . '</a>
				</span>
				<span class="width-20">|</span>
				<span class="small">' . JText::_( 'JCH_EXPAND' ) . ':
					<a id="treeExpandAll" href="javascript://">' . JText::_( 'JALL' ) . '</a>,
					<a id="treeCollapseAll" href="javascript://">' . JText::_( 'JNONE' ) . '</a>
				</span>
				<input type="text" id="treeselectfilter" name="treeselectfilter" class="input-medium search-query pull-right" size="16"
					autocomplete="off" placeholder="' . JText::_( 'JSEARCH_FILTER' ) . '" aria-invalid="false" tabindex="-1">
			</div>

			<div class="clearfix"></div>

			<hr class="hr-condensed" />

			<ul class="treeselect">';
			foreach ( $menuTypes as &$type ) :
				if ( count( $type->links ) ) :
					$prevlevel = 0;
					$html      .= '<li>
						<div class="treeselect-item pull-left">
							<label class="pull-left nav-header">' . $type->title . '</label></div>';
					foreach ( $type->links as $i => $link ) :
						if ( $prevlevel < $link->level )
						{
							$html .= '<ul class="treeselect-sub">';
						}
						elseif ( $prevlevel > $link->level )
						{
							$html .= str_repeat( '</li></ul>', $prevlevel - $link->level );
						}
						else
						{
							$html .= '</li>';
						}
//						$selected = 0;
//						if ($pluginassignment == 0)
//						{
//							$selected = 1;
//						} elseif ($pluginassingment < 0)
//						{
//							$selected = in_array(-$link->value, $this->value);
//						} elseif ($pluginassignment > 0)
//						{
						$selected = in_array( $link->value, $this->value );
//						}

						$html .= '<li>
								<div class="treeselect-item pull-left">
									<input type="checkbox" class="pull-left" name="jform[menuexcluded][]" id="' . $id . $link->value . '" value="' . (int)$link->value . '"' . ( $selected ? ' checked="checked"' : '' ) . ' />
									<label for="' . $id . $link->value . '" class="pull-left">
									' . $link->text . ' <span class="small">' . JText::sprintf( 'JGLOBAL_LIST_ALIAS', htmlentities( $link->alias ) ) . '</span>
';
						if ( JLanguageMultilang::isEnabled() && $link->language != '' && $link->language != '*' )
						{
							$html .= JHtml::_( 'image', 'mod_languages/' . $link->language_image . '.gif', $link->language_title, array( 'title' => $link->language_title ), true );
						}
						if ( $link->published == 0 )
						{
							$html .= ' <span class="label">' . JText::_( 'JUNPUBLISHED' ) . '</span>';
						}

						$html .= '	</label>
								</div>';

						$menuitems_limit = 50;
						if ( ! isset( $type->links[ $i + 1 ] ) || $i > $menuitems_limit )
						{
							$html .= str_repeat( '</li></ul>', $link->level );
						}
						$prevlevel = $link->level;

						if ( $i > $menuitems_limit )
						{
							break;
						}

					endforeach;
					$html .= '	</li>';
				endif;
			endforeach;
			$html .= '	</ul>
			<div id="noresultsfound" style="display:none;" class="alert alert-no-items">
				' . JText::_( 'JGLOBAL_NO_MATCHING_RESULTS' ) . '
			</div>
			<div style="display:none;" id="treeselectmenu">
				<div class="pull-left nav-hover treeselect-menu">
					<div class="btn-group">
						<a href="#" data-toggle="dropdown" class="dropdown-toggle btn btn-micro">
							<span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							<li class="nav-header">' . JText::_( 'JCH_SUBITEMS' ) . '</li> <li class="divider"></li>
							<li class=""><a class="checkall" href="javascript://"><span class="icon-checkbox"></span> ' . JText::_( 'JSELECT' ) . '</a>
							</li>
							<li><a class="uncheckall" href="javascript://"><span class="icon-checkbox-unchecked"></span> ' . JText::_( 'JCH_DESELECT' ) . '</a>
							</li>
							<div class="treeselect-menu-expand">
							<li class="divider"></li>
							<li><a class="expandall" href="javascript://"><span class="icon-plus"></span>' . JText::_( 'JCH_EXPAND' ) . '</a></li>
							<li><a class="collapseall" href="javascript://"><span class="icon-minus"></span>' . JText::_( 'JCH_COLLAPSE' ) . '</a></li>
							</div>
						</ul>
					</div>
				</div>
			</div>
		</div>';
		endif;

		return $html;
	}

}
