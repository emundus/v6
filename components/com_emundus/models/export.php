﻿<?php
/**
 * Export  Model for eMundus Component
 * 
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */
 
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );

//client api for file conversion
use TheCodingMachine\Gotenberg\Client;
use TheCodingMachine\Gotenberg\ClientException;
use TheCodingMachine\Gotenberg\DocumentFactory;
use TheCodingMachine\Gotenberg\OfficeRequest;
use TheCodingMachine\Gotenberg\HTMLRequest;
use TheCodingMachine\Gotenberg\Request;
use TheCodingMachine\Gotenberg\RequestException;
use GuzzleHttp\Psr7\LazyOpenStream;
 
class EmundusModelExport extends JModelList {

	var $_db = null;
    var $_user = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct(){
		parent::__construct();
        $this->_db = JFactory::getDBO();
        $this->_user = JFactory::getSession()->get('emundusUser');

        JLog::addLogger(['text_file' => 'com_emundus.export.php'], JLog::ERROR);
	}

    /*
    * 	export file to PDF
    *	@param file_src 		path to file source
    *	@param file_dest 		path to file dest
    *	@param file_src_format 	default office, html for other
    *	@param fnum 		    Application file number
    * 	@return Object
    */
	function toPdf($file_src, $file_dest, $file_src_format = null, $fnum = null)
    {
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $gotenberg_activation = $eMConfig->get('gotenberg_activation', 0);
        $gotenberg_url = $eMConfig->get('gotenberg_url', 'http://localhost:3000');

        $res = new stdClass();

        if ($gotenberg_activation != 1) {
            $res->status = false;
            $res->msg = JText::_('COM_EMUNDUS_ERROR_EXPORT_API_DESACTIVATED');
            return json_encode($res);
        }

        $user_id = !empty($fnum) ? (int)substr($fnum, -7) : null;

        if(EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $fnum))
        {
            require JPATH_LIBRARIES . '/emundus/vendor/autoload.php';

            # create the client.
            //$client = new Client($gotenberg_url, new \Http\Adapter\Guzzle6\Client());
            # ... or the following if you want the client to discover automatically an installed implementation of the PSR7 `HttpClient`.
            //$client = new Client($gotenberg_url);

            # prepare the files required for your conversion.
            # from a path.
            //$index = DocumentFactory::makeFromPath('index.html', '/path/to/file');
            # ... or from your own stream.
            //$stream = new LazyOpenStream('/path/to/file', 'r');
            //$index = DocumentFactory::makeFromStream('Template.doc', $stream);
            // ... or from a string.
            //$index = DocumentFactory::makeFromString('test.html', '<html>Foo</html>');
/*
            $header = DocumentFactory::makeFromPath('header.html', '/path/to/file');
            $footer = DocumentFactory::makeFromPath('footer.html', '/path/to/file');
            $assets = [
                DocumentFactory::makeFromPath('style.css', '/path/to/file'),
                DocumentFactory::makeFromPath('img.png', '/path/to/file'),
            ];
*/
            ///
            $src = $file_src;
            $dest = $file_dest;

            $client = new Client($gotenberg_url, new \Http\Adapter\Guzzle6\Client());
            $files = [
                DocumentFactory::makeFromPath($file_src, $src),
            ];

            try {
                if ($file_src_format != 'html') {
                    //Office
                    $request = new OfficeRequest($files);
                } else {
                    // HTML
                    // @todo define parts of html source (header, footer, body)
                    $header = '';
                    $footer = '';
                    $assets = '';
                    $request = new HTMLRequest($src);
                    $request->setHeader($header);
                    $request->setFooter($footer);
                    $request->setAssets($assets);
                    $request->setPaperSize(Request::A4);
                    $request->setMargins(Request::NO_MARGINS);
                    $request->setScale(0.75);
                }
                
                # store method allows you to... store the resulting PDF in a particular destination.
                $client->store($request, $dest);
                
                # if you wish to redirect the response directly to the browser, you may also use:
                $client->post($request);          
            } catch (RequestException $e) {
                # this exception is thrown if given paper size or margins are not correct.
                $res->status = false;
                $res->msg = JText::_('COM_EMUNDUS_ERROR_EXPORT_MARGIN');
                JLog::add($res->msg, JLog::ERROR, 'com_emundus.export');
                return json_encode($res);
            } catch (ClientException $e) {
                # this exception is thrown by the client if the API has returned a code != 200.
                $res->status = false;
                $res->msg = JText::_('COM_EMUNDUS_ERROR_EXPORT_API');
                JLog::add($res->msg, JLog::ERROR, 'com_emundus.export');
                return $res;
            }

            $res->status = true;
            $res->msg = '<a href="'.$dest.'" target="_blank">'.$dest.'</a>';
            return $res;
        }
        else
        {
            $res->status = false;
            $res->msg = JText::_('ACCESS_DENIED');
            JLog::add($res->msg, JLog::ERROR, 'com_emundus.export');
            return $res;
        }
    }
}