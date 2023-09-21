<?php
/**
 * Copyright © Lyra Network.
 * This file is part of Sogecommerce plugin for HikaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL v3)
 */

defined('_JEXEC') or die('Restricted access');

require_once 'sogecommerce_currency.php';
require_once 'sogecommerce_field.php';

if (! class_exists('SogecommerceApi', false)) {

    /**
     * Utility class for managing parameters checking, inetrnationalization, signature building and more.
     */
    class SogecommerceApi
    {

        const ALGO_SHA1 = 'SHA-1';
        const ALGO_SHA256 = 'SHA-256';

        public static $SUPPORTED_ALGOS = array(self::ALGO_SHA1, self::ALGO_SHA256);

        /**
         * The list of encodings supported by the API.
         *
         * @var array[string]
         */
        public static $SUPPORTED_ENCODINGS = array(
            'UTF-8',
            'ASCII',
            'Windows-1252',
            'ISO-8859-15',
            'ISO-8859-1',
            'ISO-8859-6',
            'CP1256'
        );

        /**
         * Generate a trans_id.
         * To be independent from shared/persistent counters, we use the number of 1/10 seconds since midnight
         * which has the appropriatee format (000000-899999) and has great chances to be unique.
         *
         * @param int $timestamp
         * @return string the generated trans_id
         */
        public static function generateTransId($timestamp = null)
        {
            if (! $timestamp) {
                $timestamp = time();
            }

            $parts = explode(' ', microtime());
            $id = ($timestamp + $parts[0] - strtotime('today 00:00')) * 10;
            $id = sprintf('%06d', $id);

            return $id;
        }

        /**
         * Returns an array of languages accepted by the payment gateway.
         *
         * @return array[string][string]
         */
        public static function getSupportedLanguages()
        {
            return array(
                'de' => 'German',
                'en' => 'English',
                'zh' => 'Chinese',
                'es' => 'Spanish',
                'fr' => 'French',
                'it' => 'Italian',
                'ja' => 'Japanese',
                'nl' => 'Dutch',
                'pl' => 'Polish',
                'pt' => 'Portuguese',
                'ru' => 'Russian',
                'sv' => 'Swedish',
                'tr' => 'Turkish'
            );
        }

        /**
         * Returns true if the entered language (ISO code) is supported.
         *
         * @param string $lang
         * @return boolean
         */
        public static function isSupportedLanguage($lang)
        {
            foreach (array_keys(self::getSupportedLanguages()) as $code) {
                if ($code == strtolower($lang)) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Return the list of currencies recognized by the payment gateway.
         *
         * @return array[int][SogecommerceCurrency]
         */
        public static function getSupportedCurrencies()
        {
            $currencies = array(
                array('AUD', '036', 2), array('KHR', '116', 0), array('CAD', '124', 2), array('CNY', '156', 1),
                array('CZK', '203', 2), array('DKK', '208', 2), array('HKD', '344', 2), array('HUF', '348', 2),
                array('INR', '356', 2), array('IDR', '360', 2), array('JPY', '392', 0), array('KRW', '410', 0),
                array('KWD', '414', 3), array('MYR', '458', 2), array('MXN', '484', 2), array('MAD', '504', 2),
                array('NZD', '554', 2), array('NOK', '578', 2), array('PHP', '608', 2), array('RUB', '643', 2),
                array('SGD', '702', 2), array('ZAR', '710', 2), array('SEK', '752', 2), array('CHF', '756', 2),
                array('THB', '764', 2), array('TND', '788', 3), array('GBP', '826', 2), array('USD', '840', 2),
                array('TWD', '901', 2), array('TRY', '949', 2), array('EUR', '978', 2), array('PLN', '985', 2),
                array('BRL', '986', 2)
            );

            $sogecommerce_currencies = array();

            foreach ($currencies as $currency) {
                $sogecommerce_currencies[] = new SogecommerceCurrency($currency[0], $currency[1], $currency[2]);
            }

            return $sogecommerce_currencies;
        }

        /**
         * Return a currency from its 3-letters ISO code.
         *
         * @param string $alpha3
         * @return SogecommerceCurrency
         */
        public static function findCurrencyByAlphaCode($alpha3)
        {
            $list = self::getSupportedCurrencies();
            foreach ($list as $currency) {
                /**
                 * @var SogecommerceCurrency $currency
                 */
                if ($currency->getAlpha3() == $alpha3) {
                    return $currency;
                }
            }

            return null;
        }

        /**
         * Returns a currency form its numeric ISO code.
         *
         * @param int $numeric
         * @return SogecommerceCurrency
         */
        public static function findCurrencyByNumCode($numeric)
        {
            $list = self::getSupportedCurrencies();
            foreach ($list as $currency) {
                /**
                 * @var SogecommerceCurrency $currency
                 */
                if ($currency->getNum() == $numeric) {
                    return $currency;
                }
            }

            return null;
        }

        /**
         * Return a currency from its 3-letters or numeric ISO code.
         *
         * @param string $code
         * @return SogecommerceCurrency
         */
        public static function findCurrency($code)
        {
            $list = self::getSupportedCurrencies();
            foreach ($list as $currency) {
                /**
                 * @var SogecommerceCurrency $currency
                 */
                if ($currency->getNum() == $code || $currency->getAlpha3() == $code) {
                    return $currency;
                }
            }

            return null;
        }

        /**
         * Returns currency numeric ISO code from its 3-letters code.
         *
         * @param string $alpha3
         * @return int
         */
        public static function getCurrencyNumCode($alpha3)
        {
            $currency = self::findCurrencyByAlphaCode($alpha3);
            return ($currency instanceof SogecommerceCurrency) ? $currency->getNum() : null;
        }

        /**
         * Returns an array of card types accepted by the payment gateway.
         *
         * @return array[string][string]
         */
        public static function getSupportedCardTypes()
        {
            return array(
                'CB' => 'CB', 'E-CARTEBLEUE' => 'e-Carte Bleue', 'MAESTRO' => 'Maestro', 'MASTERCARD' => 'Mastercard',
                'VISA' => 'Visa', 'VISA_ELECTRON' => 'Visa Electron', 'VPAY' => 'V PAY', 'AMEX' => 'American Express',
                'APETIZ' => 'Apetiz', 'AURORE-MULTI' => 'Cpay Aurore', 'CHQ_DEJ' => 'Chèque Déjeuner', 'CONECS' => 'Conecs',
                'CVCO' => 'Chèque-Vacances Connect', 'EDENRED' => 'Ticket Restaurant', 'EDENRED_EC' => 'Ticket EcoCheque',
                'EDENRED_TR' => 'Ticket Restaurant', 'E_CV' => 'e-Chèque-Vacances', 'FRANFINANCE_3X' => 'Paiement en 3 fois',
                'FRANFINANCE_4X' => 'Paiement en 4 fois', 'FULLCB3X' => 'Paiement en 3 fois CB', 'ILLICADO' => 'Carte Illicado',
                'ILLICADO_SB' => 'Carte Illicado (sandbox)', 'JCB' => 'JCB', 'ONEY_3X_4X' => 'Paiement en 3 ou 4 fois Oney',
                'ONEY_ENSEIGNE' => 'Cartes enseignes Oney', 'PAYLIB' => 'Paylib', 'PAYPAL' => 'PayPal', 'PAYPAL_SB' => 'PayPal Sandbox',
                'PICWIC' => 'Carte Picwic', 'PICWIC_SB' => 'Carte Picwic (sandbox)', 'SDD' => 'Prélèvement SEPA',
                'SODEXO' => 'Pass Restaurant'
            );
        }

        /**
         * Return the statuses list of finalized successful payments (authorized or captured).
         * @return array
         */
        public static function getSuccessStatuses()
        {
            return array(
                'AUTHORISED',
                'AUTHORISED_TO_VALIDATE', // TODO is this a pending status?
                'CAPTURED',
                'ACCEPTED'
            );
        }

        /**
         * Return the statuses list of payments that are waiting confirmation (successful but
         * the amount has not been transfered and is not yet guaranteed).
         * @return array
         */
        public static function getPendingStatuses()
        {
            return array(
                'INITIAL',
                'WAITING_AUTHORISATION',
                'WAITING_AUTHORISATION_TO_VALIDATE',
                'UNDER_VERIFICATION',
                'PRE_AUTHORISED',
                'WAITING_FOR_PAYMENT'
            );
        }

        /**
         * Return the statuses list of payments interrupted by the buyer.
         * @return array
         */
        public static function getCancelledStatuses()
        {
            return array('ABANDONED');
        }

        /**
         * Return the statuses list of payments waiting manual validation from the gateway Back Office.
         * @return array
         */
        public static function getToValidateStatuses()
        {
            return array('WAITING_AUTHORISATION_TO_VALIDATE', 'AUTHORISED_TO_VALIDATE');
        }

        /**
         * Compute the signature. Parameters must be in UTF-8.
         *
         * @param array[string][string] $parameters payment gateway request/response parameters
         * @param string $key shop certificate
         * @param string $algo signature algorithm
         * @param boolean $hashed set to false to get the unhashed signature
         * @return string
         */
        public static function sign($parameters, $key, $algo, $hashed = true)
        {
            ksort($parameters);

            $sign = '';
            foreach ($parameters as $name => $value) {
                if (substr($name, 0, 5) == 'vads_') {
                    $sign .= $value . '+';
                }
            }

            $sign .= $key;

            if (! $hashed) {
                return $sign;
            }

            switch ($algo) {
                case self::ALGO_SHA1:
                    return sha1($sign);
                case self::ALGO_SHA256:
                    return base64_encode(hash_hmac('sha256', $sign, $key, true));
                default:
                    throw new \InvalidArgumentException("Unsupported algorithm passed : {$algo}.");
            }
        }

        /**
         * Get current PHP version without build info.
         * @return string
         */
        public static function shortPhpVersion()
        {
            $version = PHP_VERSION;

            $match = array();
            if (preg_match('#^\d+(\.\d+)*#', $version, $match) === 1) {
                $version = $match[0];
            }

            return $version;
        }

        /**
         * Format a given list of e-mails separated by commas and render them as HTML links.
         * @param string $emails
         * @return string
         */
        public static function formatSupportEmails($emails)
        {
            $formatted = '';

            $parts = explode(', ', $emails);
            foreach ($parts as $part) {
                $elts = explode(':', $part);
                if (count($elts) === 2) {
                    $label = trim($elts[0]) . ': ';
                    $email = $elts[1];
                } elseif (count($elts) === 1) {
                    $label = '';
                    $email = $elts[0];
                } else {
                    throw new \InvalidArgumentException("Invalid support e-mails string passed: {$emails}.");
                }

                $email = trim($email);

                if (! empty($formatted)) {
                    $formatted .= '<br />';
                }

                $formatted .= $label . '<a href="mailto:' . $email . '">' . $email . '</a>';
            }

            return $formatted;
        }
    }
}