<?php
/**
 * PDF Document class
 *
 * @package     Joomla
 * @subpackage  Fabrik.Documents
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

namespace Joomla\CMS\Document;

defined('JPATH_PLATFORM') or die;

//require_once JPATH_SITE . '/components/com_fabrik/helpers/pdf.php';

use Fabrik\Helpers\Pdf;

jimport('joomla.utilities.utility');

/**
 * PdfDocument class, provides an easy interface to parse and display a PDF document
 *
 * @since  11.1
 */
class PdfDocument extends HtmlDocument
{
	/**
	 * Array of Header `<link>` tags
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $_links = array();

	/**
	 * Array of custom tags
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $_custom = array();

	/**
	 * Name of the template
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $template = null;

	/**
	 * Base url
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $baseurl = null;

	/**
	 * Array of template parameters
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $params = null;

	/**
	 * File name
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $_file = null;

	/**
	 * String holding parsed template
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_template = '';

	/**
	 * Array of parsed template JDoc tags
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $_template_tags = array();

	/**
	 * Integer with caching setting
	 *
	 * @var    integer
	 * @since  11.1
	 */
	protected $_caching = null;

	/**
	 * Set to true when the document should be output as HTML5
	 *
	 * @var    boolean
	 * @since  12.1
	 *
	 * @note  4.0  Will be replaced by $html5 and the default value will be true.
	 */
	private $_html5 = null;

	/**
	 * Fabrik config
	 *
	 * @var null
	 */
	protected $config = null;

	/**
	 * Orientation
	 *
	 * @var  string
	 */
	private $orientation = 'P';

	/**
	 * Paper size
	 *
	 * @var  string
	 */
	private $size = 'A4';

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Associative array of options
	 *
	 * @since   11.1
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		$this->config = \JComponentHelper::getParams('com_fabrik');
		if ($this->config->get('pdf_debug', false))
		{
			$this->setMimeEncoding('text/html');
			$this->_type = 'pdf';
		}
		else
		{
			// Set mime type
			$this->_mime = 'application/pdf';

			// Set document type
			$this->_type = 'pdf';
		}

		$this->iniPdf();
	}

	/**
	 * Init selected PDF
	 */
	protected function iniPdf()
	{
		if ($this->config->get('fabrik_pdf_lib', 'dompdf') === 'dompdf')
		{
			if (!$this->iniDomPdf())
			{
				throw new RuntimeException(FText::_('COM_FABRIK_NOTICE_DOMPDF_NOT_FOUND'));
			}
		}
	}

	/**
	 * Set up DomPDF engine
	 *
	 * @return  bool
	 */
	protected function iniDomPdf()
	{
		$this->engine = Pdf::iniDomPdf(true);

		return $this->engine;
	}

	/**
	 * Set the paper size and orientation
	 * Note if too small for content then the pdf renderer will bomb out in an infinite loop
	 * Legal seems to be more lenient than a4 for example
	 * If doing landscape set large paper size
	 *
	 * @param   string  $size         Paper size E.g A4,legal
	 * @param   string  $orientation  Paper orientation landscape|portrait
	 *
	 * @since 3.0.7
	 *
	 * @return  void
	 */
	public function setPaper($size = 'A4', $orientation = 'landscape')
	{
		if ($this->config->get('fabrik_pdf_lib', 'dompdf') === 'dompdf')
		{
			$size = strtoupper($size);
			$this->engine->set_paper($size, $orientation);
		}
		else
		{
			$this->size = ucfirst($size);

			switch ($orientation)
			{
				case 'landscape':
					$this->orientation = 'L';
					$this->size .= '-' . $this->orientation;
					break;
				case 'portrait':
				default:
					$this->orientation = 'P';
					break;
			}
		}
	}

	/**
	 * Sets the document name
	 *
	 * @param   string  $name  Document name
	 *
	 * @return  void
	 */
	public function setName($name = 'joomla')
	{
		$this->name = $name;
	}

	/**
	 * Returns the document name
	 *
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}

    /**
     * Render the document.
     *
     * @param   boolean  $cache   If true, cache the output
     * @param   array    $params  Associative array of attributes
     *
     * @return	string
     */
	public function render($cache = false, $params = array())
	{
		// mb_encoding foo when content-type had been set to text/html; uft-8;
		$this->_metaTags['http-equiv'] = array();
		$this->_metaTags['http-equiv']['content-type'] = 'text/html';

		// Testing using futural font.
		// $this->addStyleDeclaration('body: { font-family: futural !important; }');

		$data = parent::render();

		Pdf::fullPaths($data);

		/**
		 * I think we need this to handle some HTML entities when rendering otherlanguages (like Polish),
		 * but haven't tested it much
		 */
		$data = mb_convert_encoding($data,'HTML-ENTITIES','UTF-8');
		$config = \JComponentHelper::getParams('com_fabrik');

		if ($this->config->get('fabrik_pdf_lib', 'dompdf') === 'dompdf')
		{
			$this->engine->load_html($data);

			if ($config->get('pdf_debug', false))
			{
				return $this->engine->output_html();
			}
			else
			{
				$this->engine->render();
				$this->engine->stream($this->getName() . '.pdf');
			}
		}
		else
		{
			if ($config->get('pdf_debug', false))
			{
				return $data;
			}
			else
			{
				try
				{
					$mpdf = new \Mpdf\Mpdf(
						[
							'tempDir'     => \JFactory::getConfig()->get('tmp_path', JPATH_ROOT . '/tmp'),
							'mode'        => 'utf-8',
							'format'      => $this->size,
							'orientation' => $this->orientation
						]
					);
					//$mpdf->shrink_tables_to_fit = 1;
					$mpdf->use_kwt = true;
					$mpdf->WriteHTML($data);
					$mpdf->Output($this->getName() . '.pdf', \Mpdf\Output\Destination::INLINE);
				}
				catch (\Mpdf\MpdfException $e)
				{
					// mmmphh
					echo 'Error creating PDF: ' . ($e->getMessage());
				}
			}
		}

		return '';
	}

	/**
	 * Get the contents of a document include
	 *
	 * @param   string  $type     The type of renderer
	 * @param   string  $name     The name of the element to render
	 * @param   array   $attribs  Associative array of remaining attributes.
	 *
	 * @return  The output of the renderer
	 */

	public function getBuffer($type = null, $name = null, $attribs = array())
	{
		if ($type == 'head' || $type == 'component')
		{
			return parent::getBuffer($type, $name, $attribs);
		}
		else
		{
			return '';
		}
	}
}
