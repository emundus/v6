<?php
/**
 * Legacy Fabrik 3.5 Fabrik\Helpers\Html FabrikHelperHTML
 *
 * @package     Joomla
 * @subpackage  Fabrik.helpers
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class_alias('Fabrik\Document\Renderer\Pdf\ComponentRenderer', 'JDocumentRendererPdfComponent');
class_alias('Fabrik\Document\Renderer\Pdf\HeadRenderer', 'JDocumentRendererPdfHead');
class_alias('Fabrik\Document\Renderer\Pdf\MessageRenderer', 'JDocumentRendererPdfMessage');
class_alias('Fabrik\Document\Renderer\Pdf\ModuleRenderer', 'JDocumentRendererPdfModule');
class_alias('Fabrik\Document\Renderer\Pdf\ModulesRenderer', 'JDocumentRendererPdfModules');
class_alias('Fabrik\Document\PdfDocument', 'JDocumentPdf');
class_alias('Fabrik\Document\Renderer\Partial\ComponentRenderer', 'JDocumentRendererPartialComponent');
class_alias('Fabrik\Document\Renderer\Partial\HeadRenderer', 'JDocumentRendererPartialHead');
class_alias('Fabrik\Document\Renderer\Partial\MessageRenderer', 'JDocumentRendererPartialMessage');
class_alias('Fabrik\Document\Renderer\Partial\ModuleRenderer', 'JDocumentRendererPartialModule');
class_alias('Fabrik\Document\Renderer\Partial\ModulesRenderer', 'JDocumentRendererPartialModules');
class_alias('Fabrik\Document\PartialDocument', 'JDocumentPartial');
class_alias('Fabrik\Helpers\Worker', 'FabrikWorker');
class_alias('Fabrik\Helpers\Pdf', 'FabrikPDFHelper');
class_alias('Fabrik\Helpers\ArrayHelper', 'FArrayHelper');
class_alias('Fabrik\Helpers\StringHelper', 'FabrikString');
class_alias('Fabrik\Helpers\Element', 'FabrikHelperElement');
class_alias('Fabrik\Helpers\Html', 'FabrikHelperHTML');
//class_alias('Fabrik\Helpers\LayoutFile', 'FabrikLayoutFile');
class_alias('Fabrik\Helpers\Image', 'FabimageHelper');
//class_alias('Fabrik\Helpers\Pagination', 'FPagination');
class_alias('Fabrik\Helpers\Googlemap', 'FabGoogleMapHelper');

if (file_exists(JPATH_LIBRARIES . '/fabrik/fabrik/Helpers/Custom.php'))
{
	class_alias('Fabrik\Helpers\Custom', 'FabrikCustom');
}