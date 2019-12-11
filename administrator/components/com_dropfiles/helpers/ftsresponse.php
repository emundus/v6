<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien BarrÃ¨re (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

// no direct access
defined('_JEXEC') || die;

/**
 * Class wpfdHelperResponse
 */
class FtsHelperResponse
{

    /**
     * Response array
     *
     * @var array
     */
    protected $ftsResponse = array();

    /**
     * Return console message
     *
     * @param string $msg Message
     *
     * @return void
     * @since  version
     */
    public function console($msg)
    {
        $this->ftsResponse[] = array('cn', $msg);
    }

    /**
     * Return alert message
     *
     * @param string $msg Alert message
     *
     * @return void
     * @since  version
     */
    public function alert($msg)
    {
        $this->ftsResponse[] = array('al', $msg);
    }

    /**
     * Return assign message
     *
     * @param string $id   Id
     * @param string $data Data
     *
     * @return void
     * @since  version
     */
    public function assign($id, $data)
    {
        $this->ftsResponse[] = array('as', $id, $data);
    }

    /**
     * Return a redirect
     *
     * @param string  $url   URL to redirect
     * @param integer $delay Delay time before redirect
     *
     * @return void
     * @since  version
     */
    public function redirect($url = '', $delay = 0)
    {
        $this->ftsResponse[] = array('rd', $url, $delay);
    }

    /**
     * Send reload signal
     *
     * @return void
     * @since  version
     */
    public function reload()
    {
        $this->ftsResponse[] = array('rl');
    }

    /**
     * Return a script to execution
     *
     * @param string $script Script
     *
     * @return void
     * @since  version
     */
    public function script($script = '')
    {
        $this->ftsResponse[] = array('js', $script);
    }

    /**
     * Return a variable
     *
     * @param string $var   Var name
     * @param string $value Var value
     *
     * @return void
     * @since  version
     */
    public function variable($var, $value)
    {
        $this->ftsResponse[] = array('vr', $var, $value);
    }

    /**
     * Set response
     *
     * @param array $a Response array
     *
     * @return void
     * @since  version
     */
    public function setResponse($a)
    {
        $this->ftsResponse = $a;
    }

    /**
     * Return json response
     *
     * @return mixed|string
     * @since  version
     */
    public function getJSON()
    {
        //@ini_set('error_reporting', 0);
        //@error_reporting(0);
        return json_encode($this->ftsResponse);
    }

    /**
     * Get data from input
     *
     * @return array|boolean|mixed|object
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function getData()
    {
        //@ini_set('error_reporting', 0);
        //@error_reporting(0);
        $input = JFactory::getApplication()->input;
        $xr = $input->post->getInt('__xr', null);
        if ($xr === 1) {
            $z = $input->post->get('z', null, null);
            $post = isset($z) ? json_decode(stripslashes($z), true) : array();
            return $post;
        } else {
            return false;
        }
    }
}
