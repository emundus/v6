<?php
require_once("XmlSchema.php");

# credential types : NoAuth // API Key // Bearer Token // Basic Auth // Digest Auth // OAuth 1.0 // OAuth 2.0 // Haw Authentication // AWS Signature // NTLM Authentication // Akamai EdgeGrid

class SoapConnect {
    public function setSoapHeader($xmlTree, $credentials) {
        $headers = array(
            'type' => 'Content-Type: text/xml; charset="utf-8"',
            'content' => 'Content-Length: ' . strlen($xmlTree),
            'accept' => 'Accept: text/xml',
            'cache' => 'Cache-Control: no-cache',
            'pragma' => 'Pragma: no-cache',
            'auth' => 'Authorization: ',
        );

        switch($credentials->auth_type) {
            case 0:
                # not available at this moment
                break;
            case 1:
                # not available at this moment
                break;
            case 2:
                $headers['auth']  =  $headers['auth'] . 'Basic ' . base64_encode($credentials->auth_user.':'.$credentials->auth_pwd);     # basic auth = base64_encode(username:password) - more info: https://developer.mozilla.org/en-US/docs/Web/HTTP/Authentication
                break;
            case 3:
                # not available at this moment
                break;
            default:
                exit;
        }

        return array_values($headers);
    }

    public function webServiceConnect($wsdl,$xmlTree,$credentials) {
        # init connection to web server with CURL
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $wsdl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->setSoapHeader($xmlTree,$credentials));

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlTree);

        switch($credentials->auth_type) {
            case 0:     /// no auth
                break;

            case 2:
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, base64_encode($credentials->auth_user.':'.$credentials->auth_pwd));
                break;
        }

        return $ch;
    }

    public function sendRequest($curl_obj,$fnum) {
        $app = JFactory::getApplication();
        $offset = $app->get('offset', 'UTC');

        $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
        $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
        $now = $dateTime->format('Y-m-d H:i:s');

        /// get fnum info
        require_once(JPATH_SITE.'/components/com_emundus/models/files.php');
        $_mFile = new EmundusModelFiles;
        $fnum_infos = $_mFile->getFnumInfos($fnum);

        # write log file
        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.apogee.php'], JLog::ALL, ['com_emundus']);

        # send request
        try {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            /// execute cURL
            $response = curl_exec($curl_obj);
            $info = curl_getinfo($curl_obj, CURLINFO_HTTP_CODE);

            $doc = new DOMDocument();
            $doc->loadXML($response);
            $faultString = $doc->getElementsByTagName('faultstring');

            if ($response === false || !in_array($info, array(200,201,202,203,204,205,206,207,208,226))) {
                $response_message = $faultString->length > 0 ? $doc->getElementsByTagName('faultstring')->item(0)->nodeValue : "";

                /// insert the status FAILED to table "jos_emundus_apogee_status"
                $data = array(
                    'date_time' => $now,
                    'applicant_id' => $fnum_infos['applicant_id'],
                    'fnum'      => $fnum,
                    'status'    => 0,
                    'params'    => $response_message
                );

                //JLog::add('[emundusApogee] Error when passing data, applicant file number : ' . $fnum . ' at ' . date('Y-m-d H:i:s') . ', error message : ' . $response_message, JLog::ERROR, 'com_emundus');
            } else {
                if($faultString->length == 0) {
                    $data = array(
                        'date_time' => $now,
                        'applicant_id' => $fnum_infos['applicant_id'],
                        'fnum' => $fnum,
                        'status' => 1
                    );
                } else {
                    $data = array(
                        'date_time' => $now,
                        'applicant_id' => $fnum_infos['applicant_id'],
                        'fnum'      => $fnum,
                        'status'    => 0,
                        'params'    => $doc->getElementsByTagName('faultstring')->item(0)->nodeValue
                    );
                    //JLog::add('[emundusApogee] Error when passing data, applicant file number : ' . $fnum . ' at ' . date('Y-m-d H:i:s') . ', error message : ' . $doc->getElementsByTagName('faultstring')->item(0)->nodeValue, JLog::ERROR, 'com_emundus');
                }

            }

        } catch(Exception $e) {

            $data = array(
                'date_time' => $now,
                'applicant_id' => $fnum_infos['applicant_id'],
                'fnum'      => $fnum,
                'status'    => 0,
                'params'    => $e->getMessage()
            );

            //JLog::add('[emundusApogee] Error when fetching data, applicant file number : ' . $fnum . ' at ' . date('Y-m-d H:i:s') . ', error message : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
        }

        finally {
            $query->clear()->insert($db->quoteName('#__emundus_apogee_status'))
                ->columns($db->quoteName(array_keys($data)))
                ->values(implode(',', $db->quote(array_values($data))));

            $db->setQuery($query);
            $db->execute();
        }
    }
}
