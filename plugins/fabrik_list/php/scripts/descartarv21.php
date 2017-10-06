<?php
$db = JFactory::getDBO();
$ids = JRequest::getVar( 'ids', array(), 'method', 'array' );
$estado ="descartado";
foreach ($ids AS $k){
	$row_intercambio = $model->getRow($k);
	$ID_inter = $row_intercambio->jos_fabrik_intercambios___id;//hallamos el id del intercambio para iniciar el borrado en cascada
	$confirmacion_solicitado = $row_intercambio->jos_fabrik_intercambios___confirmacion_solicitado;
	$confirmacion_solicitante = $row_intercambio->jos_fabrik_intercambios___confirmacion_solicitante;
	if (($confirmacion_solicitado == 1) && ($confirmacion_solicitante ==1))
	{
		$statusMsg  = "Intercambio confirmado por ambas partes. No se puede descartar. Si tiene algún problema póngase en contacto con nosotros.";
	}
	else
		{
			$InsertQuery="UPDATE `jos_fabrik_intercambios` SET `activo`=0, `estado_solicitante`='$estado', `estado_solicitado`='$estado' WHERE `jos_fabrik_intercambios`.`id` = '$ID_inter'";
			$db->setQuery($InsertQuery);
			$db->query();
			//var_dump($db);exit;
			$statusMsg  = "Intercambio descartado";
		}
}//fin_foreach	
//var_dump( $db ); exit;
//$your_url = "";
//$app =&JFactory::getApplication();
//$app->redirect( JRoute::_($your_url), $your_msg);
?>
