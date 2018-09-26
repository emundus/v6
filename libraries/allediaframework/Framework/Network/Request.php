<?php
/**
 * @package   AllediaFramework
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC., All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework\Network;

defined('_JEXEC') or die();

class Request
{
    /**
     * post
     * POST request
     *
     * @access public
     *
     * @param string $url
     * @param array  $data
     *
     * @return string
     */
    public function post($url, $data = array())
    {
        if ($this->hasCURL()) {
            return $this->postCURL($url, $data);

        } else {
            return $this->postFOpen($url, $data);
        }
    }

    /**
     * hasCURL
     * Does the server have the curl extension ?
     *
     * @access protected
     * @return boolean
     */
    protected function hasCURL()
    {
        return function_exists('curl_init');
    }

    /**
     * postCURL
     * POST request with curl
     *
     * @access protected
     *
     * @param string $url
     * @param array  $data
     *
     * @return string
     */
    protected function postCURL($url, $data = array())
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $contents = curl_exec($ch);
        curl_close($ch);

        return $contents;
    }

    /**
     * postFOpen
     * POST request with fopen
     *
     * @access protected
     *
     * @param string $url
     * @param array  $data
     *
     * @return string
     */
    protected function postFOpen($url, $data = array())
    {
        $stream = fopen($url, 'r', false, stream_context_create(array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query(
                    $data
                )
            )
        )));

        $contents = stream_get_contents($stream);
        fclose($stream);

        return $contents;
    }
}
