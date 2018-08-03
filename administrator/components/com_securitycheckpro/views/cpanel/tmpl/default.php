<?php
/**
* Securitycheck Pro Control Panel View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load language
$lang = JFactory::getLanguage();
$lang->load('com_securitycheckpro.sys');

// Load plugin language
$lang2 = JFactory::getLanguage();
$lang2->load('plg_system_securitycheckpro');

$review = sprintf( $lang->_('COM_SECURITYCHECKPRO_REVIEW'), '<a href="http://extensions.joomla.org/extensions/extension/access-a-security/site-security/securitycheck-pro" target="_blank"  rel="noopener noreferrer">', '</a>' );
$translator_name = $lang2->_('COM_SECURITYCHECKPRO_TRANSLATOR_NAME');
$firewall_plugin_status = $lang2->_('COM_SECURITYCHECKPRO_FIREWALL_PLUGIN_STATUS');
$cron_plugin_status = $lang2->_('COM_SECURITYCHECKPRO_CRON_PLUGIN_STATUS');
$update_database_plugin_status = $lang2->_('COM_SECURITYCHECKPRO_UPDATE_DATABASE_PLUGIN_STATUS');
$spam_protection_plugin_status = $lang2->_('COM_SECURITYCHECKPRO_SPAM_PROTECTION_PLUGIN_STATUS');
$logs_status = $lang->_('COM_SECURITYCHECKPRO_LOGS_STATUS');
$autoupdate_status = $lang2->_('COM_SECURITYCHECKPRO_AUTOUPDATE_STATUS');
$translator_url = $lang2->_('COM_SECURITYCHECKPRO_TRANSLATOR_URL');

// Cargamos el comportamiento modal para mostrar las ventanas para exportar
JHtml::_('behavior.modal');

// Eliminamos la carga de las librerías mootools
$document = JFactory::getDocument();
$rootPath = JURI::root(true);
$arrHead = $document->getHeadData();
unset($arrHead['scripts'][$rootPath.'/media/system/js/mootools-core.js']);
unset($arrHead['scripts'][$rootPath.'/media/system/js/mootools-more.js']);
$document->setHeadData($arrHead);

$opa_icons = "media/com_securitycheckpro/stylesheets/opa-icons.css";
JHTML::stylesheet($opa_icons);

$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

// Css circle
JHTML::stylesheet('media/com_securitycheckpro/new/css/circle.css');

$sweet = "media/com_securitycheckpro/stylesheets/sweetalert.css";
JHTML::stylesheet($sweet);

// Load Javascript
$document = JFactory::getDocument();
//Cookie
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/new/js/js.cookie.js');

// Url to be used on statistics
$logUrl = 'index.php?option=com_securitycheckpro&controller=securitycheckpro&view=logs&datefrom=%s&dateto=%s';
?>

  <!-- Bootstrap core JavaScript -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/jquery/jquery.min.js"></script>
<!-- Page level plugin JavaScript-->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/chart.js/Chart.min.js"></script>

<?php 
// Cargamos el contenido común
include JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';
?>

<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/js/sweetalert.min.js"></script>

<script type="text/javascript" language="javascript">

	jQuery(document).ready(function() {
		// Actualizamos los datos del gráfico 'pie'
		Chart.defaults.global.defaultFontFamily='-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif',Chart.defaults.global.defaultFontColor="#292b2c";var ctx=document.getElementById("piechart"),piechart=new Chart(ctx,{type:"pie",data:{labels:['<?php echo JText::_('COM_SECURITYCHECKPRO_BLOCKED_ACCESS'); ?>','<?php echo JText::_('COM_SECURITYCHECKPRO_USER_AND_SESSION_PROTECTION'); ?>','<?php echo JText::_('COM_SECURITYCHECKPRO_FIREWALL_RULES_APLIED'); ?>'],datasets:[{data:['<?php echo $this->total_blocked_access; ?>','<?php echo $this->total_user_session_protection; ?>','<?php echo $this->total_firewall_rules; ?>'],backgroundColor:["#007bff","#dc3545","#ffc107"]}]}});
			
		<?php 
			// Actualizamos la variable de estado para no mostrar más el popup
			$mainframe = JFactory::getApplication();						
		?>
		
		// Mostramos el aviso de actualizaciones si hay que actualizar
		dias_ultima_actualizacion = '<?php echo $this->geoip_database_update; ?>';
		testigo_update = '<?php 
			$existe = JFile::exists(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR .'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'scans' . DIRECTORY_SEPARATOR . 'maxmind_update.php');
			echo $existe;
		?>';
		if (dias_ultima_actualizacion > 30 && testigo_update == false ) {
			jQuery("#div_update_geoblock_database").modal('show');		
			jQuery("#div_refresh").show();		
		}
		
		//Tooltip subscripcion
		jQuery("#subscriptions_status").tooltip();
		jQuery("#scp_version").tooltip();
		jQuery("#update_database_version").tooltip();
		
		if( Cookies.get('SCPInfoMessage') ){
            //it is still within the day          		  
        } else {
            //either cookie already expired, or user never visit the site
            //create the cookie			
            Cookies.set('SCPInfoMessage', 'SCPInfoMessage', { expires: 1 });

            //and display the div
           jQuery("#mensaje_informativo").show();
		   window.setTimeout(function () {
				jQuery("#mensaje_informativo").fadeTo(500, 0).slideUp(500, function () {
					jQuery(this).remove();
				});
			}, 5000);
        }
		
	});
	
	function oculta_popup(){
		jQuery("#div_update_geoblock_database").modal('hide');		
		jQuery("#div_refresh").hide();
	}
	function muestra_progreso(){
		jQuery("#div_boton_subida").hide();
		jQuery("#div_loading").show();
	}
</script>

<script type="text/javascript" language="javascript">

	function Set_Easy_Config() {
		url = 'index.php?option=com_securitycheckpro&controller=cpanel&format=raw&task=Set_Easy_Config';
		jQuery.ajax({
			url: url,							
			method: 'GET',
			success: function(data){
				location.reload();				
			}
		});
	}
	
	function Set_Default_Config() {
		url = 'index.php?option=com_securitycheckpro&controller=cpanel&format=raw&task=Set_Default_Config';
		jQuery.ajax({
			url: url,							
			method: 'GET',
			success: function(data){
				location.reload();				
			}
		});
	}
	var ActiveTab = "lists"; 
		
	function SetActiveTab($value) {
		ActiveTab = $value;
		storeValue('active', ActiveTab);
	}
	
	function storeValue(key, value) {
		if (localStorage) {
			localStorage.setItem(key, value);
		} else {
			$.cookies.set(key, value);
		}
	}		
</script>
<?php 
	$valor_a_mostrar = 0; 
	$contador = 0; 
	$period = ""; 
	while ( ($valor_a_mostrar == 0) && ($contador < 3) ){
		$aleatorio = rand(1,5);
		switch ($aleatorio) {
			case 1:
				// Logs este año
				$valor_a_mostrar = $this->this_year_logs;
				$period = JText::_('COM_SECURITYCHECKPRO_CPANEL_THIS_YEAR');
				break;
			case 2:
				// Logs mes pasado
				$valor_a_mostrar = $this->last_month_logs;
				$period = JText::_('COM_SECURITYCHECKPRO_CPANEL_LAST_MONTH');
				break;
			case 3:
				// Logs este mes
				$valor_a_mostrar = $this->this_month_logs;
				$period = JText::_('COM_SECURITYCHECKPRO_CPANEL_THIS_MONTH');
				break;
			case 4:
				// Logs ayer
				$valor_a_mostrar = $this->yesterday;
				$period = JText::_('COM_SECURITYCHECKPRO_CPANEL_YESTERDAY');
				break;
			case 5:
				// Logs hoy
				$valor_a_mostrar = $this->today;
				$period = JText::_('COM_SECURITYCHECKPRO_CPANEL_TODAY');
				break;
		}
		$contador++;		
	}
	
?>

<?php 
if ( version_compare(JVERSION, '3.20', 'lt') ) {
?>
<!-- Bootstrap core CSS-->
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
<?php } else { ?>
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/bootstrap/css/bootstrap_j4.css" rel="stylesheet">

<?php } ?>
 <!-- Custom styles for this template-->
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/css/sb-admin.css" rel="stylesheet">
<!-- Custom fonts for this template-->
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/font-awesome/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/font-awesome/css/fa-solid.css" rel="stylesheet" type="text/css">

  
<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro');?>" method="post" name="adminForm" id="adminForm">

	<!-- Modal update geoblock -->
	<div class="modal hide bd-example-modal-lg" id="div_update_geoblock_database" tabindex="-1" role="dialog" aria-labelledby="div_update_geoblock_databaseLabel" aria-hidden="true">
		  <div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
			  <div class="modal-header">
				<h2 class="modal-title" id="div_update_geoblock_databaseLabel"><?php echo JText::_('COM_SECURITYCHECKPRO_UPDATE_DATABASE_TEXT'); ?></h2>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>				
			  </div>
			  <div class="modal-body">	
				<span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_GEOIPV2_NEEDS_UPDATE'); ?></span><br/><br/>	
			  </div>
				<div class="modal-footer" id="div_boton_subida">
					<button class="btn btn-dark" type="button" onclick="Joomla.submitbutton('go_to_geoblock');"><?php echo JText::_( 'COM_SECURITYCHECKPRO_GO_TO_GEOBLOCK' ); ?></button>
					<button class="btn btn-info" type="button" onclick="oculta_popup(); Joomla.submitbutton('automatic_updates_geoblock');"><?php echo JText::_( 'COM_SECURITYCHECKPRO_AUTOMATIC_UPDATES_GEOBLOCK' ); ?></button>
				</div>			  
			</div>
		  </div>
		</div>
		
		<?php 
		// Cargamos la navegación
		include JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
		?>	
		
			<?php
				if ($valor_a_mostrar != 0) {		
			?>
			<div id="mensaje_informativo" class="alert alert-success" style="display: none;">
				 <h4><?php echo JText::sprintf('COM_SECURITYCHECKPRO_INFO_MESSAGE',$valor_a_mostrar,$period) ?></h4>
			 </div>
			<?php
				}
			?>
		
		  <!-- Breadcrumb-->
		  <ol class="breadcrumb">
			<li class="breadcrumb-item">
			  <a href="#"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
			</li>
			<li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_MYDASHBOARD'); ?></li>
		  </ol>
		  
		 <?php
				$app = JComponentHelper::getParams('com_securitycheckpro');
				$downloadid = $app->get('downloadid');
				if ( empty($downloadid) ) {
			?>		
			<div class="card text-center mb-3">
				<div class="card-header">
					<?php echo JText::_( 'COM_SECURITYCHECKPRO_DOWNLOAD_ID' ); ?>
				</div>
				<div class="card-body">
					<p class="card-text"><?php echo JText::_( 'COM_SECURITYCHECKPRO_DOWNLOAD_ID_MESSAGE' ); ?></p>
					<a href="index.php?option=com_config&view=component&component=com_securitycheckpro&path=&return=<?php echo base64_encode(JURI::getInstance()->toString()); ?>" class="btn btn-info">
					<i class="icon-edit icon-white"></i>					
						<?php echo JText::_('COM_SECURITYCHECKPRO_FILL_IT_NOW'); ?>
					</a>
				</div>				
			</div>			
			<?php 
				}
			?> 			
				
		<!-- Contenido principal -->
      <div class="row">
				
		<div class="col-xl-3 col-sm-6 mb-3">
		<?php
			$enabled = $this->firewall_plugin_enabled;
			if ($enabled){
		?>
		    <div class="card border-success text-center">
		<?php } else { ?>
			<div class="card border-danger text-center">
		<?php } ?>	
			<div class="card-header">
				<?php echo $firewall_plugin_status; ?>
			</div>
            <div class="card-body">				
				<?php
					if ($enabled) { ?>
						<span class="sc-icon32 sc-icon-green sc-icon-globe"></span>					
				<?php  } else { ?>
						<span class="sc-icon32 sc-icon-darkgray sc-icon-globe"></span>
				<?php 	}  ?>								
				<div>
				<?php
						if ($enabled){ ?>
							<span class="badge badge-success"><?php echo(JText::_( 'COM_SECURITYCHECKPRO_PLUGIN_ENABLED' )); ?></span>
				<?php 	}else{ ?>
							<span class="badge badge-danger"><?php echo(JText::_( 'COM_SECURITYCHECKPRO_PLUGIN_DISABLED' )); ?></span>
				<?php	}  ?>
				</div>
				<div style="margin-top: 10px;">
				<?php
					if ($enabled){ 
				?>
					<button class="btn btn-danger" onclick="Joomla.submitbutton('disable_firewall')" href="#">
						<i class="fapro fa-fw fa-power-off"> </i>
						<?php echo JText::_('COM_SECURITYCHECKPRO_DISABLE'); ?>
					</button>
				<?php 	}else{ ?>
					<button class="btn btn-success" onclick="Joomla.submitbutton('enable_firewall')" href="#">
						<i class="icon-ok icon-white"> </i>
						<?php echo JText::_('COM_SECURITYCHECKPRO_ENABLE'); ?>
					</button>
				<?php	}  ?>
				</div>              
            </div>            
          </div>
        </div>
	  
		<div class="col-xl-3 col-sm-6 mb-3">
		<?php
			$enabled = $this->cron_plugin_enabled;
			if ($enabled){
		?>
		    <div class="card border-success text-center">
		<?php } else { ?>
			<div class="card border-danger text-center">
		<?php } ?>	
			<div class="card-header">
				<?php echo $cron_plugin_status; ?>
			</div>
            <div class="card-body">
				<?php
					if ($enabled) { ?>
						<span class="sc-icon32 sc-icon-green sc-icon-clock"></span>						
				<?php  } else { ?>
						<span class="sc-icon32 sc-icon-darkgray sc-icon-clock"></span>
				<?php 	}  ?>							
				<div>
				<?php
						if ($enabled){ ?>
							<span class="badge badge-success"><?php echo(JText::_( 'COM_SECURITYCHECKPRO_PLUGIN_ENABLED' )); ?></span>
				<?php 	}else{ ?>
							<span class="badge badge-danger"><?php echo(JText::_( 'COM_SECURITYCHECKPRO_PLUGIN_DISABLED' )); ?></span>
				<?php	}  ?>
				</div>
				<div style="margin-top: 10px;">
				<?php
					if ($enabled){ 
				?>
					<button class="btn btn-danger" onclick="Joomla.submitbutton('disable_cron')" href="#">
						<i class="fapro fa-fw fa-power-off"> </i>
						<?php echo JText::_('COM_SECURITYCHECKPRO_DISABLE'); ?>
					</button>
				<?php 	}else{ ?>
					<button class="btn btn-success" onclick="Joomla.submitbutton('enable_cron')" href="#">
						<i class="icon-ok icon-white"> </i>
						<?php echo JText::_('COM_SECURITYCHECKPRO_ENABLE'); ?>
					</button>
				<?php	}  ?>
				</div>              
            </div>            
          </div>
        </div>
        
		<div class="col-xl-3 col-sm-6 mb-3">
		<?php
			$exists = $this->update_database_plugin_exists; 
			$enabled = $this->update_database_plugin_enabled;
			if ($enabled){
		?>
		    <div class="card border-success text-center">
		<?php } else { 
			if ($exists){ ?>
				<div class="card border-danger text-center">
			<?php } else { ?>
				<div class="card text-center">
			<?php } ?>
		<?php } ?>	
			<div class="card-header">
				<?php echo $update_database_plugin_status; ?>
			</div>
            <div class="card-body">
				<?php
					if (!$exists) { ?>
						<span class="sc-icon32 sc-icon-black sc-icon-refresh"></span>						
				<?php  } else if ($enabled && $exists){ ?>
						<span class="sc-icon32 sc-icon-green sc-icon-refresh"></span>
				<?php 	}else if (!$enabled && $exists){ ?>
						<span class="sc-icon32 sc-icon-darkgray sc-icon-refresh"></span>
				<?php	}  ?>								
				<div>
				<?php
						if (!$exists) { ?>
							<span class="badge badge-dark"><?php echo(JText::_( 'COM_SECURITYCHECKPRO_PLUGIN_NOT_INSTALLED' )); ?></span>
							
				<?php  } else if ($enabled && $exists) { ?>
							<span class="badge badge-success"><?php echo(JText::_( 'COM_SECURITYCHECKPRO_PLUGIN_ENABLED' )); ?></span>
				<?php 	}else if (!$enabled && $exists) { ?>
							<span class="badge badge-danger"><?php echo(JText::_( 'COM_SECURITYCHECKPRO_PLUGIN_DISABLED' )); ?></span>
				<?php	}  ?>
				</div>
				<div style="margin-top: 10px;">
				<?php
					if ($enabled && $exists ){ 
				?>
					<button class="btn btn-danger" onclick="Joomla.submitbutton('disable_update_database')" href="#">
						<i class="fapro fa-fw fa-power-off"> </i>
						<?php echo JText::_('COM_SECURITYCHECKPRO_DISABLE'); ?>
					</button>
				<?php } else if (!$enabled && $exists ) { ?>
					<button class="btn btn-success" onclick="Joomla.submitbutton('enable_update_database')" href="#">
						<i class="icon-ok icon-white"> </i>
						<?php echo JText::_('COM_SECURITYCHECKPRO_ENABLE'); ?>
					</button>
				<?php } else if (!$exists ) { ?>
					<a class="btn btn-info" type="button" href="https://securitycheck.protegetuordenador.com/index.php/our-products/securitycheck-pro-database-update" target="_blank"  rel="noopener noreferrer"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
				<?php } ?>
				</div>              
            </div>            
          </div>
        </div>
     	  
	  <div class="col-xl-3 col-sm-6 mb-3">
		<?php
			$exists = $this->spam_protection_plugin_exists; 
			$enabled = $this->spam_protection_plugin_enabled;
			if ($enabled){
		?>
		    <div class="card border-success text-center">
		<?php } else { 
			if ($exists){ ?>
				<div class="card border-danger text-center">
			<?php } else { ?>
				<div class="card text-center">
			<?php } ?>
		<?php } ?>	
			<div class="card-header">
				<?php echo $spam_protection_plugin_status; ?>
			</div>
            <div class="card-body">
				<?php
					if (!$exists) { ?>
						<span class="sc-icon32 sc-icon-black sc-icon-user"></span>						
				<?php  } else if ($enabled && $exists){ ?>
						<span class="sc-icon32 sc-icon-green sc-icon-user"></span>
				<?php 	}else if (!$enabled && $exists){ ?>
						<span class="sc-icon32 sc-icon-darkgray sc-icon-user"></span>
				<?php	}  ?>								
				<div>
				<?php
						if (!$exists) { ?>
							<span class="badge badge-dark"><?php echo(JText::_( 'COM_SECURITYCHECKPRO_PLUGIN_NOT_INSTALLED' )); ?></span>
							
				<?php  } else if ($enabled && $exists) { ?>
							<span class="badge badge-success"><?php echo(JText::_( 'COM_SECURITYCHECKPRO_PLUGIN_ENABLED' )); ?></span>
				<?php 	}else if (!$enabled && $exists) { ?>
							<span class="badge badge-danger"><?php echo(JText::_( 'COM_SECURITYCHECKPRO_PLUGIN_DISABLED' )); ?></span>
				<?php	}  ?>
				</div>
				<div style="margin-top: 10px;">
				<?php
					if ($enabled && $exists ){ 
				?>
					<button class="btn btn-danger" onclick="Joomla.submitbutton('disable_spam_protection')" href="#">
						<i class="fapro fa-fw fa-power-off"> </i>
						<?php echo JText::_('COM_SECURITYCHECKPRO_DISABLE'); ?>
					</button>
				<?php } else if (!$enabled && $exists ) { ?>
					<button class="btn btn-success" onclick="Joomla.submitbutton('enable_spam_protection')" href="#">
						<i class="icon-ok icon-white"> </i>
						<?php echo JText::_('COM_SECURITYCHECKPRO_ENABLE'); ?>
					</button>
				<?php } else if (!$exists ) { ?>
					<a class="btn btn-info" type="button" href="https://securitycheck.protegetuordenador.com/index.php/our-products/securitycheck-spam-protection" target="_blank"  rel="noopener noreferrer"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
				<?php } ?>
				</div>              
            </div>            
          </div>
        </div>
      </div>
		
	<div class="row">
        <div class="col-lg-6">
        <!-- Statistics-->
			<div class="card mb-3">
				<div class="card-header">
					<i class="fapro fa-bars"></i>
					<?php echo ' ' . JText::_('COM_SECURITYCHECKPRO_CPANEL_STATISTICS'); ?>
				</div>
				<div class="card-body">
				
					<ul class="nav nav-tabs" role="tablist" id="myTab">
					  <li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#historic" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_HISTORIC'); ?></a>
					  </li>
					  <li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#detail" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DETAIL'); ?></a>
					  </li>
					  <li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#lists" role="tab"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_LISTS'); ?></a>
					  </li>
					</ul>

					<div class="tab-content">
					  <div class="tab-pane active" id="historic" role="tabpanel">
						<h5 style="text-align: center;"><?php echo JText::_('COM_SECURITYCHECKPRO_GRAPHIC_HEADER'); ?></h5>
						<canvas id="piechart" width="100%" height="40"></canvas>						
					  </div>
					  
					  <div class="tab-pane" id="detail" role="tabpanel">
						<table class="table table-striped">
							<thead>
							  <tr>
								  <th><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_PERIOD'); ?></th>
								  <th class="center"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_ENTRIES'); ?></th>
							  </tr>
							</thead> 
							<tbody>
								<tr>
									<td>
										<a href="<?php echo sprintf($logUrl, (gmdate('Y')-1).'-01-01 00:00:00', (gmdate('Y')-1).'-12-31 23:59:59')?>">
										<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_LAST_YEAR'); ?></a>
									</td>
									<td class="center">
										<b><?php echo $this->last_year_logs ?></b>
									</td>						
								</tr>
								<tr>
									<td>
										<a href="<?php echo sprintf($logUrl, gmdate('Y').'-01-01', gmdate('Y').'-12-31 23:59:59')?>">
										<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_THIS_YEAR'); ?></a>
									</td>
									<td class="center">
										<b><?php echo $this->this_year_logs ?></b>
									</td>						
								</tr>
								<tr>
									<?php
										$y = gmdate('Y');
										$m = gmdate('m');
										if($m == 1) {
											$m = 12; $y -= 1;
										} else {
											$m -= 1;
										}
										switch($m) {
											case 1: case 3: case 5: case 7: case 8: case 10: case 12:
												$lmday = 31; break;
											case 4: case 6: case 9: case 11:
												$lmday = 30; break;
											case 2:
												if( !($y % 4) && ($y % 400) ) {
													$lmday = 29;
												} else {
													$lmday = 28;
												}
										}
										if($y < 2011) $y = 2011;
										if($m < 1) $m = 1;
										if($lmday < 1) $lmday = 1;
									?>
									<td>
										<a href="<?php echo sprintf($logUrl, $y.'-'.$m.'-01', $y.'-'.$m.'-'.$lmday.' 23:59:59')?>">
										<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_LAST_MONTH'); ?></a>
									</td>
									<td class="center">
										<b><?php echo $this->last_month_logs ?></b>
									</td>						
								</tr>
								<tr>
									<?php
										switch(gmdate('m')) {
											case 1: case 3: case 5: case 7: case 8: case 10: case 12:
												$lmday = 31; break;
											case 4: case 6: case 9: case 11:
												$lmday = 30; break;
											case 2:
												$y = gmdate('Y');
												if( !($y % 4) && ($y % 400) ) {
													$lmday = 29;
												} else {
													$lmday = 28;
												}
										}
										if($lmday < 1) $lmday = 28;
									?>
									<td>
										<a href="<?php echo sprintf($logUrl, gmdate('Y').'-'.gmdate('m').'-01', gmdate('Y').'-'.gmdate('m').'-'.$lmday.' 23:59:59')?>">
										<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_THIS_MONTH'); ?></a>
									</td>
									<td class="center">
										<b><?php echo $this->this_month_logs ?></b>
									</td>						
								</tr>
								<tr>
									<td>
										<a href="<?php echo sprintf($logUrl, gmdate('Y-m-d', time()-7*24*3600), gmdate('Y-m-d 23:59:59'))?>">
										<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_LAST_7_DAYS'); ?></a>
									</td>
									<td class="center">
										<b><?php echo $this->last_7_days ?></b>
									</td>						
								</tr>
								<tr>
									<?php
										$date = new DateTime();
										$date->setDate(gmdate('Y'), gmdate('m'), gmdate('d'));
										$date->modify("-1 day");
										$yesterday = $date->format("Y-m-d");
										$date->modify("+1 day")
									?>
									<td>
										<a href="<?php echo sprintf($logUrl, $yesterday, $date->format("Y-m-d"))?>">
										<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_YESTERDAY'); ?></a>
									</td>
									<td class="center">
										<b><?php echo $this->yesterday ?></b>
									</td>						
								</tr>
								<tr>
									<?php
										$expiry = clone $date;
										$expiry->modify('+1 day');
									?>
									<td>
										<a href="<?php echo sprintf($logUrl, $date->format("Y-m-d"), $expiry->format("Y-m-d"))?>">
										<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_TODAY'); ?></a>
									</td>
									<td class="center">
										<b><?php echo $this->today ?></b>
									</td>						
								</tr>
							</tbody>
						</table>
					  </div>
					  
					  <div class="tab-pane" id="lists" role="tabpanel">
						<table class="table table-striped">
							<thead>
							  <tr>
								  <th><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_LIST'); ?></th>
								  <th class="center"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_LIST_ELEMENTS'); ?></th>
							  </tr>							   
							</thead> 
							<tbody>
								<tr>
									<td>
										<?php echo JText::_('COM_SECURITYCHECKPRO_BLACKLIST'); ?></a>
									</td>
									<td class="center">
										<b><?php
											$black = count($this->blacklist_elements);											
											echo $black;
										?></b>
									</td>						
								</tr>
								<tr>
									<td>
										<?php
											if ( ($black-1) >= 0 ) { ?>
												<span class="badge badge-danger"><?php echo $this->blacklist_elements[$black-1]; ?></span>
										<?php } ?>
										<?php
											if ( ($black-2) >= 0 ) { ?>
												<span class="badge badge-danger"><?php echo $this->blacklist_elements[$black-2]; ?></span>
										<?php } ?>										
										<?php
											if ( ($black-3) >= 0 ) { ?>
												<span class="badge badge-danger"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE'); ?></span>
										<?php } ?>
									</td>
									<td>											
									</td>
								</tr>								
								<tr>
									<td>
										<?php echo JText::_('COM_SECURITYCHECKPRO_DYNAMIC_BLACKLIST'); ?></a>
									</td>
									<td class="center">
										<b><?php
											$dynamic = count($this->dynamic_blacklist_elements);											
											echo $dynamic;											
										?></b>
									</td>										
								</tr>
								<tr>
									<td>
										<?php
											if ( ($dynamic-1) >= 0 ) { ?>
												<span class="badge badge-warning"><?php echo $this->dynamic_blacklist_elements[$dynamic-1]; ?></span>
										<?php } ?>
										<?php
											if ( ($dynamic-2) >= 0 ) { ?>
												<span class="badge badge-warning"><?php echo $this->dynamic_blacklist_elements[$dynamic-2]; ?></span>
										<?php } ?>
										<?php
											if ( ($dynamic-3) >= 0 ) { ?>
												<span class="badge badge-warning"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE'); ?></span>
										<?php } ?>										
									</td>
									<td>											
									</td>
								</tr>
								<tr>
									<td>
										<?php echo JText::_('COM_SECURITYCHECKPRO_WHITELIST'); ?></a>
									</td>
									<td class="center">
										<b><?php
											$white = count($this->whitelist_elements);										
											echo $white;
										?></b>
									</td>						
								</tr>
								<tr>
									<td>
										<?php
											if ( ($white-1) >= 0 ) { ?>
												<span class="badge badge-success"><?php echo $this->whitelist_elements[$white-1]; ?></span>
										<?php } ?>
										<?php
											if ( ($white-2) >= 0 ) { ?>
												<span class="badge badge-success"><?php echo $this->whitelist_elements[$white-2]; ?></span>
										<?php } ?>
										<?php
											if ( ($white-3) >= 0 ) { ?>
												<span class="badge badge-success"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE'); ?></span>
										<?php } ?>										
									</td>
									<td>											
									</td>
								</tr>
							</tbody>
						</table>
						<div id="dynamic_blacklist_buttons" class="btn-toolbar">
							<div class="btn-group" style="margin-bottom: 5px;">
								<button class="btn btn-info" onclick="SetActiveTab('lists'); Joomla.submitbutton('manage_lists');" href="#">
									<i class="icon-wrench icon-white"> </i>
										<?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_MANAGE_LISTS'); ?>
								</button>
							</div>						
						</div>
					  </div>
					  
					<!-- Tab content -->  
					</div>					
				
					<div class="span11 alert alert-warning"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_HELP'); ?></div>
				</div>				
			</div>			
		</div>
		

		<div class="col-lg-2">
			<!-- Overall status -->
			<div class="card mb-3">
				<div class="card-header">
					<i class="fapro fa-chart-pie"></i>
					<?php echo JText::_( 'COM_SECURITYCHECKPRO_SECURITY_OVERALL_SECURITY_STATUS' ); ?>
				</div>
				<div class="card-body">
					<div style="text-align: right;">
						<button class="btn btn-info btn-mini right" type="button" onclick="Joomla.submitbutton('Go_system_info')" href="#"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CHECK_STATUS' ); ?></i></button>
					</div>					
					<?php 
						$class = "c100 p" .$this->overall . " green";
						if ( ($this->overall > 0) && ($this->overall < 60) ) {
							$class = "c100 p" .$this->overall . " orange";
						} else if ( ($this->overall >= 60) && ($this->overall < 80) ) {
							$class = "c100 p" .$this->overall . '"';
						} 
					?>
					<div class="<?php echo $class; ?>">
                    <span><?php echo $this->overall . "%"; ?></span>
                    <div class="slice">
                        <div class="bar"></div>
                        <div class="fill"></div>
                    </div>
                </div>
				</div>
				<div class="card-footer small text-muted"><?php echo JText::_( 'COM_SECURITYCHECKPRO_UPDATE_DATE' ); echo date('Y-m-d H:i:s'); ?>
				</div>
			</div>						
			
			<!-- Overall status -->
			<div class="card text-white bg-info mb-3" style="max-width: 20rem;">
			  <div class="card-header"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CPANEL_DISCLAIMER' ); ?></div>
			  <div class="card-body">
				<p class="card-text"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CPANEL_DISCLAIMER_TEXT' ); ?></p>
			  </div>
			</div>
		</div>
		
		<div class="col-lg-3">
			<!-- Subscription status -->
			<div class="card mb-3">
				<div class="card-header">
					<i class="fapro fa-ellipsis-v-alt"></i>
					<a href="#" id="subscriptions_status" data-toggle="tooltip" title="<?php echo JText::_( 'COM_SECURITYCHECKPRO_SUBSCRIPTIONS_STATUS_EXPLAINED' ); ?>"><?php echo JText::_( 'COM_SECURITYCHECKPRO_SUBSCRIPTIONS_STATUS' ); ?></a>
				</div>
				<div class="card-body">
					<?php
						$expired = false;
						$mainframe = JFactory::getApplication();
						$exists = $this->update_database_plugin_exists;
						$exists_trackactions = $this->trackactions_plugin_exists;						
						
						if ( !$exists ) {
							$span_update_database = "Securitycheck Pro Update Database<br/><span class=\"badge badge-dark\">";
							$scp_update_database_subscription_status = JText::_( 'COM_SECURITYCHECKPRO_PLUGIN_NOT_INSTALLED' );
						} else {
							$scp_update_database_subscription_status = $mainframe->getUserState("scp_update_database_subscription_status",JText::_( 'COM_SECURITYCHECKPRO_NOT_DEFINED' ));
							$span_update_database = "Securitycheck Pro Update Database<br/>(<span id=\"update_database_version\" data-toggle=\"tooltip\" title=\"" . JText::_( 'COM_SECURITYCHECKPRO_VERSION_INSTALLED' ) . ":&nbsp;" . $this->version_update_database . "\" class=\"badge badge-info\">" . $this->version_update_database . "</span>)&nbsp;&nbsp;";
							if ( $scp_update_database_subscription_status == JText::_( 'COM_SECURITYCHECKPRO_ACTIVE' ) ) {					
								$span_update_database .= "<span class=\"badge badge-success\">";								
							} else if ( $scp_update_database_subscription_status == JText::_( 'COM_SECURITYCHECKPRO_EXPIRED' ) ) {
								$span_update_database .= "<span class=\"badge badge-danger\">";
								$expired = true;
							} else {
								$span_update_database .= "<span class=\"badge badge-dark\">";
							}
						}
						
						if ( !$exists_trackactions ) {
							$span_trackactions = "Track Actions<br/><span class=\"badge badge-dark\">";
							$trackactions_subscription_status = JText::_( 'COM_SECURITYCHECKPRO_PLUGIN_NOT_INSTALLED' );
						} else {
							$trackactions_subscription_status = $mainframe->getUserState("trackactions_subscription_status",JText::_( 'COM_SECURITYCHECKPRO_NOT_DEFINED' ));
							$span_trackactions = "Track Actions<br/>(<span id=\"trackactions_version\" data-toggle=\"tooltip\" title=\"" . JText::_( 'COM_SECURITYCHECKPRO_VERSION_INSTALLED' ) . ":&nbsp;" . $this->version_trackactions . "\" class=\"badge badge-info\">" . $this->version_trackactions . "</span>)&nbsp;&nbsp;";
							if ( $trackactions_subscription_status == JText::_( 'COM_SECURITYCHECKPRO_ACTIVE' ) ) {					
								$span_trackactions .= "<span class=\"badge badge-success\">";								
							} else if ( $trackactions_subscription_status == JText::_( 'COM_SECURITYCHECKPRO_EXPIRED' ) ) {
								$span_trackactions .= "<span class=\"badge badge-danger\">";
								$expired = true;
							} else {
								$span_trackactions .= "<span class=\"badge badge-dark\">";
							}
						}
						
						$scp_subscription_status = $mainframe->getUserState("scp_subscription_status",JText::_( 'COM_SECURITYCHECKPRO_NOT_DEFINED' ));
						if ( $scp_subscription_status == JText::_( 'COM_SECURITYCHECKPRO_ACTIVE' ) ) {					
							$span_scp = "<span class=\"badge badge-success\">";								
						} else if ( $scp_subscription_status == JText::_( 'COM_SECURITYCHECKPRO_EXPIRED' ) ) {
							$span_scp = "<span class=\"badge badge-danger\">";	
							$expired = true;
						} else {
							$span_scp = "<span class=\"badge badge-dark\">";					
						}												
					?>
					<p>Securitycheck Pro<br/>(<span id="scp_version" data-toggle="tooltip" title="<?php echo JText::_( 'COM_SECURITYCHECKPRO_VERSION_INSTALLED' ); ?>:&nbsp;<?php echo $this->version_scp; ?>" class="badge badge-info"><?php echo $this->version_scp ?></span>)&nbsp;&nbsp;<?php echo $span_scp ?><?php echo $scp_subscription_status ?> </span></p>
					<p><?php echo $span_update_database ?><?php echo $scp_update_database_subscription_status ?> </span></p>
					<p><?php echo $span_trackactions ?><?php echo $trackactions_subscription_status ?> </span></p>
					<?php if ( $expired ) { ?>
							<a class="btn btn-small btn-info" type="button" href="https://securitycheck.protegetuordenador.com/subscriptions" target="_blank"  rel="noopener noreferrer"><?php echo JText::_('COM_SECURITYCHECKPRO_RENEW'); ?></a>
					<?php	} ?>		
				</div>				
			</div>	
			
			<!-- Easy config -->
			<div class="card mb-3">
				<div class="card-header">
					<i class="fapro fa-cog"></i>
					<?php echo JText::_( 'COM_SECURITYCHECKPRO_CPANEL_EASY_CONFIG' ); ?>
				</div>
				<div class="card-body text-center">
					<?php $easy_config_applied = $this->easy_config_applied; ?>
					<div><?php echo JText::_( 'COM_SECURITYCHECKPRO_CPANEL_EASY_CONFIG_STATUS' ); ?></div>
					<?php
							if ($easy_config_applied){ ?>
								<span class="badge badge-success"><?php echo(JText::_( 'COM_SECURITYCHECKPRO_CPANEL_APPLIED' )); ?></span>
					<?php 	}else{ ?>
								<span class="badge badge-info"><?php echo(JText::_( 'COM_SECURITYCHECKPRO_CPANEL_NOT_APPLIED' )); ?></span>
					<?php	}  ?>
					<br/>
					<br/>
					<div><?php echo(JText::_( 'COM_SECURITYCHECKPRO_CPANEL_EASY_CONFIG_DEFINITION' )); ?></div>
					<?php
							if ($easy_config_applied){ ?>
								<button class="btn btn-primary" type="button" onclick="Set_Default_Config();"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CPANEL_APPLY_DEFAULT_CONFIG' ); ?></button>
					<?php 	}else{ ?>
								<button class="btn btn-success" type="button" onclick="Set_Easy_Config();"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CPANEL_APPLY_EASY_CONFIG' ); ?></button>														
					<?php	}  ?>	
				</div>
			</div>	
			
			<!-- Help us -->
			<div class="card bg-light mb-3">
				<div class="card-body text-center">
					<h3 class="card-title"><i class="fapro fa-thumbs-up"></i>&nbsp;&nbsp;<?php echo ' ' . JText::_('COM_SECURITYCHECKPRO_CPANEL_HELP_US'); ?></h3>
					<p class="card-text">
						<?php echo($review); ?><br/><br/>
						<i class="fapro fa-info-square"></i>&nbsp;&nbsp;<?php echo('<a href="' . $translator_url . '" target="_blank"  rel="noopener noreferrer">' . $translator_name . '</a>'); ?>
					</p>
				</div>
			</div>
		</div>
			
	</div>
	
	<!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#isisJsData">
      <i class="fapro fa-angle-up"></i>
    </a>
	
<!-- End Main panel -->	
</div> 
  
  <!-- Bootstrap core JavaScript -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/popper/popper.min.js"></script>
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/bootstrap/js/bootstrap.min.js"></script>
<!-- Custom scripts for all pages -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/js/sb-admin.js"></script> 
 <!-- Core plugin JavaScript-->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/jquery-easing/jquery.easing.min.js"></script>
 
<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="cpanel" />
</form>


