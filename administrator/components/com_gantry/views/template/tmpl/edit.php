<?php
/**
 * @version	$Id: edit.php 6306 2013-01-05 05:39:57Z btowles $
 * @package Gantry
 * @copyright Copyright (C) 2009 RocketTheme. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author RocketTheme, LLC
 */
/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );


JFactory::getApplication()->input->set('hidemainmenu', 1);

/** @var $gantry Gantry */
		global $gantry;

JHtml::_('behavior.keepalive');
$user = JFactory::getUser();
$canDo = $this->getActions();
$isNew = ($this->item->id == 0);

require_once(JPATH_LIBRARIES."/gantry/gantry.php");


gantry_import('core.config.gantryform');
gantry_import('core.config.gantryformnaminghelper');

$gantryForm = $this->gantryForm;
$fieldSets = $gantryForm->getFieldsets();



//$gantry->addStyle($gantry->gantryUrl."/admin/widgets/gantry-administrator.css");
$this->compileLess();
define('GANTRY_CSS', 1);


$gantry->addScript($gantry->gantryUrl."/admin/widgets/moofx.js");
$gantry->addScript($gantry->gantryUrl."/admin/widgets/Twipsy.js");
$gantry->addScript($gantry->gantryUrl."/admin/widgets/gantry.js");
$gantry->addScript($gantry->gantryUrl."/admin/widgets/gantry.popupbuttons.js");
$gantry->addScript($gantry->gantryUrl . '/admin/widgets/ajaxbutton/js/ajaxbutton.js');
$gantry->addScript($gantry->gantryUrl."/admin/widgets/growl.js");
if ($this->override) $gantry->addScript($gantry->gantryUrl."/admin/widgets/assignments/js/assignments.js");
$gantry->addInlineScript("var GantryIsMaster = ".(($this->override) ? 'false' : 'true').";");

function gantry_admin_render_menu($view, $item)
{
	$user = JFactory::getUser();
	$canDo = $view->getActions();
	$isNew = ($item->id == 0);
	ob_start();
	?>
	<ul class="g4-actions">
		<li class="rok-dropdown-group">
			<div class="rok-buttons-group">

				<div class="rok-button rok-button-primary" id="toolbar-apply" data-g4-toolbaraction="template.apply">Save</div
				<div data-g4-toggle="save" class="rok-button rok-button-primary">
					<span class="caret"></span>
					<ul data-g4-dropdown="save" class="rok-dropdown">
						<li><a href="#" id="toolbar-save" data-g4-toolbaraction="template.save">Save &amp; Close</a></li>
						<?php if (!$isNew && $canDo->get('core.create')):?>
						<li><a href="#" id="toolbar-save-copy" data-g4-toolbaraction="template.save2copy">Save as Copy</a></li>
						<?php endif; ?>
						<li class="divider"></li>
						<li><a href="#" id="toolbar-save-preset">Save Preset</a></li>

					</ul>
				</div>
			</div>
		</li>
		<li class="rok-button rok-button-secondary" id="toolbar-show-presets">Presets</li>
		<li class="rok-button" id="toolbar-clearcache" data-ajaxbutton="{model: 'cache', action: 'clear'}">Clear Cache</li>
		<!--<li class="rok-button" id="toolbar-purge">Reset</li>-->
		<li class="rok-button" data-g4-toolbaraction="template.cancel">Close</li>
	</ul>
	<?php
	$buffer = ob_get_clean();
	return $buffer;
}

function gantry_admin_render_edit_item($element)
{
	if ($element->type == 'tips' && (isset($element->element['tab']) && (string) $element->element['tab'] != 'overview')) return $element->getInput();

	$buffer = '';
	$buffer .= "				<div class=\"gantry-field " . $element->type . "-field g4-row\">\n";
	$label = '';
	if ($element->show_label) $label = $element->getLabel() . "\n";
	$buffer .= "<div class=\"g4-cell g4-col1\">\n";
	$buffer .= $label;
	$buffer .= "</div>";
	$buffer .= "<div class=\"g4-cell g4-col2\"><div class=\"g4-col2-wrap\">\n";
	$buffer .= "<span class=\"arrow\"><span></span></span>";
	$buffer .= $element->getInput() . "\n";
	$buffer .= "</div></div>\n";
	$buffer .= "</div>\n";
	return $buffer;
}

