<?php
/*código php para regalar. Tener en cuenta que sólo puede regalar el solicitado*/
$db = JFactory::getDBO();
$ids = JRequest::getVar( 'ids', array(), 'method', 'array' );
$user = JFactory::getUser();
$ID_usuario_actual =  $user->get('id');
//var_dump($ID_usuario_actual);exit;
$peso_solicitado ="-- kg";
$portes_solicitado=0;
foreach ($ids AS $k){
	
	$row_intercambio = $model->getRow($k);
	$ID_inter = $row_intercambio->jos_fabrik_intercambios___id;//hallamos el id del intercambio para iniciar el borrado en cascada
	$ID_solicitado = $row_intercambio->jos_fabrik_intercambios___solicitado;
	$ID_solicitante = $row_intercambio->jos_fabrik_intercambios___solicitante;
	$regalar = $row_intercambio->jos_fabrik_intercambios___regalar;
	//var_dump($ID_usuario_actual,$ID_inter,$regalar,$ID_solicitado);exit;
	if (($confirmacion_solicitado == 1) && ($confirmacion_solicitante ==1))
	{
		$statusMsg = "Intercambio confirmado por ambas partes. No se puede regalar. Si tiene algún problema póngase en contacto con nosotros.";
	}
	elseif  (($regalar == NULL) || ($regalar==0))
		{
			if ($ID_usuario_actual == $ID_solicitado)
			{
				//var_dump($ID_usuario_actual,$ID_inter,$regalar,$ID_solicitado,$ID_solicitante);exit;
				$InsertQuery="DELETE artinter FROM `jos_fabrik_articulos` AS a INNER JOIN `jos_fabrik_art_inter` AS artinter ON a.id = artinter.id_art WHERE artinter.id_inter='$ID_inter' AND a.propietario='$ID_solicitante'";
				$db->setQuery($InsertQuery);
				$db->query();
				//var_dump($db);exit;
				$estado_solicitado="regalas tus artículos. Intercambio confirmado";
				$estado_solicitante="El solicitado regala sus artículos. Confirma el intercambio";
				$InsertQuery3="UPDATE `jos_fabrik_intercambios` SET `confirmacion_solicitado`=1,`regalar`=1, `date_time`=CURRENT_DATE,`estado_solicitado`='$estado_solicitado',`estado_solicitante`='$estado_solicitante', `peso_solicitado`='$peso_solicitado',`portes_solicitado`='$portes_solicitado' WHERE id='$ID_inter'";
				$db->setQuery($InsertQuery3);
				$db->query();
				//var_dump($db);exit;
				$statusMsg == "regalas tu intercambio. Intercambio confirmado";
			}
		}
		else
		{
			$statusMsg == "Ya has regalado tu intercambio";
		}
}
/*$your_url = "index.php/mi-armario";
$app =&JFactory::getApplication();
$app->redirect( JRoute::_($your_url), $your_msg);*/
?>
