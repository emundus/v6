<?php
jimport( 'joomla.application.component.model' );
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
require_once (JPATH_COMPONENT.DS.'models'.DS.'ranking.php');
class EmundusModelXls_ranking extends JModelList
{
	function export_xls($uids = array(), $element_id = array(0)) {
		error_reporting(0);

		@set_time_limit(10800);
		global $mainframe;
		$baseurl = JURI::base();
		$db	= JFactory::getDBO();
		jimport( 'joomla.user.user' );

		/** PHPExcel */
		ini_set('include_path', JPATH_BASE . '/libraries/');
		include 'PHPExcel.php'; 
		include 'PHPExcel/Writer/Excel5.php'; 
		$filename = 'emundus_applicants_'.date('Y.m.d').'.xls';
		$realpath = EMUNDUS_PATH_REL.'tmp/'.$filename;

		$query = 'SELECT params FROM #__fabrik_elements WHERE name like "final_grade" LIMIT 1';
		$db->setQuery( $query );
		//die(str_replace('#_','jos',$query));
		$params = $db->loadResult();
		$params=json_decode($params);
		$sub_options=$params->sub_options;
		$sub_values=$sub_options->sub_values;

		foreach($sub_values as $sv)
			$patterns[]="/".$sv."/";

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		// Initiate cache
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
		$cacheSettings = array( 'memoryCacheSize' => '32MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
		// Set properties
		$objPHPExcel->getProperties()->setCreator("eMundus SAS : http://www.emundus.fr/");
		$objPHPExcel->getProperties()->setLastModifiedBy("eMundus SAS");
		$objPHPExcel->getProperties()->setTitle("eMmundus® Report");
		$objPHPExcel->getProperties()->setSubject("eMmundus® Report");
		$objPHPExcel->getProperties()->setDescription("Report from open source eMundus® plateform : http://www.emundus.fr/");
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle('Ranking');
		$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

		
		$model = new EmundusModelRanking;
		$model->getUsers();
		/*$users=$model->_applicants;
		$profile = EmundusHelperList::getProfiles();
		$col = new EmundusModelRanking;
		$column = $col->getEvalColumns();*/


		$objPHPExcel->setActiveSheetIndex(0);
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');

		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0
		exit;
	}
}
?>