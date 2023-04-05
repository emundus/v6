<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class to fire onContentPrepare for non-article based content.
 *
 * @since  1.5
 */
abstract class Content
{
    /**
     * Fire onContentPrepare for content that isn't part of an article.
     *
     * @param   string  $text     The content to be transformed.
     * @param   array   $params   The content params.
     * @param   string  $context  The context of the content to be transformed.
     *
     * @return  string   The content after transformation.
     *
     * @since   1.5
     */
    public static function prepare($text, $params = null, $context = 'text')
    {
        if ($params === null) {
            $params = new CMSObject();
        }

        $article = new \stdClass();
        $article->text = $text;
        PluginHelper::importPlugin('content');
        Factory::getApplication()->triggerEvent('onContentPrepare', [$context, &$article, &$params, 0]);

        return $article->text;
    }

    /**
     * Returns an array of months.
     *
     * @param   Registry  $state  The state object.
     *
     * @return  array
     *
     * @since   3.9.0
     */
    public static function months($state)
    {
        /** @var \Joomla\Component\Content\Administrator\Extension\ContentComponent $contentComponent */
        $contentComponent = Factory::getApplication()->bootComponent('com_content');

        /** @var \Joomla\Component\Content\Site\Model\ArticlesModel $model */
        $model = $contentComponent->getMVCFactory()
            ->createModel('Articles', 'Site', ['ignore_request' => true]);

        foreach ($state as $key => $value) {
            $model->setState($key, $value);
        }

        $model->setState('filter.category_id', $state->get('category.id'));
        $model->setState('list.start', 0);
        $model->setState('list.limit', -1);
        $model->setState('list.direction', 'asc');
        $model->setState('list.filter', '');

        $items = [];

        foreach ($model->countItemsByMonth() as $item) {
            $date    = new Date($item->d);
            $items[] = HTMLHelper::_('select.option', $item->d, $date->format('F Y') . ' [' . $item->c . ']');
        }

        return $items;
    }
}
