<?php
/**
 * Needletail plugin for Craft CMS 3.x
 *
 * Needletail Search and Index package for Craft 3.x
 *
 * @link      https://needletail.io
 * @copyright Copyright (c) 2019 Needletail
 */

namespace needletail\needletail;

use craft\events\ElementEvent;
use needletail\needletail\models\NeedletailModel;
use needletail\needletail\services\Buckets;
use needletail\needletail\services\Connection;
use needletail\needletail\services\DataTypes;
use needletail\needletail\services\Elements;
use needletail\needletail\services\Events;
use needletail\needletail\services\Fields;
use needletail\needletail\services\Hash;
use needletail\needletail\services\Logs;
use needletail\needletail\services\Process;
use needletail\needletail\services\Query;
use needletail\needletail\variables\NeedletailVariable;
use needletail\needletail\twigextensions\NeedletailTwigExtension;
use needletail\needletail\models\Settings;
use needletail\needletail\utilities\NeedletailUtility as NeedletailUtilityUtility;
use needletail\needletail\widgets\NeedletailWidget as NeedletailWidgetWidget;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\console\Application as ConsoleApplication;
use craft\web\UrlManager;
use craft\services\Utilities;
use craft\web\twig\variables\CraftVariable;
use craft\services\Dashboard;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;

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
 * @author    Needletail
 * @package   Needletail
 * @since     1.0.0
 *
 * @property  Buckets $buckets
 * @property  Connection $connection
 * @property  Elements $elements
 * @property  Events $events
 * @property  Fields $fields
 * @property  Hash $hash
 * @property  Logs $logs
 * @property  Process $process
 * @property  Query $query
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class Needletail extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * Needletail::$plugin
     *
     * @var Needletail
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

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * Needletail::$plugin
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

        // Add in our Twig extensions
        Craft::$app->view->registerTwigExtension(new NeedletailTwigExtension());

        // Add in our console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'needletail\needletail\console\controllers';
        }

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['needletail/buckets'] = 'needletail/buckets/index';
                $event->rules['needletail/buckets/new'] = 'needletail/buckets/edit';
                $event->rules['needletail/buckets/<bucketId:\d+>'] = 'needletail/buckets/edit';
                $event->rules['needletail/buckets/<bucketId:\d+>/map'] = 'needletail/buckets/map';
                $event->rules['needletail/buckets/<bucketId:\d+>/start'] = 'needletail/buckets/start';
            }
        );

        // Register our utilities
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = NeedletailUtilityUtility::class;
            }
        );

        // Register our widgets
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = NeedletailWidgetWidget::class;
            }
        );

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('needletail', NeedletailVariable::class);
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


        $this->setComponents([
            'buckets'    => Buckets::class,
            'connection' => Connection::class,
            'elements'   => Elements::class,
            'events'     => Events::class,
            'fields'     => Fields::class,
            'hash'       => Hash::class,
            'logs'       => Logs::class,
            'query'      => Query::class,
            'process'    => Process::class,
        ]);


        Event::on(\craft\services\Elements::class, \craft\services\Elements::EVENT_BEFORE_DELETE_ELEMENT, function (ElementEvent $event) {
            Needletail::$plugin->events->onDelete($event);
        });

        Event::on(\craft\services\Elements::class, \craft\services\Elements::EVENT_AFTER_RESTORE_ELEMENT, function (ElementEvent $event) {
            Needletail::$plugin->events->onRestore($event);
        });

        Event::on(\craft\services\Elements::class, \craft\services\Elements::EVENT_AFTER_SAVE_ELEMENT, function (ElementEvent $event) {
            Needletail::$plugin->events->onSave($event);
        });

        Event::on(\craft\services\Elements::class, \craft\services\Elements::EVENT_AFTER_UPDATE_SLUG_AND_URI, function (ElementEvent $event) {
            Needletail::$plugin->events->onUpdateSlugAndUri($event);
        });

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
                'needletail',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'needletail/settings',
            [
                'settings' => $this->getSettings(),
                'needletail' => (new NeedletailModel())
            ]
        );
    }

    public static function error($message, $params = [], $options = [])
    {
        static::$plugin->logs->log(__METHOD__, $message, $params, $options);
    }

    public static function info($message, $params = [], $options = [])
    {
        static::$plugin->logs->log(__METHOD__, $message, $params, $options);
    }

    public static function debug($message, $params = [])
    {
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }

        if (Craft::$app->getRequest()->getSegment(-1) === 'debug') {
            echo "<pre>";
            print_r($message);
            echo "</pre>";
        }
    }

}