function  gantry_admin_render_edit_override_item($element)
{
	if ($element->type == 'tips' && (isset($element->element['tab']) && (string) $element->element['tab'] != 'overview')) return $element->getInput();

	$buffer = "";
	$buffer .= "				<div class=\"gantry-field " . $element->type . "-field g4-row\">\n";
	$label = '';
	$checked = ($element->variance) ? ' checked="checked"' : '';
	if ($element->show_label){
		if (!$element->setinoverride) $label = $element->getLabel() . "\n";
		else $label = '<div class="field-label"><span class="inherit-checkbox"><input  name="overridden-' . $element->name . '" type="checkbox"' . $checked . '/></span><span class="base-label">' . $element->getLabel() . '</span></div>';
	}
	$buffer .= "<div class=\"g4-cell g4-col1\">\n";
	$buffer .= $label;
	$buffer .= "</div>";
	$buffer .= "<div class=\"g4-cell g4-col2\"><div class=\"g4-col2-wrap\">\n";
	$buffer .= "<span class=\"arrow\"><span></span></span>";
	$buffer .= $element->getInput() . "\n";
	$buffer .= "</div></div>\n";
	$buffer .= "</div>\n";
	return $buffer;
}

function get_badges_layout($name, $override=0, $involved=0, $assignments=0) {
	if ($name == 'assignment'){
		return '<span class="menuitems-involved"><span>'.$assignments.'</span></span>';
	} else {
		if ($override) {
			return '
				<span class="badges-involved">'."\n".'
				<span class="presets-involved"> <span>0</span></span> '."\n".'
				<span class="overrides-involved"> <span>'.$involved.'</span></span>'."\n".'
			</span>';
		} else {
			return '<span class="presets-involved"><span>0</span></span>';
		}
	}
}

function get_version_update_info(){

	$buffer = '';
	gantry_import('core.gantryupdates');
	$gantry_updates = GantryUpdates::getInstance();
	$currentVersion =  $gantry_updates->getCurrentVersion();
	$latest_version = $gantry_updates->getLatestVersion();

	if (version_compare($latest_version,$currentVersion,'>')){
		$klass="update";
		$upd = JText::sprintf('COM_GANTRY_VERSION_UPDATE_OUTOFDATE',$latest_version,'index.php?option=com_installer&view=update');
	} else {
		$klass = "noupdate";
		jimport('joomla.utilities.date');
		$nextupdate = new JDate($gantry_updates->getLastUpdated()+(24*60*60));

		$upd = JText::sprintf('COM_GANTRY_VERSION_UPDATE_CURRENT');
	}

	$buffer .= "
	<div class='gantry-field updater-field ".$klass."'  id='updater'>
		<div id='updater-bar' class='h2bar'>Gantry <span>v".$currentVersion."</span></div>
		<div id='updater-desc'>".$upd."</div>
	</div>";

	return $buffer;
}

$this->gantryForm->initialize();
?>

