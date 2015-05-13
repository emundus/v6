<?php
/*código php para regalar. Tener en cuenta que sólo puede regalar el solicitado*/
$db = JFactory::getDBO();
$ids = JRequest::getVar( 'ids', array(), 'method', 'array' );
$user = JFactory::getUser();
$ID_usuario_actual =  $user->get('id');
$estado="confirmado. Esperando respuesta.";
//var_dump($estado,$ID_usuario_actual);exit;
foreach ($ids AS $k){
	
	$row_intercambio = $model->getRow($k);
	$ID_inter = $row_intercambio->jos_fabrik_intercambios___id;//hallamos el id del intercambio para iniciar el borrado en cascada
	$ID_solicitado = $row_intercambio->jos_fabrik_intercambios___solicitado;
	$ID_solicitante = $row_intercambio->jos_fabrik_intercambios___solicitante;
	$confirmacion_solicitado = $row_intercambio->jos_fabrik_intercambios___confirmacion_solicitado;
	$confirmacion_solicitante = $row_intercambio->jos_fabrik_intercambios___confirmacion_solicitante;
	if (($confirmacion_solicitado == 1) && ($confirmacion_solicitante ==1))
	{
		$statusMsg = "Intercambio ya confirmado. Si tiene algún problema póngase en contacto con nosotros.";
	}
	elseif ($ID_usuario_actual == $ID_solicitado)
	{
		
		$InsertQuery3="UPDATE `jos_fabrik_intercambios` SET `confirmacion_solicitado`=1,`estado_solicitado`='$estado' WHERE id='$ID_inter'";
		$db->setQuery($InsertQuery3);
		$db->query();
		//var_dump($db);exit;
		$statusMsg == "Intercambio confirmado. Esperando respuesta";
	}
	else
		{
			$InsertQuery3="UPDATE `jos_fabrik_intercambios` SET `date_time`=CURRENT_DATE,`confirmacion_solicitante`=1,`estado_solicitante`='$estado' WHERE id='$ID_inter'";
			$db->setQuery($InsertQuery3);
			$db->query();
			$statusMsg == "Intercambio confirmado. Esperando respuesta.";
		}
}//fin_foeach
/*$your_url = "index.php/mi-armario";
$app =&JFactory::getApplication();
$app->redirect( JRoute::_($your_url), $your_msg);*/
?>
			
