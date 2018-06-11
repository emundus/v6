<?php
/**
 * Fabrik List Template: Div
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$doc = JFactory::getDocument();
$doc->addStyleSheet( 'media/com_emundus/lib/bootstrap-232/css/bootstrap.min.css' );
// The number of columns to split the list rows into
$pageClass = $this->params->get('pageclass_sfx', '');

if ($pageClass !== '') :
	echo '<div class="' . $pageClass . '">';
endif;

if ($this->tablePicker != '') : ?>
	<div style="text-align:right"><?php echo FText::_('COM_FABRIK_LIST') ?>: <?php echo $this->tablePicker; ?></div>
<?php
endif;

if ($this->params->get('show_page_heading')) :
	echo '<h1>' . $this->params->get('page_heading') . '</h1>';
endif;

if ($this->showTitle == 1) : ?>
	<div class="page-header">
		<h1><?php echo $this->table->label;?></h1>
	</div>
<?php
endif;

// Intro outside of form to allow for other lists/forms to be injected.
echo $this->table->intro;

?>
<form class="fabrikForm form-search" action="<?php echo $this->table->action;?>" method="post" id="<?php echo $this->formid;?>" name="fabrikList">

	<?php
	if ($this->hasButtons):
		echo $this->loadTemplate('buttons');
	endif;

	
	//for some really ODD reason loading the headings template inside the group
	//template causes an error as $this->_path['template'] doesn't contain the correct
	// path to this template - go figure!
	/*$headingsHtml = $this->loadTemplate('headings');
	echo $this->loadTemplate('tabs');*/
	?>

	<div class="fabrikDataContainer">
		
		<?php foreach ($this->pluginBeforeList as $c) :
			echo $c;
		endforeach;


		$data = array();
		$i = 0;
			//if($key == $group[0]->data->$key)
		foreach($this->rows[0] as $k=>$v){
			foreach ($this->headings as $key => $val) {
				if(array_key_exists($key, $v->data))
					if(strcasecmp($v->data->$key , "oui") == 0)
						$data[$i][$val] = $v->data->$key;
					else
						$data[$i][$key] = $v->data->$key;		
			}
			$i = $i + 1;
		}
		//var_dump($data);die;

		?>												
		<style>
			table {
				font-family: Arial, sans-serif;
				width: 100%;
				float: right;
				margin-bottom:50px;
			}

			td, th {
				text-align: left;
				padding: 10px;
			}
			

			/*tr:nth-child(even) {
				border-bottom-left-radius: 10px;
			}*/
			p {
				font-size: 16px;
				color:black;
			}
			.em-search-engine-div-data {
				width:95%; 
				height:100px; 
				text-align:justify;
				border: 1px solid;
				border-radius:5px;
    			padding: 10px;
    			box-shadow: 5px 10px #a22727;
			}
			.em-search-engine-filters {
				width:52%;
				float:left;
				position:absolute;
				
			}
			.em-search-engine-data {
				width:68%;
				float:right;
				position:relative
			}
			.fabrikDataContainer{
				padding-bottom:500px;
			}
			.filtertable{
				border: 1px solid #b9b9b9f5; 
				border-radius: 5px;
				border-style: hidden; 
				box-shadow: 0 0 10px 0 #a22727
			}
			
		</style>

		<div class="em-search-engine-filters well" >
			<?php
				if ($this->showFilters && $this->bootShowFilters) :
					echo $this->layoutFilters();
				endif; 
			?>
		</div>
		
		<div class="em-search-engine-data">
			<table>
				<thead>
					<tr>
						<td><h3>RESULTAT DE LA RECHERCHE ACTIVE</h3></td>
					</tr>
				</thead>
				<tfoot>
					<tr class="fabrik___heading">
						<td colspan="<?php echo count($this->headings);?>">
							<?php echo $this->nav;?>
						</td>
					</tr>
				</tfoot>
				
				<tbody>
				
					<?php  
						$region=""; $department=""; $chercheur=""; $cherches=""; $themes="";
						$gCounter = 0;

						foreach($data as $d){
									
							$region 	= $d['data_regions___name'];
							$department = $d['data_departements___departement_nom'];
							$chercheur 	= strtolower($d['jos_emundus_setup_profiles___label']);
							if(count(array_keys($d, "Oui")) > 1){
								$cherches  	= implode(",", array_keys($d, "Oui"));
								$cherches 	= strtolower(str_replace(","," et ", $cherches));	
							}else{
								$cherches	= strtolower(array_search("Oui", $d));
							}
								$themes     = $d['jos_emundus_projet_620_repeat___themes'];
							echo '<tr>
									<td>
										<div class="em-search-engine-div-data">
											<p>En région '.$region.', dans le département '.$department.', un '.$chercheur.' cherche '.$cherches.' sur le thème '.$themes.'</p>
											<a class="em-search-engine-learn-more" href="#">Connectez-vous pour en savoir plus</a>
										</div>
									</td>
								</tr>';
							unset($cherches);
							unset($themes);
							$gCounter++;

						}

					?>
				</tbody>
				<?php if ($this->hasCalculations) : ?>
					<tfoot>
						<tr class="fabrik_calculations">

						<?php
						foreach ($this->headings as $key => $heading) :
							$h = $this->headingClass[$key];
							$style = empty($h['style']) ? '' : 'style="' . $h['style'] . '"';?>
							<td class="<?php echo $h['class']?>" <?php echo $style?>>
								<?php
								$cal = $this->calculations[$key];
								echo array_key_exists($groupedBy, $cal->grouped) ? $cal->grouped[$groupedBy] : $cal->calc;
								?>
							</td>
						<?php
						endforeach;
						?>

						</tr>
					</tfoot>
				<?php endif ?>
			</table>
		</div>
		<?php print_r($this->hiddenFields);?>
	</div>
</form>