<div class="g4-wrap <?php echo (!$this->override) ? 'defaults-wrap' : 'override-wrap'; ?>">
	<?php if(!$this->override):?><div id="gantry-master"></div><?php endif;?>
	<div id="g4-toolbar">
		<h1>Templates Manager <small>/ Edit Style</small></h1>
		<?php echo gantry_admin_render_menu($this, $this->item); ?>
	</div>
	<form action="<?php echo JRoute::_('index.php?option=com_gantry&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
		<?php echo $this->form->getInput('client_id'); ?>
		<div id="g4-hidden">
			<?php if ($this->item->id) : ?>
				<?php echo $this->form->getInput('id'); ?>
			<?php endif; ?>
		</div>

		<?php
			$status = JFactory::getApplication()->input->cookie->getString('gantry-'.$gantry->templateName.'-adminpresets','hide');
			$presetsShowing = ($status == 'hide') ? "" : ' class="presets-showing"';

			if ($this->override) {
				$flag = "g4-flag-override";
				$flag_text = "Override";
			} else {
				$flag = "g4-flag-master";
				$flag_text = "&#10029; Master";
			}
		?>

		<div id="g4-details-wrapper">
			<div id="g4-master" class="<?php echo $flag; ?> g4-size-13">
				<div id="g4-flag">
					<?php echo $flag_text; ?>
					<span class="arrow"><span></span></span>
				</div>
			</div>
			<div id="g4-details"<?php echo $presetsShowing; ?>>



				<fieldset class="adminform g4-horizontal-form">
					<div class="g4-controlgroup g4-detail-title g4-size-30">
						<?php echo $this->form->getLabel('title'); ?>
						<div class="g4-controls g4-input-text">
							<?php echo $this->form->getInput('title'); ?>
						</div>
					</div>
					<div class="g4-controlgroup g4-detail-template g4-size-25">
						<?php echo $this->form->getLabel('template'); ?>
						<div class="g4-controls g4-input-text g4-input-readonly">
							<?php echo $this->form->getInput('template'); ?>
						</div>
					</div>
					<div class="g4-controlgroup g4-detail-home g4-size-25">
						<?php echo $this->form->getLabel('home'); ?>
						<div class="g4-controls g4-input-select">
							<?php echo $this->form->getInput('home'); ?>
						</div>
					</div>


				</fieldset>
			</div>
		</div>
		<div id="g4-presets">
			<div class="submit-wrapper png"></div>
			<?php echo $this->loadTemplate('presets'); ?>
		</div>
		<div id="g4-container">

			<?php //settings_fields('theme-options-array'); ?>



				<div class="g4-header">
					<div class="g4-wrapper">
						<div class="g4-row">
							<div class="g4-column">
								<div id="g4-logo"><span></span></div>
								<ul class="g4-tabs">
								<?php
									$panels = array();
									$positions = array(
										'hiddens' => array(),
										'top' => array(),
										'left' => array(),
										'right' => array(),
										'bottom' => array()
									);

									$involvedCounts = array();
									foreach ($fieldSets as $name => $fieldSet) {
										if ($name == 'toolbar-panel') continue;
										$fields = $gantryForm->getFullFieldset($name);
										$involved = 0;
										if ($name == 'assignment' && (!$user->authorise('core.edit', 'com_menu') || !$canDo->get('core.edit.state'))) continue;
										array_push($panels, array("name" => $name, "height" => (isset($fieldSet->height))?$fieldSet->height:null));
										foreach($fields as $fname => $field) {
											$position = $field->panel_position;

											if ($field->type != 'hidden' && $field->setinoverride && $field->variance) $involved++;
											if ($field->type == 'hidden') $position = 'hiddens';
											if (!isset($positions[$position][$name])) $positions[$position][$name] = array();
											array_push(
												$positions[$position][$name],
												$field
												//array("name" => $field->name, "label" => $field->label, "input" => $field->input, "show_label" => $field->show_label, 'type' => $field->type)
											);
										}
										$involvedCounts[$name] = $involved;
									}


									foreach ($fieldSets as $name => $fieldSet):
										if ($name == 'toolbar-panel') continue;
										if ($name == 'assignment' && (!$user->authorise('core.edit', 'com_menu') || !$canDo->get('core.edit.state'))) continue;
										?>
										<li class="<?php echo $this->tabs[$name];?>">
											<span class="badge"><?php echo get_badges_layout($name, $this->override, $involvedCounts[$name], $this->assignmentCount);?></span>
											<?php echo JText::_($fieldSet->label);?>
											<span class="arrow"><span><span></span></span></span>
										</li>
									<?php endforeach;?>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="g4-body">
					<?php
						$output = "";
						$output .= "<div id=\"g4-panels\">\n";
						if (count($panels) > 0)
						{
							for($i = 0; $i < count($panels); $i++) {
								$panel = $panels[$i]['name'];
								if ($panel == 'assignment' && (!$user->authorise('core.edit', 'com_menu') || !$canDo->get('core.edit.state'))) continue;
								$width = '';
								if ((@count($positions['left'][$panels[$i]['name']]) && !@count($positions['right'][$panels[$i]['name']])) || (!@count($positions['left'][$panels[$i]['name']]) && @count($positions['right'][$panels[$i]['name']]))) {
									$width = 'width-100pc';
								}

								$activePanel = "";
								if ($i == $this->activeTab - 1) $activePanel = " active-panel";
								else $activePanel = "";

								$output .= "	<div class=\"g4-panel panel-".($i+1)." panel-".$panel." ".$width.$activePanel."\">\n";

								$buffer = "";
								foreach($positions as $name => $position) {

									if (isset($positions[$name][$panel])) {
										// hide right panels in Gantry4 for all but overview tab
										if (!($name == "right" && $panel != "overview")) {
											$buffer .= "		<div class=\"g4-panel-".$name."\">\n";
											$panel_name = $name == 'left' ? 'panelform' : 'paneldesc';

											$buffer .= "			<div class=\"".$panel_name."\">\n";

											if ($panel_name == 'paneldesc' && $panel == 'overview') {
												$buffer .= get_version_update_info();

											}
											foreach($positions[$name][$panel] as $element) {
												if (!$this->override){
													$buffer .= $element->render('gantry_admin_render_edit_item');
												}
												else{
													$buffer .= $element->render('gantry_admin_render_edit_override_item');
												}
											}

											$buffer .= "			</div>\n";
											$buffer .= "		</div>\n";
										}

										if ($panel != 'overview' && $name == 'right'){
											foreach($positions[$name][$panel] as $element) {
												if (get_class($element) != 'GantryFormFieldTips') continue;

												if (!$this->override){
													$buffer .= $element->render('gantry_admin_render_edit_item');
												}
												else{
													$buffer .= $element->render('gantry_admin_render_edit_override_item');
												}
											}
										}
									}
								}
								$output .= $buffer;

								$output .= "	</div>";
							}
						}
						$output .= "</div>\n";
						echo $output;
						?>
						<div class="clr"></div>
					</div>

				<div class="clr"></div>
				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_('form.token'); ?>
			</div>

		</form>
	</div>

