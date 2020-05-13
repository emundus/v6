<?php
/**
 * @version     $Id: export.php 750 2020-05-05 22:29:38Z brivalland $
 * @package     Joomla
 * @copyright   (C) 2020 eMundus LLC. All rights reserved.
 * @license     GNU General Public License
 */

// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( JText::_('RESTRICTED_ACCESS') );
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');

//client api for file conversion
use TheCodingMachine\Gotenberg\Client;
use TheCodingMachine\Gotenberg\ClientException;
use TheCodingMachine\Gotenberg\DocumentFactory;
use TheCodingMachine\Gotenberg\OfficeRequest;
use TheCodingMachine\Gotenberg\HTMLRequest;
use TheCodingMachine\Gotenberg\Request;
use TheCodingMachine\Gotenberg\RequestException;
use GuzzleHttp\Psr7\LazyOpenStream;

/**
 * Custom report controller
 * @package     Emundus
 */
class EmundusControllerExport extends JControllerLegacy
{
    public function display($cachable = false, $urlparams = false) {
        // Set a default view if none exists
        if ( ! JRequest::getCmd( 'view' ) ) {
            $default = 'application_form';
            JRequest::setVar('view', $default );
        }
        parent::display();
    }

    public function to_pdf()
    {
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);

        $user_id = (int)substr($fnum, -7);
        $dir = EMUNDUS_PATH_ABS.$user_id.DS;
        $res = new stdClass();

        if(EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $fnum))
        {
            require JPATH_LIBRARIES . '/emundus/vendor/autoload.php';

            # create the client.
            $client = new Client('http://localhost:3000', new \Http\Adapter\Guzzle6\Client());
            # ... or the following if you want the client to discover automatically an installed implementation of the PSR7 `HttpClient`.
            //$client = new Client('http://localhost:3000');

            # prepare the files required for your conversion.

            # from a path.
            //$index = DocumentFactory::makeFromPath('index.html', '/home/deploy/dev/images/emundus/files');
            # ... or from your own stream.
            //$stream = new LazyOpenStream('/home/deploy/dev/images/emundus/files', 'r');
            //$index = DocumentFactory::makeFromStream('Template.doc', $stream);
            // ... or from a string.
            //$index = DocumentFactory::makeFromString('test.html', '<html>Foo</html>');

            /*$header = DocumentFactory::makeFromPath('header.html', '/path/to/file');
            $footer = DocumentFactory::makeFromPath('footer.html', '/path/to/file');
            $assets = [
                DocumentFactory::makeFromPath('style.css', '/path/to/file'),
                DocumentFactory::makeFromPath('img.png', '/path/to/file'),
            ];
*/ 

            ///


            $client = new Client('http://localhost:3000', new \Http\Adapter\Guzzle6\Client());
            $files = [
                DocumentFactory::makeFromPath('@Template.docx', JPATH_ROOT.DS.'images'.DS.'emundus'.DS.'letters'.DS.'Template.docx'),
            ];

            ///
            try {
                //Office
                $request = new OfficeRequest($files);

                // HTML
                //$request = new HTMLRequest($index);
                //$request->setHeader($header);
                //$request->setFooter($footer);
                //$request->setAssets($assets);
                //$request->setPaperSize(Request::A4);
                //$request->setMargins(Request::NO_MARGINS);
                //$request->setScale(0.75);

                $dest = JPATH_ROOT.DS.'images'.DS.'emundus'.DS.'test.pdf';
                
                # store method allows you to... store the resulting PDF in a particular destination.
                $client->store($request, $dest);
                
                # if you wish to redirect the response directly to the browser, you may also use:
                $client->post($request);          
            } catch (RequestException $e) {
                # this exception is thrown if given paper size or margins are not correct.
                $res->status = false;
                $res->msg = JText::_('COM_EMUNDUS_ERROR_EXPORT_MARGIN');
                echo json_encode($res);
                exit();
            } catch (ClientException $e) {
                # this exception is thrown by the client if the API has returned a code != 200.
                $res->status = false;
                $res->msg = JText::_('COM_EMUNDUS_ERROR_EXPORT_API');
                echo json_encode($res);
                exit();
            }

            $res->status = true;
            $res->msg = '<a href="images/emundus/test.pdf" target="_blank">/images/emundus/test.pdf</a>';
            
            echo json_encode($res);
            exit();
        }
        else
        {
            $res->status = false;
            $res->msg = JText::_('ACCESS_DENIED');
            echo json_encode($res);
            exit();
        }
    }
}
