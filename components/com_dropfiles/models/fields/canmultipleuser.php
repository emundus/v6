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
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') || die;

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 */
class JFormFieldCanmultipleuser extends JFormField
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'Canmultipleuser';

    /**
     * Filtering groups
     *
     * @var array
     */
    protected $groups = null;

    /**
     * Users to exclude from the list of users
     *
     * @var array
     */
    protected $excluded = null;

    /**
     * Layout to render
     *
     * @var string
     */
    protected $layout = 'joomla.form.field.user';

    /**
     * Get label
     *
     * @return string
     */
    protected function getLabel()
    {
        $params = JComponentHelper::getParams('com_dropfiles');

        if (!$params->get('restrictfile', 0)) {
            return '';
        }
        return parent::getLabel();
    }

    /**
     * Method to get the field input canuser.
     *
     * @return string
     */
    protected function getInput()
    {
        $user = JFactory::getUser();
        $params = JComponentHelper::getParams('com_dropfiles');
        if (!$params->get('restrictfile', 0)) {
            return '';
        }

        if (empty($this->layout)) {
            throw new UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
        }
        $displayData = $this->getLayoutData();
        // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Too many variables
        extract($displayData);
        if (!isset($required)) {
            $required = 0;
        }

        $link = 'index.php?option=com_dropfiles&amp;view=users&amp;layout=multiplemodal&amp;tmpl=component&amp;required='
            . ($required ? 1 : 0)
            . (isset($id) ? ('&amp;field=' . $id) : '')
            . (isset($groups) ? ('&amp;groups=' . base64_encode(json_encode($groups))) : '')
            . (isset($excluded) ? ('&amp;excluded=' . base64_encode(json_encode($excluded))) : '')
            . (isset($value) ? ('&amp;selected=' . base64_encode($value)) : '');

        // Invalidate the input value if no user selected
        if (!isset($userName)) {
            $userName = $user->name;
        }
        if (!isset($id)) {
            $id = 0;
        }

        if (JText::_('JLIB_FORM_SELECT_USER') === htmlspecialchars($userName, ENT_COMPAT, 'UTF-8')) {
            $userName = '';
        }
        if (!isset($readonly)) {
            $readonly = false;
        }

        ?>
        <div class="field-multiple-user-wrapper"
             data-url="<?php echo $link; ?>"
             data-modal=".modal"
             data-modal-width="100%"
             data-modal-height="400px"
             data-input=".field-multiple-user-input"
             data-input-name=".field-multiple-user-input-name"
             data-buttovaluen-select=".button-select"
        >
            <div class="input-append">
                <input
                        type="text" id="<?php echo $id; ?>"
                        value="<?php echo htmlspecialchars($userName, ENT_COMPAT, 'UTF-8'); ?>"
                        placeholder="<?php echo JText::_('JLIB_FORM_SELECT_USER'); ?>"
                        readonly
                        class="field-multiple-user-input-name <?php echo isset($class) ? (string)$class : '' ?>"
                    <?php echo isset($size) ? ' size="' . (int)$size . '"' : ''; ?>
                    <?php echo $required ? 'required' : ''; ?>/>
                <?php if (!$readonly) : ?>
                    <a data-bs-toggle="modal" data-bs-target="#<?php echo 'userModal_' . (isset($id) ? $id : 0);?>" class="btn btn-primary button-select"
                       title="<?php echo JText::_('JLIB_FORM_CHANGE_USER') ?>"><span class="icon-user"></span></a>
                    <?php echo JHtml::_(
                        'bootstrap.renderModal',
                        'userModal_' . $id,
                        array(
                            'url' => $link,
                            'title' => JText::_('JLIB_FORM_CHANGE_USER'),
                            'closeButton' => true,
                            'footer' => '<button class="btn" data-dismiss="modal" data-bs-dismiss="modal">' . JText::_('COM_DROPFILES_JS_OK') . '</button>'
                        )
                    ); ?>
                    <a class="btn user-clear"><span class="icon-delete"></span></a>
                <?php endif; ?>
            </div>
            <?php // Create the real field, hidden, that stored the user id.
            ?>
            <input type="hidden" id="<?php echo $id; ?>_id" name="<?php echo isset($name) ? $name : ''; ?>"
                   value="<?php echo isset($value) ? $value : ''; ?>"
                   class="field-multiple-user-input <?php echo isset($class) ? (string)$class : '' ?>"
                   data-onchange=""/>
        </div>
        <?php
    }

    /**
     * Get the data that is going to be passed to the layout
     *
     * @return array
     */
    public function getLayoutData()
    {
        // Get the basic field data
        $data = $this->getLayoutDatas();

        // Load the current username if available.
        $table = JTable::getInstance('user');
        $userName = array();
        $userIds = explode(',', $this->value);
        if (is_numeric($this->value)) {
            $table->load($this->value);
            $userName[] = $table->name;
        } elseif (strtoupper($this->value) === 'CURRENT') { // Handle the special case for "current".
            // 'CURRENT' is not a reasonable value to be placed in the html
            $this->value = JFactory::getUser()->id;
            $table->load($this->value);
            $userName[] = $table->name;
        } elseif (!empty($userIds)) {
            foreach ($userIds as $userId) {
                $table->load($userId);
                $userName[] = $table->name;
            }
        } else {
            $userName[] = JText::_('JLIB_FORM_SELECT_USER');
        }

        $extraData = array(
            'userName' => implode(',', $userName),
            'groups' => $this->getGroups(),
            'excluded' => $this->getExcluded()
        );

        return array_merge($data, $extraData);
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return array
     *
     * @since 3.5
     */
    protected function getLayoutDatas()
    {
        // Label preprocess
        $label = $this->element['label'] ? (string)$this->element['label'] : (string)$this->element['name'];
        $label = $this->translateLabel ? JText::_($label) : $label;

        // Description preprocess
        $description = !empty($this->description) ? $this->description : null;
        $description = !empty($description) && $this->translateDescription ? JText::_($description) : $description;

        $alt = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);

        return array(
            'autocomplete' => $this->autocomplete,
            'autofocus' => $this->autofocus,
            'class' => $this->class,
            'description' => $description,
            'disabled' => $this->disabled,
            'field' => $this,
            'group' => $this->group,
            'hidden' => $this->hidden,
            'hint' => $this->translateHint ? JText::alt($this->hint, $alt) : $this->hint,
            'id' => $this->id,
            'label' => $label,
            'labelclass' => $this->labelclass,
            'multiple' => $this->multiple,
            'name' => $this->name,
            'onchange' => $this->onchange,
            'onclick' => $this->onclick,
            'pattern' => $this->pattern,
            'readonly' => $this->readonly,
            'repeat' => $this->repeat,
            'required' => (bool)$this->required,
            'size' => $this->size,
            'spellcheck' => $this->spellcheck,
            'validate' => $this->validate,
            'value' => $this->value
        );
    }

    /**
     * Method to get the filtering groups (null means no filtering)
     *
     * @return mixed  array of filtering groups or null.
     *
     * @since 1.6
     */
    protected function getGroups()
    {
        if (isset($this->element['groups'])) {
            return explode(',', $this->element['groups']);
        }

        return null;
    }

    /**
     * Method to get the users to exclude from the list of users
     *
     * @return mixed  Array of users to exclude or null to to not exclude them
     *
     * @since 1.6
     */
    protected function getExcluded()
    {
        return explode(',', $this->element['exclude']);
    }
}
