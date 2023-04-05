<?php

/**
 * @package     Joomla.API
 * @subpackage  com_fields
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Api\View\Groups;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Router\Exception\RouteNotFoundException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The groups view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
    /**
     * The fields to render item in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderItem = [
        'typeAlias',
        'id',
        'asset_id',
        'context',
        'title',
        'note',
        'description',
        'state',
        'checked_out',
        'checked_out_time',
        'ordering',
        'params',
        'language',
        'created',
        'created_by',
        'modified',
        'modified_by',
        'access',
        'type',
    ];

    /**
     * The fields to render items in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderList = [
        'id',
        'title',
        'name',
        'checked_out',
        'checked_out_time',
        'note',
        'state',
        'access',
        'created_time',
        'created_user_id',
        'ordering',
        'language',
        'fieldparams',
        'params',
        'type',
        'default_value',
        'context',
        'group_id',
        'label',
        'description',
        'required',
        'language_title',
        'language_image',
        'editor',
        'access_level',
        'author_name',
        'group_title',
        'group_access',
        'group_state',
        'group_note',
    ];

    /**
     * Execute and display a template script.
     *
     * @param   object  $item  Item
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function displayItem($item = null)
    {
        if ($item === null) {
            /** @var \Joomla\CMS\MVC\Model\AdminModel $model */
            $model = $this->getModel();
            $item  = $this->prepareItem($model->getItem());
        }

        if ($item->id === null) {
            throw new RouteNotFoundException('Item does not exist');
        }

        if ($item->context != $this->getModel()->getState('filter.context')) {
            throw new RouteNotFoundException('Item does not exist');
        }

        return parent::displayItem($item);
    }
}
