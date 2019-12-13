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
class JFormFieldHtmllink extends JFormField
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'Htmllink';

    /**
     * Get label
     *
     * @return string
     */
    protected function getLabel()
    {
        return '';
    }

    /**
     * Field input html link
     *
     * @return string
     */
    protected function getInput()
    {
        ob_start();
        echo '
            <style>
            .btn-link {
                height: auto;
                width: auto;
                border: none;
                text-shadow: none;
                box-shadow: none;
                background-color: #1d6cb0;
                border-radius: 2px;
                padding: 10px 20px;
                font-size: 14px;
                color: white;
            }
            .btn-link:hover {
                background-color: #1d6cb0;
                color: white;
                text-decoration: none;
            }
            </style>
            ';
        ?>
        <p>
            <a class="btn btn-link" target="_blank" href="<?php echo $this->element['value']; ?>">
                <?php echo JText::_('COM_DROPFILES_ONLINE_DOCUMENT_LINK'); ?>
            </a>
        </p>
        <?php
        return ob_get_clean();
    }
}
