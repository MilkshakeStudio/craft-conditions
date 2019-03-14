<?php
/**
 * Conditions plugin for Craft CMS 3.x
 *
 * conditions on fields
 *
 * @link      http://mobile.everybyte.in/
 * @copyright Copyright (c) 2019 W3care
 */

namespace craftconditions\conditions;

use craftconditions\conditions\services\ConditionsService as ConditionsService;
use craftconditions\conditions\elements\Conditions as ConditionsElement;
use craftconditions\conditions\models\ConditionalsModel as ConditionalsModel;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\services\Elements;
use craft\events\RegisterComponentTypesEvent;

use craft\services\Fields;
use craft\records\EntryType as EntryTypeRecord;
use craft\web\View;
use craftconditions\conditions\assets\ConditionsAssets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\db\Migration;

use craftconditions\conditions\records\ConditionalsRecord as ConditionalsRecord;


use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    W3care
 * @package   Conditions
 * @since     1.0.0
 *
 * @property  ConditionsServiceService $conditionsService
 */
class Conditions extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * Conditions::$plugin
     *
     * @var Conditions
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';
	
	protected $version = '1.0.0';
	protected $pluginName = 'Conditions';
	public $sourcePath = "";
	public $depends = "";
	public $js = "";

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * Conditions::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Register our elements
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ConditionsElement::class;
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );

