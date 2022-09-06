<?php

class modEmundusPaymentHelper
{
    /**
     * @param $fnum
     * @return bool
     */
    public function doINeedToPay($fnum): bool
    {
        $doINeedToPay = true;

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('jespbc.*')
            ->from('#__emundus_setup_payments_by_campaign as jespbc')
            ->leftJoin('#__emundus_setup_payments_by_campaign_repeat_campaign_id as jespbcr ON jespbcr.parent_id = jespbc.id')
            ->leftJoin('#__emundus_campaign_candidature as jec ON jec.campaign_id = jespbcr.campaign_id')
            ->where('jec.fnum = ' . $db->quote($fnum));

        $db->setQuery($query);
        try {
            $payment = $db->loadObject();
        } catch (Exception $e) {
            JLog::add('Error getting payment infos from fnum ('. $fnum .')', JLog::ERROR, 'com_emundus_payment');
        }

        if (empty($payment) || empty($payment->id)) {
            $doINeedToPay = false;
        }

        return $doINeedToPay;
    }

    /**
     * @param $fnum
     * @return bool
     */
    public function didIAlreadyPay($fnum): bool
    {
        $didIAlreadyPay = false;

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('jho.order_id, jho.order_status')
            ->from('#__hikashop_order as jho')
            ->leftJoin('#__emundus_hikashop as jeh ON jeh.order_id = jho.order_id')
            ->where('jeh.fnum = ' . $db->quote($fnum));

        $db->setQuery($query);

        try {
            $order = $db->loadObject();
        } catch (Exception $e) {
            JLog::add('Error getting order infos from fnum ('. $fnum .') : '. $e, JLog::ERROR, 'com_emundus_payment');
        }

        if (!empty($order) && $order->order_status === 'confirmed') {
            $didIAlreadyPay = true;
        }

        return $didIAlreadyPay;
    }

    /**
     * Check if I've already tried to pay for this file, and return infos about the payment if I did
     * @param $fnum
     * @return stdClass|null
     */
    public function didIStartPayment($fnum)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('jeh.*, jho.order_status, jho.order_type')
            ->from('#__emundus_hikashop as jeh')
            ->leftJoin('#__hikashop_order as jho ON jho.order_id = jeh.order_id')
            ->where('jeh.fnum = ' . $db->quote($fnum));

        $db->setQuery($query);

        try {
            $payment = $db->loadObject();
        } catch (Exception $e) {
            JLog::add('Error getting payment infos from fnum ('. $fnum .') : '. $e, JLog::ERROR, 'com_emundus_payment');
        }

