<?php
/**
* Modelo Securitychecks para el Componente Securitycheck Pro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );
/**
* Modelo Securitycheck
*/
class SecuritycheckprosModelSecuritycheckpro extends JModelLegacy
{

function __construct()
{
	parent::__construct();

	$array = JRequest::getVar('product');
	$this->setProduct($array);
}

function setProduct($product)
	{
		// Establecemos el nombre del producto y los datos a "null"
		$this->_product		= $product;
		$this->_data	= null;		
	}

/**
 * Método para cargar todas las vulnerabilidades de los componentes
 */
function &getData()
{
	// Cargamos los datos
	if (empty( $this->_data )) {
		$this->_product = filter_var($this->_product, FILTER_SANITIZE_STRING);
		$query = ' SELECT * FROM #__securitycheckpro_db '.
				'  WHERE id IN (SELECT vuln_id FROM #__securitycheckpro_vuln_components WHERE Product = "'.$this->_product .'")';			
		$this->_db->setQuery( $query );
		$this->_data = $this->_db->loadAssocList();			
	}	
	return $this->_data;
}
}