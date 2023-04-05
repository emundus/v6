<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Workflow.Featuring
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Event\Table\BeforeStoreEvent;
use Joomla\CMS\Event\View\DisplayEvent;
use Joomla\CMS\Event\Workflow\WorkflowFunctionalityUsedEvent;
use Joomla\CMS\Event\Workflow\WorkflowTransitionEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\DatabaseModelInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\ContentHistory;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\Workflow\WorkflowPluginTrait;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\Component\Content\Administrator\Event\Model\FeatureEvent;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Joomla\String\Inflector;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Workflow Featuring Plugin
 *
 * @since  4.0.0
 */
class PlgWorkflowFeaturing extends CMSPlugin implements SubscriberInterface
{
    use WorkflowPluginTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Loads the CMS Application for direct access
     *
     * @var   CMSApplicationInterface
     * @since 4.0.0
     */
    protected $app;

    /**
     * The name of the supported functionality to check against
     *
     * @var   string
     * @since 4.0.0
     */
    protected $supportFunctionality = 'core.featured';

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterDisplay'                  => 'onAfterDisplay',
            'onContentBeforeChangeFeatured'   => 'onContentBeforeChangeFeatured',
            'onContentBeforeSave'             => 'onContentBeforeSave',
            'onContentPrepareForm'            => 'onContentPrepareForm',
            'onContentVersioningPrepareTable' => 'onContentVersioningPrepareTable',
            'onTableBeforeStore'              => 'onTableBeforeStore',
            'onWorkflowAfterTransition'       => 'onWorkflowAfterTransition',
            'onWorkflowBeforeTransition'      => 'onWorkflowBeforeTransition',
            'onWorkflowFunctionalityUsed'     => 'onWorkflowFunctionalityUsed',
        ];
    }

    /**
     * The form event.
     *
     * @param   EventInterface  $event  The event
     *
     * @since   4.0.0
     */
    public function onContentPrepareForm(EventInterface $event)
    {
        $form = $event->getArgument('0');
        $data = $event->getArgument('1');

        $context = $form->getName();

        // Extend the transition form
        if ($context === 'com_workflow.transition') {
            $this->enhanceWorkflowTransitionForm($form, $data);

            return;
        }

        $this->enhanceItemForm($form, $data);
    }

    /**
     * Disable certain fields in the item form view, when we want to take over this function in the transition
     * Check also for the workflow implementation and if the field exists
     *
     * @param   Form      $form  The form
     * @param   stdClass  $data  The data
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    protected function enhanceItemForm(Form $form, $data)
    {
        $context = $form->getName();

        if (!$this->isSupported($context)) {
            return true;
        }

        $parts = explode('.', $context);

        $component = $this->app->bootComponent($parts[0]);

        $modelName = $component->getModelName($context);

        $table = $component->getMVCFactory()->createModel($modelName, $this->app->getName(), ['ignore_request' => true])
            ->getTable();

        $fieldname = $table->getColumnAlias('featured');

        $options = $form->getField($fieldname)->options;

        $value = $data->$fieldname ?? $form->getValue($fieldname, null, 0);

        $text = '-';

        $textclass = 'body';

        switch ($value) {
            case 1:
                $textclass = 'success';
                break;

            case 0:
            case -2:
                $textclass = 'danger';
        }

        if (!empty($options)) {
            foreach ($options as $option) {
                if ($option->value == $value) {
                    $text = $option->text;

                    break;
                }
            }
        }

        $form->setFieldAttribute($fieldname, 'type', 'spacer');

        $label = '<span class="text-' . $textclass . '">' . htmlentities($text, ENT_COMPAT, 'UTF-8') . '</span>';
        $form->setFieldAttribute(
            $fieldname,
            'label',
            Text::sprintf('PLG_WORKFLOW_FEATURING_FEATURED', $label)
        );

        return true;
    }

    /**
     * Manipulate the generic list view
     *
     * @param   DisplayEvent  $event
     *
     * @since   4.0.0
     */
    public function onAfterDisplay(DisplayEvent $event)
    {
        $app = Factory::getApplication();

        if (!$app->isClient('administrator')) {
            return;
        }

        $component = $event->getArgument('extensionName');
        $section   = $event->getArgument('section');

        // We need the single model context for checking for workflow
        $singularsection = Inflector::singularize($section);

        if (!$this->isSupported($component . '.' . $singularsection)) {
            return true;
        }

        // List of related batch functions we need to hide
        $states = [
            'featured',
            'unfeatured',
        ];

        $js = "
			document.addEventListener('DOMContentLoaded', function()
			{
				var dropdown = document.getElementById('toolbar-status-group');

				if (!dropdown)
				{
					return;
				}

				" . json_encode($states) . ".forEach((action) => {
					var button = document.getElementById('status-group-children-' + action);

					if (button)
					{
						button.classList.add('d-none');
					}
				});

			});
		";

        $app->getDocument()->addScriptDeclaration($js);

        return true;
    }

    /**
     * Check if we can execute the transition
     *
     * @param   WorkflowTransitionEvent  $event
     *
     * @return   boolean
     *
     * @since   4.0.0
     */
    public function onWorkflowBeforeTransition(WorkflowTransitionEvent $event)
    {
        $context    = $event->getArgument('extension');
        $transition = $event->getArgument('transition');
        $pks        = $event->getArgument('pks');

        if (!$this->isSupported($context) || !is_numeric($transition->options->get('featuring'))) {
            return true;
        }

        $value = $transition->options->get('featuring');

        if (!is_numeric($value)) {
            return true;
        }

        /**
         * Here it becomes tricky. We would like to use the component models featured method, so we will
         * Execute the normal "onContentBeforeChangeFeatured" plugins. But they could cancel the execution,
         * So we have to precheck and cancel the whole transition stuff if not allowed.
         */
        $this->app->set('plgWorkflowFeaturing.' . $context, $pks);

        // Trigger the change state event.
        $eventResult = $this->app->getDispatcher()->dispatch(
            'onContentBeforeChangeFeatured',
            AbstractEvent::create(
                'onContentBeforeChangeFeatured',
                [
                    'eventClass' => 'Joomla\Component\Content\Administrator\Event\Model\FeatureEvent',
                    'subject'    => $this,
                    'extension'  => $context,
                    'pks'        => $pks,
                    'value'      => $value,
                    'abort'      => false,
                    'abortReason' => '',
                ]
            )
        );

        // Release allowed pks, the job is done
        $this->app->set('plgWorkflowFeaturing.' . $context, []);

        if ($eventResult->getArgument('abort')) {
            $event->setStopTransition();

            return false;
        }

        return true;
    }

    /**
     * Change Feature State of an item. Used to disable feature state change
     *
     * @param   WorkflowTransitionEvent  $event
     *
     * @return   void
     *
     * @since   4.0.0
     */
    public function onWorkflowAfterTransition(WorkflowTransitionEvent $event): void
    {
        $context       = $event->getArgument('extension');
        $extensionName = $event->getArgument('extensionName');
        $transition    = $event->getArgument('transition');
        $pks           = $event->getArgument('pks');

        if (!$this->isSupported($context)) {
            return;
        }

        $component = $this->app->bootComponent($extensionName);

        $value = $transition->options->get('featuring');

        if (!is_numeric($value)) {
            return;
        }

        $options = [
            'ignore_request'               => true,
            // We already have triggered onContentBeforeChangeFeatured, so use our own
            'event_before_change_featured' => 'onWorkflowBeforeChangeFeatured',
        ];

        $modelName = $component->getModelName($context);

        $model = $component->getMVCFactory()->createModel($modelName, $this->app->getName(), $options);

        $model->featured($pks, $value);
    }

    /**
     * Change Feature State of an item. Used to disable Feature state change
     *
     * @param   FeatureEvent  $event
     *
     * @return   boolean
     *
     * @throws   Exception
     * @since   4.0.0
     */
    public function onContentBeforeChangeFeatured(FeatureEvent $event)
    {
        $extension = $event->getArgument('extension');
        $pks       = $event->getArgument('pks');

        if (!$this->isSupported($extension)) {
            return true;
        }

        // We have allowed the pks, so we're the one who triggered
        // With onWorkflowBeforeTransition => free pass
        if ($this->app->get('plgWorkflowFeaturing.' . $extension) === $pks) {
            return true;
        }

        $event->setAbort('PLG_WORKFLOW_FEATURING_CHANGE_STATE_NOT_ALLOWED');
    }

    /**
     * The save event.
     *
     * @param   EventInterface  $event
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function onContentBeforeSave(EventInterface $event)
    {
        $context = $event->getArgument('0');

        /** @var TableInterface $table */
        $table = $event->getArgument('1');
        $isNew = $event->getArgument('2');
        $data  = $event->getArgument('3');

        if (!$this->isSupported($context)) {
            return true;
        }

        $keyName = $table->getColumnAlias('featured');

        // Check for the old value
        $article = clone $table;

        $article->load($table->id);

        /**
         * We don't allow the change of the feature state when we use the workflow
         * As we're setting the field to disabled, no value should be there at all
         */
        if (isset($data[$keyName])) {
            $this->app->enqueueMessage(Text::_('PLG_WORKFLOW_FEATURING_CHANGE_STATE_NOT_ALLOWED'), 'error');

            return false;
        }

        return true;
    }

    /**
     * We remove the featured field from the versioning
     *
     * @param   EventInterface  $event
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function onContentVersioningPrepareTable(EventInterface $event)
    {
        $subject = $event->getArgument('subject');
        $context = $event->getArgument('extension');

        if (!$this->isSupported($context)) {
            return true;
        }

        $parts = explode('.', $context);

        $component = $this->app->bootComponent($parts[0]);

        $modelName = $component->getModelName($context);

        $model = $component->getMVCFactory()->createModel($modelName, $this->app->getName(), ['ignore_request' => true]);

        $table = $model->getTable();

        $subject->ignoreChanges[] = $table->getColumnAlias('featured');
    }

    /**
     * Pre-processor for $table->store($updateNulls)
     *
     * @param   BeforeStoreEvent  $event  The event to handle
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onTableBeforeStore(BeforeStoreEvent $event)
    {
        $subject = $event->getArgument('subject');

        if (!($subject instanceof ContentHistory)) {
            return;
        }

        $parts = explode('.', $subject->item_id);

        $typeAlias = $parts[0] . (isset($parts[1]) ? '.' . $parts[1] : '');

        if (!$this->isSupported($typeAlias)) {
            return;
        }

        $component = $this->app->bootComponent($parts[0]);

        $modelName = $component->getModelName($typeAlias);

        $model = $component->getMVCFactory()->createModel($modelName, $this->app->getName(), ['ignore_request' => true]);

        $table = $model->getTable();

        $field = $table->getColumnAlias('featured');

        $versionData = new Registry($subject->version_data);

        $versionData->remove($field);

        $subject->version_data = $versionData->toString();
    }

    /**
     * Check if the current plugin should execute workflow related activities
     *
     * @param   string  $context
     *
     * @return   boolean
     *
     * @since   4.0.0
     */
    protected function isSupported($context)
    {
        if (!$this->checkAllowedAndForbiddenlist($context) || !$this->checkExtensionSupport($context, $this->supportFunctionality)) {
            return false;
        }

        $parts = explode('.', $context);

        // We need at least the extension + view for loading the table fields
        if (count($parts) < 2) {
            return false;
        }

        $component = $this->app->bootComponent($parts[0]);

        if (
            !$component instanceof WorkflowServiceInterface
            || !$component->isWorkflowActive($context)
            || !$component->supportFunctionality($this->supportFunctionality, $context)
        ) {
            return false;
        }

        $modelName = $component->getModelName($context);

        $model = $component->getMVCFactory()->createModel($modelName, $this->app->getName(), ['ignore_request' => true]);

        if (!$model instanceof DatabaseModelInterface || !method_exists($model, 'featured')) {
            return false;
        }

        $table = $model->getTable();

        if (!$table instanceof TableInterface || !$table->hasField('featured')) {
            return false;
        }

        return true;
    }

    /**
     * If plugin supports the functionality we set the used variable
     *
     * @param   WorkflowFunctionalityUsedEvent  $event
     *
     * @since 4.0.0
     */
    public function onWorkflowFunctionalityUsed(WorkflowFunctionalityUsedEvent $event)
    {
        $functionality = $event->getArgument('functionality');

        if ($functionality !== 'core.featured') {
            return;
        }

        $event->setUsed();
    }
}