<?php
 // css overrides
	if ($gantry->browser->name == 'ie' && file_exists($gantry->gantryPath . '/' . 'admin' . '/' . 'widgets' . '/' . 'gantry-ie.css')) {
		$gantry->addStyle($gantry->gantryUrl . '/admin/widgets/gantry-ie.css');
	}
	if ($gantry->browser->name == 'ie' && $gantry->browser->version == '7' && file_exists($gantry->gantryPath . '/' . 'admin' . '/' . 'widgets' . '/' . 'gantry-ie7.css')) {
		$gantry->addStyle($gantry->gantryUrl . '/admin/widgets/gantry-ie7.css');
	}

	if (($gantry->browser->name == 'firefox' && $gantry->browser->version < '3.7') || ($gantry->browser->name == 'ie' && $gantry->browser->version > '6')) {
		$css = ".text-short, .text-medium, .text-long, .text-color {padding-top: 4px;height:19px;}";
		$gantry->addInlineStyle($css);
	}

	if ($gantry->browser->name == 'ie' && $gantry->browser->shortversion == '7') {
		$css = "
			.g-surround, .g-inner, .g-surround > div {zoom: 1;position: relative;}
			.text-short, .text-medium, .text-long, .text-color {border:0 !important;}
			.selectbox {z-index:500;position:relative;}
			.group-fusionmenu, .group-splitmenu {position:relative;margin-top:0 !important;zoom:1;}
			.scroller .inner {position:relative;}
			.moor-hexLabel {display:inline-block;zoom:1;float:left;}
			.moor-hexLabel input {float:left;}
		";
		$gantry->addInlineStyle($css);
	}
	if ($gantry->browser->name == 'opera' && file_exists($gantry->gantryPath . '/' . 'admin' . '/' . 'widgets' . '/' . 'gantry-opera.css')) {
		$gantry->addStyle($gantry->gantryUrl . '/admin/widgets/gantry-opera.css');
	}

	$this->gantryForm->finalize();
	$gantry->finalize();
