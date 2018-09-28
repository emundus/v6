<?php
/**
 * @package   AllediaFramework
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC., All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework\Content;

use Alledia\Framework\Base;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

class Tag extends Base
{
    /**
     * The unparsed tag string
     *
     * @var string
     */
    protected $unparsedString;

    /**
     * The tag name
     *
     * @var string
     */
    protected $name;

    /**
     * The tag content
     *
     * @var string
     */
    protected $content = '';

    /**
     * The regex used to find the tag
     *
     * @var string
     */
    protected $regex;

    /**
     * The tag params
     *
     * @var Registry
     */
    public $params;

    /**
     * Constructor method, that defines the internal content
     *
     * @param string $name
     * @param string $unparsedString
     *
     * @return void
     */
    public function __construct($name, $unparsedString)
    {
        $this->name           = $name;
        $this->unparsedString = $unparsedString;
        $this->regex          = $this->getRegex($this->name);

        $this->parse();
    }

    /**
     * Return the regex used to find tags inside a text.
     *
     * @param string $tagName
     *
     * @return string
     */
    public static function getRegex($tagName)
    {
        return '/\{' . $tagName . '(?:(?!\{\/' . $tagName
            . '\}).)*\}[\s]*([^{]*)[\s]*\{\/' . $tagName . '\}/i';
    }

    /**
     * Parse this tag storing the content and params.
     *
     * @return void
     */
    protected function parse()
    {
        $this->content = $this->parseContent();

        $this->params = $this->parseParams();
    }

    /**
     * Parse the {tagName}{/tagName} returning the content
     *
     * @return string
     */
    protected function parseContent()
    {
        $content = '';

        if (!empty($this->unparsedString)) {
            if (strpos($this->unparsedString, '{' . $this->name) !== false) {
                // Check if the source has the tag name
                if (preg_match($this->regex, $this->unparsedString, $match)) {
                    $content = trim($match[1]);
                }
            }
        }

        return trim($content);
    }

    /**
     * Parse inline parameters from the tag
     *
     * @return Registry
     */
    protected function parseParams()
    {
        // Remove the tag name, extracting only the tag attributes
        $inlineParams = preg_replace('/^\{' . $this->name . '/', '', $this->unparsedString);
        $inlineParams = trim(preg_replace('/\}[a-z0-9\s]*\{\/' . $this->name . '\}/', '', $inlineParams));

        // Parse the inline params
        $regex  = '/([a-z0-9\_]*)(?:="([^"]*)")?\s?/i';
        $parsed = new Registry();
        if (preg_match_all($regex, $inlineParams, $vars)) {
            $fullParams  = $vars[0];
            $paramNames  = $vars[1];
            $paramValues = $vars[2];

            foreach ($fullParams as $i => $param) {
                if (!empty($paramNames[$i])) {
                    $parsed->set(trim($paramNames[$i]), trim($paramValues[$i]));
                }
            }
        }

        return $parsed;
    }

    /**
     * Return the unparsed string
     *
     * @return string
     */
    public function toString()
    {
        return $this->unparsedString;
    }

    /**
     * Returns the tag content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Registry
     */
    public function getParams()
    {
        return $this->params;
    }
}
