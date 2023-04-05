<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Editor
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Form\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields Editor Plugin
 *
 * @since  3.7.0
 */
class PlgFieldsEditor extends \Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin
{
    /**
     * Transforms the field into a DOM XML element and appends it as a child on the given parent.
     *
     * @param   stdClass    $field   The field.
     * @param   DOMElement  $parent  The field node parent.
     * @param   Form        $form    The form.
     *
     * @return  DOMElement
     *
     * @since   3.7.0
     */
    public function onCustomFieldsPrepareDom($field, DOMElement $parent, Form $form)
    {
        $fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);

        if (!$fieldNode) {
            return $fieldNode;
        }

        $fieldNode->setAttribute('buttons', $field->fieldparams->get('buttons', $this->params->get('buttons', 0)) ? 'true' : 'false');
        $fieldNode->setAttribute('hide', implode(',', $field->fieldparams->get('hide', [])));

        return $fieldNode;
    }
}
