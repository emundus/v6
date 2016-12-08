<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Render;

use FOF30\Container\Container;
use FOF30\Form\Form;
use FOF30\Model\DataModel;

defined('_JEXEC') or die;

/**
 * Base class for other render classes
 */
abstract class RenderBase implements RenderInterface
{

	/** @var   Container|null  The container we are attached to */
	protected $container = null;

	/** @var   bool  Is this renderer available under this execution environment? */
	protected $enabled = false;

	/** @var   int  The priority of this renderer in case we have multiple available ones */
	protected $priority = 0;

	/** @var   \JRegistry  A registry object holding renderer options */
	protected $optionsRegistry = null;

	/**
	 * Public constructor. Determines the priority of this class and if it should be enabled
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;

		$this->optionsRegistry = new \JRegistry();
	}

	/**
	 * Set a renderer option (depends on the renderer)
	 *
	 * @param   string $key   The name of the option to set
	 * @param   string $value The value of the option
	 *
	 * @return  void
	 */
	public function setOption($key, $value)
	{
		$this->optionsRegistry->set($key, $value);
	}

	/**
	 * Set multiple renderer options at once (depends on the renderer)
	 *
	 * @param   array $options The options to set as key => value pairs
	 *
	 * @return  void
	 */
	public function setOptions(array $options)
	{
		foreach ($options as $key => $value)
		{
			$this->setOption($key, $value);
		}
	}

	/**
	 * Get the value of a renderer option
	 *
	 * @param   string $key     The name of the parameter
	 * @param   mixed  $default The default value to return if the parameter is not set
	 *
	 * @return  mixed  The parameter value
	 */
	public function getOption($key, $default = null)
	{
		return $this->optionsRegistry->get($key, $default);
	}

	/**
	 * Returns the information about this renderer
	 *
	 * @return object
	 */
	public function getInformation()
	{
		return (object) array(
			'enabled'  => $this->enabled,
			'priority' => $this->priority
		);
	}

	/**
	 * Echoes any HTML to show before the view template
	 *
	 * @param   string $view The current view
	 * @param   string $task The current task
	 *
	 * @return  void
	 */
	function preRender($view, $task)
	{
	}

	/**
	 * Echoes any HTML to show after the view template
	 *
	 * @param   string $view The current view
	 * @param   string $task The current task
	 *
	 * @return  void
	 */
	function postRender($view, $task)
	{
	}

	/**
	 * Renders a Form and returns the corresponding HTML
	 *
	 * @param   Form      &$form         The form to render
	 * @param   DataModel $model         The model providing our data
	 * @param   string    $formType      The form type: edit, browse or read
	 * @param   boolean   $raw           If true, the raw form fields rendering (without the surrounding form tag) is
	 *                                   returned.
	 *
	 * @return  string    The HTML rendering of the form
	 */
	function renderForm(Form &$form, DataModel $model, $formType = null, $raw = false)
	{
		throw new \LogicException(sprintf('Renderer class %s must implement the %s method', get_class($this), __METHOD__));
	}

	/**
	 * Renders a F0FForm for a Browse view and returns the corresponding HTML
	 *
	 * @param   Form      &$form The form to render
	 * @param   DataModel $model The model providing our data
	 *
	 * @return  string    The HTML rendering of the form
	 */
	function renderFormBrowse(Form &$form, DataModel $model)
	{
		throw new \LogicException(sprintf('Renderer class %s must implement the %s method', get_class($this), __METHOD__));
	}

	/**
	 * Renders a F0FForm for a Read view and returns the corresponding HTML
	 *
	 * @param   Form      &$form The form to render
	 * @param   DataModel $model The model providing our data
	 *
	 * @return  string    The HTML rendering of the form
	 */
	function renderFormRead(Form &$form, DataModel $model)
	{
		throw new \LogicException(sprintf('Renderer class %s must implement the %s method', get_class($this), __METHOD__));
	}

	/**
	 * Renders a F0FForm for an Edit view and returns the corresponding HTML
	 *
	 * @param   Form      &$form The form to render
	 * @param   DataModel $model The model providing our data
	 *
	 * @return  string    The HTML rendering of the form
	 */
	function renderFormEdit(Form &$form, DataModel $model)
	{
		throw new \LogicException(sprintf('Renderer class %s must implement the %s method', get_class($this), __METHOD__));
	}

	/**
	 * Renders a F0FForm for an Edit view and returns the corresponding HTML
	 *
	 * @param   Form      &$form    The form to render
	 * @param   DataModel $model    The model providing our data
	 * @param   string    $formType The form type: edit, browse or read
	 *
	 * @return  string    The HTML rendering of the form
	 */
	function renderFormRaw(Form &$form, DataModel $model, $formType = null)
	{
		throw new \LogicException(sprintf('Renderer class %s must implement the %s method', get_class($this), __METHOD__));
	}


	/**
	 * Renders the submenu (link bar) for a category view when it is used in a
	 * extension
	 *
	 * Note: this function has to be called from the addSubmenu function in
	 *         the ExtensionNameHelper class located in
	 *         administrator/components/com_ExtensionName/helpers/Extensionname.php
	 *
	 * @return  void
	 */
	function renderCategoryLinkbar()
	{
		throw new \LogicException(sprintf('Renderer class %s must implement the %s method', get_class($this), __METHOD__));
	}

	/**
	 * Renders a raw fieldset of a F0FForm and returns the corresponding HTML
	 *
	 * @param   \stdClass &$fieldset  The fieldset to render
	 * @param   Form      &$form      The form to render
	 * @param   DataModel $model      The model providing our data
	 * @param   string    $formType   The form type e.g. 'edit' or 'read'
	 * @param   boolean   $showHeader Should I render the fieldset's header?
	 *
	 * @return  string    The HTML rendering of the fieldset
	 */
	function renderFieldset(\stdClass &$fieldset, Form &$form, DataModel $model, $formType, $showHeader = true)
	{
		throw new \LogicException(sprintf('Renderer class %s must implement the %s method', get_class($this), __METHOD__));
	}

	/**
	 * Renders a label for a fieldset.
	 *
	 * @param   object  $field The field of the label to render
	 * @param   Form    &$form The form to render
	 * @param    string $title The title of the label
	 *
	 * @return    string        The rendered label
	 */
	function renderFieldsetLabel($field, Form &$form, $title)
	{
		throw new \LogicException(sprintf('Renderer class %s must implement the %s method', get_class($this), __METHOD__));
	}

	/**
	 * Checks if the fieldset defines a tab pane
	 *
	 * @param   \SimpleXMLElement $fieldset
	 *
	 * @return  boolean
	 */
	function isTabFieldset($fieldset)
	{
		if (!isset($fieldset->class) || !$fieldset->class)
		{
			return false;
		}

		$class = $fieldset->class;
		$classes = explode(' ', $class);

		if (!in_array('tab-pane', $classes))
		{
			return false;
		}
		else
		{
			return in_array('active', $classes) ? 2 : 1;
		}
	}
}