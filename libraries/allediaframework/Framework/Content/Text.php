<?php
/**
 * @package   AllediaFramework
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC., All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework\Content;

use Alledia\Framework\Base;

defined('_JEXEC') or die();

class Text extends Base
{
    public $content = '';

    /**
     * Constructor method, that defines the internal content
     *
     * @param string $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Extract multiple {mytag} tags from the content
     *
     * @todo Recognize unclose tags like {dumbtag param1="12"}
     *
     * @param  string $tagName
     *
     * @return array  An array with all tags {tagName} found on the text
     */
    protected function extractPluginTags($tagName)
    {
        preg_match_all(Tag::getRegex($tagName), $this->content, $matches);

        return $matches[0];
    }

    /**
     * Extract multiple {mytag} tags from the content, returning
     * as Tag instances
     *
     * @param  string $tagName
     *
     * @return array  An array with all tags {tagName} found on the text
     */
    public function getPluginTags($tagName)
    {
        $unparsedTags = $this->extractPluginTags($tagName);

        $tags = array();
        foreach ($unparsedTags as $unparsedTag) {
            $tags[] = new Tag($tagName, $unparsedTag);
        }

        return $tags;
    }

    /**
     * Extract multiple {mytag} tags from the content, returning
     * as Tag instances
     *
     * @param  string $tagName
     *
     * @return array  An array with all tags {tagName} found on the text
     * @deprecated 1.3.1 Use getPluginsTags instead
     */
    public function getTags($tagName)
    {
        // Deprecated. Use getPluginTags instead
        return $this->getPluginTags($tagName);
    }
}