/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'conditions',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
		if (Craft::$app->getRequest()->getIsAjax()) {
            $this->ajaxProcessInit();
        } else {

            $this->includeAssets();
            Craft::$app->view->registerJs('if (window.Craft && window.Craft.ConditionsPlugin) {
                Craft.ConditionsPlugin.init('.$this->getData().');
            }');
			Event::on(Fields::class, Fields::EVENT_BEFORE_SAVE_FIELD_LAYOUT, function(Event $event) {
				
				$this->onSaveConditionalLayout($event);
			});
        }
    }
	/*
    *   Protected methods
    *
    */
    /**
     * @return bool
     */
    protected function ajaxProcessInit()
    {

        if (!Craft::$app->getRequest()->getIsPost()) {
            return false;
        }
	
        $segments = Craft::$app->request->segments;
        $actionSegment = $segments[count($segments) - 1];
		
        switch ($actionSegment) {

            case 'switch-entry-type' :
                Craft::$app->view->registerJs('Craft.ConditionsPlugin.initPrimaryForm();');
                break;

            case 'get-editor-html' :

                $elementId = (int)Craft::$app->getRequest()->getBodyParam('elementId');
				
                $element = $elementId ? Craft::$app->getElements()->getElementById($elementId) : null;
                $elementType = $element ?Craft::$app->getElements()->getElementTypeById($elementId) : Craft::$app->getRequest()->getBodyParam('elementType');
                $attributes =  Craft::$app->getRequest()->getBodyParam('attributes');

                $conditionalsKey = null;

                switch ($elementType) {

                    case 'craft\elements\Entry' :
                        if ($element) {
                            $conditionalsKey = 'entryType:' . $element->type->id;
                        } else if (isset($attributes['typeId'])) {
                            $conditionalsKey = 'entryType:' . $attributes['typeId'];
                        } else if (isset($attributes['sectionId'])) {
                            $entryTypes = Craft::$app->sections->getEntryTypesBySectionId((int)$attributes['sectionId']);
                            $entryType = $entryTypes ? array_shift($entryTypes) : false;
                            $conditionalsKey = $entryType ? 'entryType:' . $entryType->id : null;
                        }
                        break;

                    case 'craft\elements\GlobalSet' :
                        $conditionalsKey = $element ? 'globalSet:' . $element->id : null;
                        break;

                    case 'craft\elements\Asset' :
						
                        $conditionalsKey = $element ? 'assetSource:' . $element->volumeId : null;
                        break;

                    case 'craft\elements\Category' :
                        $conditionalsKey = $element ? 'categoryGroup:' . $element->group->id : null;
                        break;

                    case 'craft\elements\Tag' :
                        $conditionalsKey = $element ? 'tagGroup:' . $element->group->id : null;
                        break;

                    case 'craft\elements\User' :
                        $conditionalsKey = 'users';
                        break;
                }
				
                if ($conditionalsKey) {
                    Craft::$app->view->registerJs('Craft.ConditionsPlugin.initElementEditor("' . $conditionalsKey . '");');
                }
                break;
        }
    }

    /**
     *
     */
    protected function includeAssets()
    {
		$this->view->registerAssetBundle(ConditionsAssets::class);
	}

    /**
     * @return string
     */
    protected function getData()
    {
       $data = array(
                'conditionals' => $this->getConditionalObj(),
                'toggleFieldTypes' => $this->getAllToggleFieldTypes(),
                'toggleFields' => $this->getAllToggleFields(),
                'fieldIds' => $this->getFieldIds(),
        );
        return json_encode($data);
    }

    /**
     * @return array
     */
    protected function getConditionalObj()
    {
        $resources = array();
        $sources = array();
        // Entry types
		
        $entryTypeRecords = EntryTypeRecord::find()->all();
		
        if ($entryTypeRecords) {
            foreach ($entryTypeRecords as $entryType) {
                $sources['entryType:' . $entryType->id] = $entryType->fieldLayoutId;
                $sources['section:' . $entryType->sectionId] = $entryType->fieldLayoutId;
            }
        }

        // Category groups
        $allCategoryGroups = Craft::$app->categories->getAllGroups();
        foreach ($allCategoryGroups as $categoryGroup) {
            $sources['categoryGroup:' . $categoryGroup->id] = $categoryGroup->fieldLayoutId;
        }

        // Tag groups
        $allTagGroups = Craft::$app->tags->getAllTagGroups();
        foreach ($allTagGroups as $tagGroup) {
            $sources['tagGroup:' . $tagGroup->id] = $tagGroup->fieldLayoutId;
        }

        // Asset sources
		$allAssetSources = Craft::$app->getVolumes()->getAllVolumes();
		
		if(!empty($allAssetSources))
		{
			foreach ($allAssetSources as $assetSource) {
				$sources['assetSource:' . $assetSource->id] = $assetSource->fieldLayoutId;
			}
		}
		
        // Global sets
        $allGlobalSets = Craft::$app->globals->getAllSets();
        foreach ($allGlobalSets as $globalSet) {
            $sources['globalSet:' . $globalSet->id] = $globalSet->fieldLayoutId;
        }

        // Users Field Layout 
		$usersFieldLayout = Craft::$app->fields->getLayoutByType('craft\elements\User');
		
		
		
        if ($usersFieldLayout) {
            $sources['users'] = $usersFieldLayout->id;
        }
	
        // Solspace Calendar
        $solspaceCalendarPlugin = Craft::$app->plugins->getPlugin('calendar');
        if ($solspaceCalendarPlugin && $solspaceCalendarPlugin->getDeveloper() === 'Solspace') {
            // Before 1.7.0, Solspace Calendar used a single Field Layout for all calendars. Let's try and support both the old and the new
            if (version_compare($solspaceCalendarPlugin->getVersion(), '1.7.0', '>=')) {
                $solspaceCalendars = Craft::$app->calendar_calendars->getAllCalendars();
                if ($solspaceCalendars && is_array($solspaceCalendars) && !empty($solspaceCalendars)) {
                    foreach ($solspaceCalendars as $solspaceCalendar) {
                        $sources['solspaceCalendar:'.$solspaceCalendar->id] = $solspaceCalendar->fieldLayoutId;
                    }
                }
            } else {
                $solspaceCalendarFieldLayout = Craft::$app->fields->getLayoutByType('Calendar_Event');
                if ($solspaceCalendarFieldLayout) {
                    $sources['solspaceCalendar'] = $solspaceCalendarFieldLayout->id;
                }
            }
        }

        // Get all conditionals
        $conditionals = array();
		$tableSchema = Craft::$app->db->schema->getTableSchema('{{%conditions_conditionalsrecord}}');
        if ($tableSchema != null)
		{
			$conditionalsRecords = ConditionalsRecord::find()->all();
			if ($conditionalsRecords) {
				foreach ($conditionalsRecords as $conditionalsRecord) {
					$conditionalsModel = $conditionalsRecord;
					if ($conditionalsModel->conditionals && $conditionalsModel->conditionals != '') {
						$conditionals['fieldLayout:' . $conditionalsModel->fieldLayoutId] = $conditionalsModel->conditionals;
					}
				}
			}
		}
        // Map conditionals to sources
        foreach ($sources as $sourceId => $fieldLayoutId) {
            if (isset($conditionals['fieldLayout:' . $fieldLayoutId])) {
                $resources[$sourceId] = $conditionals['fieldLayout:' . $fieldLayoutId];
            }
        }
        return $resources;

    }

    /**
     * @return array
     */
    protected function getAllToggleFieldTypes()
    {
        return array(
            // Stock FieldTypes
            'Lightswitch',
            'Dropdown',
            'Checkboxes',
            'MultiSelect',
            'RadioButtons',
            'Number',
            'PositionSelect',
            'PlainText',
            'Entries',
            'Categories',
            'Tags',
            'Assets',
            'Users',
            // Custom FieldTypes
            'Calendar_Event',
            'ButtonBox_Buttons',
            'ButtonBox_Colours',
            'ButtonBox_Stars',
            'ButtonBox_TextSize',
            'ButtonBox_Width',
			'SuperTableField'
        );
    }

    /*
    *   Returns all toggleable fields
    *
    */
    /**
     * @return array
     */
    protected function getAllToggleFields()
    {
        $toggleFieldTypes = $this->getAllToggleFieldTypes();
		
        $toggleFields = array();
        $fields = Craft::$app->fields->getAllFields();
		
        foreach ($fields as $field) {
	
			$fieldType = join('', array_slice(explode('\\', get_class($field)), -1));
			$classHandle = $fieldType;
            if (!$classHandle) {
                continue;
            }
            if (in_array($classHandle, $toggleFieldTypes)) {
                $toggleFields[] = array(
                    'id' => $field->id,
                    'handle' => $field->handle,
                    'name' => $field->name,
                    'type' => $classHandle,
                    'settings' => $field->settings,
                );
            }
        }
        return $toggleFields;
    }

    /**
     * @return array
     */
    protected function getFieldIds()
    {
        $handles = array();
        $fields = Craft::$app->fields->getAllFields();
        foreach ($fields as $field) {
            $handles[$field->handle] = $field->id;
        }
        return $handles;
    }
    /*
    *   Event handlers
    *
    */
    /**
     * @param Event $e
     */
    public function onSaveConditionalLayout(Event $e)
    {
		$conditionals = Craft::$app->getRequest()->getBodyParam('_conditions');
		
		$fieldLayout = $e->layout;
		$conditionalsModel = new ConditionalsModel();
		$conditionalsModel->fieldLayoutId = $fieldLayout->id;

		$conditionalsModel->conditionals = Craft::$app->getRequest()->getBodyParam('_conditions');
		$conditionsService = new ConditionsService();
		$conditionsService->saveConditionals($conditionalsModel);
	}
    
    // Protected Methods
    // =========================================================================
	
}
