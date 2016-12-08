<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Controller\Mixin\CustomACL;
use Akeeba\AdminTools\Admin\Controller\Mixin\PredefinedTaskList;
use FOF30\Container\Container;
use FOF30\Controller\Controller;
use JText;

class AdminPassword extends Controller
{
    use PredefinedTaskList, CustomACL;

    public function __construct(Container $container, array $config)
    {
        parent::__construct($container, $config);

        $this->predefinedTaskList = ['browse', 'protect', 'unprotect'];
    }

    public function protect()
    {
        // CSRF prevention
        $this->csrfProtection();

        $username  = $this->input->get('username', '', 'raw', 2);
        $password  = $this->input->get('password', '', 'raw', 2);
        $password2 = $this->input->get('password2', '', 'raw', 2);

        if (empty($username))
        {
            $this->setRedirect('index.php?option=com_admintools&view=AdminPassword', JText::_('COM_ADMINTOOLS_ERR_ADMINPASSWORD_NOUSERNAME'), 'error');

            return;
        }

        if (empty($password))
        {
            $this->setRedirect('index.php?option=com_admintools&view=AdminPassword', JText::_('COM_ADMINTOOLS_ERR_ADMINPASSWORD_NOPASSWORD'), 'error');

            return;
        }

        if ($password != $password2)
        {
            $this->setRedirect('index.php?option=com_admintools&view=AdminPassword', JText::_('COM_ADMINTOOLS_ERR_ADMINPASSWORD_PASSWORDNOMATCH'), 'error');

            return;
        }

        /** @var \Akeeba\AdminTools\Admin\Model\AdminPassword $model */
        $model = $this->getModel();

        $model->username = $username;
        $model->password = $password;

        $status = $model->protect();
        $url    = 'index.php?option=com_admintools';

        if ($status)
        {
            $this->setRedirect($url, JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_APPLIED'));

            return;
        }

        $this->setRedirect($url, JText::_('COM_ADMINTOOLS_ERR_ADMINPASSWORD_NOTAPPLIED'), 'error');
    }

    public function unprotect()
    {
        // CSRF prevention
        $this->csrfProtection();

        /** @var \Akeeba\AdminTools\Admin\Model\AdminPassword $model */
        $model  = $this->getModel();
        $status = $model->unprotect();
        $url    = 'index.php?option=com_admintools';

        if ($status)
        {
            $this->setRedirect($url, JText::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_UNAPPLIED'));

            return;
        }

        $this->setRedirect($url, JText::_('COM_ADMINTOOLS_ERR_ADMINPASSWORD_NOTUNAPPLIED'), 'error');
    }
}