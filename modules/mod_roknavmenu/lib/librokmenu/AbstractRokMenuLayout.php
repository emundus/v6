<?php
/**
 * @version   $Id: AbstractRokMenuLayout.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

require_once(dirname(__FILE__) . '/RokMenuLayout.php');


/**
 *
 */
abstract class AbstractRokMenuLayout implements RokMenuLayout
{
    protected $args = array();
    protected $scripts = array();
    protected $inlineScript = '';
    protected $styles = array();
    protected $inlineStyle = '';
    protected $browser;
    protected $staged = false;

    public function __construct(&$args)
    {
        $this->args =& $args;
        $this->browser = new RokBrowserCheck();
    }

    public function doStageHeader()
    {
        if ($this->staged) return;
        $this->stageHeader();
        $this->staged = true;
    }

    public function getInlineScript()
    {
        return $this->inlineScript;
    }

    public function getInlineStyle()
    {
        return $this->inlineStyle;
    }

    public function getStyleFiles()
    {
        return $this->styles;
    }

    public function getScriptFiles()
    {
        return $this->scripts;
    }

    /**
     * Enqeues a script file after doing the browser specific check.
     * @param  $scriptFile the script file relative to the themes root dir
     * @return void
     */
    protected function addScript($scriptFile, $prefix = 'rokmenu_')
    {
        $full_path = $this->args['theme_path'] . '/' . dirname($scriptFile) . '/';
        $relative_path = $this->args['theme_rel_path'] . '/' . dirname($scriptFile) . '/';
        $url_path = $this->args['theme_url'] . '/' . dirname($scriptFile) . '/';

        $file_checks = array_reverse($this->browser->getChecks($scriptFile));
        foreach ($file_checks as $file_check) {
            if (file_exists($full_path . $file_check) && is_readable($full_path . $file_check)) {
                $this->scripts[$prefix . $file_check] = array(
                    'full' => $full_path . $file_check,
                    'relative' => $relative_path . $file_check,
                    'url' => $url_path . $file_check
                );
                break;
            }
        }
    }

    /**
     * Add a css style file and any browser specific versions of it
     * @param  $styleFile the css style file relative to the themes root dir
     * @return void
     */
    protected function addStyle($styleFile, $prefix = 'rokmenu_')
    {
        $full_path = $this->args['theme_path'] . '/' . dirname($styleFile) . '/';
        $relative_path = $this->args['theme_rel_path'] . '/' . dirname($styleFile) . '/';
        $url_path = $this->args['theme_url'] . '/' . dirname($styleFile) . '/';

        $file_checks = $this->browser->getChecks($styleFile);
        foreach ($file_checks as $file_check) {
            if (file_exists($full_path . $file_check) && is_readable($full_path . $file_check)) {
                $this->styles[$prefix . $file_check] = array(
                    'full' => $full_path . $file_check,
                    'relative' => $relative_path . $file_check,
                    'url' => $url_path . $file_check
                );
            }
        }
    }

    protected function appendInlineStyle($inlineStyle)
    {
        $this->inlineStyle .= $inlineStyle;
    }

    protected function appendInlineScript($inlineScript)
    {
        $this->inlineScript .= $inlineScript;
    }
}

