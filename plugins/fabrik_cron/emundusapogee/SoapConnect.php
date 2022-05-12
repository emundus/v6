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
        /// get fnum info
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'files.php');
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
            curl_exec($curl_obj);
            $info = curl_getinfo($curl_obj, CURLINFO_HTTP_CODE);

            if(false === curl_exec($curl_obj) || $info !== 200) {
                /// insert the status FAILED to table "jos_emundus_apogee_status"
                $data = array(
                    'date_time' => date('Y-m-d H:i:s'),
                    'applicant_id' => $fnum_infos['applicant_id'],
                    'fnum'      => $fnum,
                    'status'    => 0
                );
                JLog::add('# Error when passing data to APOGEE server, cURL exec fail or HTTP status is not 200 ' . date('Y-m-d H:i:s'), JLog::ERROR, 'com_emundus');
            } else {
                $data = array(
                    'date_time' => date('Y-m-d H:i:s'),
                    'applicant_id' => $fnum_infos['applicant_id'],
                    'fnum'      => $fnum,
                    'status'    => 1
                );

            }

        } catch(Exception $e) {

            $data = array(
                'date_time' => date('Y-m-d H:i:s'),
                'applicant_id' => $fnum_infos['applicant_id'],
                'fnum'      => $fnum,
                'status'    => 0
            );

            JLog::add('# Error when passing data to APOGEE server, error message : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
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