        return $payment;
    }

    /**
     * @param $payment
     * @return boolean
     */
    public function waitForValidation($payment): bool
    {
        $wait = false;

        if ($payment->orderStatus == 'confirmed') {
            return true;
        }

        switch ($payment->order_type) {
            case 'flywire':
                $params = json_decode($payment->params, true);
                if ($params['initiator'] == 'flywire' && $params['flywire_status'] != 'cancelled') {
                    $wait = true;
                }
                break;
            default:
                break;
        }

        return $wait;
    }

    /**
     * Detect if student is a scholarship student
     * @param $fnum string
     * @return bool
     */
    public function isScholarshipStudent($fnum): bool
    {
        require_once JPATH_ROOT . '/components/com_emundus/models/payment.php';
        $m_payment = new EmundusModelPayment();

        return $m_payment->isScholarshipStudent($fnum);
    }

    /**
     * @return bool
     */
    public function doesScholarshipHoldersNeedToPay(): bool
    {
        $params	= JComponentHelper::getParams('com_emundus');
        return $params->get('pay_scholarship', 0) == 1;
    }

    /**
     * Get payment informations from fnum
     * @param $fnum string
     * @return object|false|null
     */
    public static function getPaymentInfos($fnum)
    {
        $payment = false;

        if (!empty($fnum)) {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);

            $query->select('jespbc.*, jec.campaign_id')
                ->from('#__emundus_setup_payments_by_campaign AS jespbc')
                ->leftJoin('#__emundus_setup_payments_by_campaign_repeat_campaign_id as jespbcr ON jespbcr.parent_id = jespbc.id')
                ->leftJoin('#__emundus_campaign_candidature as jec ON jec.campaign_id = jespbcr.campaign_id')
                ->where('jec.fnum = ' . $db->quote($fnum));

            $db->setQuery($query);

            try {
                $payment = $db->loadObject();
            } catch (Exception $e) {
                JLog::add('Error getting payment infos from fnum ('. $fnum .') : '. $e, JLog::ERROR, 'com_emundus_payment');
            }
        } else {
            JLog::add('Error getting payment infos from fnum : fnum is empty', JLog::WARNING, 'com_emundus_payment');
        }

        return $payment;
    }

    /**
     * Get product data from product_id
     * @param $product_id int
     * @return object|false|null
     */
    public static function getProduct($product_id)
    {
        $product = false;

        if (!empty($product_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('*')
                ->from('#__hikashop_product')
                ->where($db->quoteName('product_id') . ' = ' . $db->quote($product_id));
            $db->setQuery($query);

            try {
                $product = $db->loadObject();
            } catch (Exception $e) {
                JLog::add('Error getting product ('. $product_id .') : '. $e, JLog::ERROR, 'com_emundus_payment');
            }
        } else {
            JLog::add('Error getting product : product_id is empty', JLog::WARNING, 'com_emundus_payment');
        }

        return $product;
    }

    /**
     * Get campaign data from $campaign_id
     * @param $campaign_id int
     * @return object|false|null
     */
    public function getCampaign($campaign_id)
    {
        $campaign = false;

        if (!empty($campaign_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('*')
                ->from('#__emundus_setup_campaigns')
                ->where($db->quoteName('id') . ' = ' . $db->quote($campaign_id));
            $db->setQuery($query);

            try {
                $campaign = $db->loadObject();
            } catch (Exception $e) {
                JLog::add('Error getting campaign ('. $campaign_id .') : '. $e, JLog::ERROR, 'com_emundus_payment');
            }
        } else {
            JLog::add('Error getting campaign : campaign_id is empty', JLog::WARNING, 'com_emundus_payment');
        }

        return $campaign;
    }

    /**
     * Get country code ISO 2 from table data_country
     * @return array
     */
    public static function getCountryCodes(): array
    {
        $countries = [];

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from('data_country');

        $db->setQuery($query);

        try {
            $countries = $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('Error getting countries : '. $e, JLog::ERROR, 'com_emundus_payment');
        }

        return $countries;
    }

    public function getFlywireConfig($fnum)
    {
        require_once JPATH_ROOT . '/components/com_emundus/models/payment.php';
        $m_payment = new EmundusModelPayment();
        $config = $m_payment->getConfig($fnum);

        return $config;
    }

    /**
     * @param $fnum
     * @return mixed|string|null
     */
    public function getFilePaymentStatus($fnum)
    {
        $status = 'unknown';
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('jho.status')
            ->from('#__hikashop_order as jho')
            ->leftJoin('#__emundus_hikashop as jeh ON jeh.order_id = jho.order_id')
            ->where('jeh.fnum = ' . $db->quote($fnum));

        $db->setQuery($query);

        try {
            $status = $db->loadResult();
        } catch (Exception $e) {
            JLog::add('Error getting file payment status from fnum ('. $fnum .') : '. $e, JLog::ERROR, 'com_emundus_payment');
        }

        return $status;
    }

    public function getAttachmentLabelFromId($attachmentId): string
    {
        $lbl = '';

        if (!empty($attachmentId)) {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);

            $query->select('lbl')
                ->from('jos_emundus_setup_attachments')
                ->where('id = ' . $attachmentId);

            $db->setQuery($query);

            try {
                $lbl = $db->loadResult();
            } catch (Exception $e) {
                $lbl = '';
                JLog::add('Error getting attachment lbl : ' . $e->getMessage(), JLog::ERROR, 'com_emundus_payment');
            }
        }

        return $lbl;
    }

    public function getAttachmentAllowedExtTypes($attachmentId): array
    {
        $ext = array();

        if (!empty($attachmentId)) {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);

            $query->select('allowed_types')
                ->from('jos_emundus_setup_attachments')
                ->where('id = ' . $attachmentId);

            $db->setQuery($query);

            try {
                $allowed_types = $db->loadResult();
            } catch (Exception $e) {
                $allowed_types = '';
                JLog::add('Error getting attachment ext : ' . $e->getMessage(), JLog::ERROR, 'com_emundus_payment');
            }

            if (!empty($allowed_types)) {
                $types = explode(';', $allowed_types);

                foreach ($types as $type) {
                    $ext[$type] = $this->get_mime_type('test.' . $type);
                }
            }
        }

        return $ext;
    }

    function get_mime_type($filename) {
        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else return 'application/octet-stream';
    }
}