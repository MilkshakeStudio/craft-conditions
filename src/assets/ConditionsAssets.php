<?php
/**
 * Conditions plugin for Craft CMS 3.x
 *
 * Conditions
 *
 * @link      http://milkshake.studio
 * @copyright Copyright (c) 2019 Milkshake Studio
 */

namespace craftconditions\conditions\assets;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\garnish\GarnishAsset;
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
 * @author    Milkshake Studio
 * @package   Conditions
 * @since     1.0.11
 *
 */

class ConditionsAssets extends AssetBundle
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     *
     */
    public static $plugin;
	

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
	public $sourcePath = "";
	public $depends = "";
	public $js = "";
	public $css = "";

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
        // define the path that your publishable resources live
        $this->sourcePath = '@craftconditions/conditions/resources';

        // define the dependencies
        $this->depends = [
			GarnishAsset::class,
			CpAsset::class,
        ];
 		$cssFile = 'stylesheets/conditions-btest9098.css';
        $jsFile = 'javascripts/conditions-atest3dc3r.js';
        $this->js = [
            $jsFile
        ];

        $this->css = [
            $cssFile
        ];

        parent::init();
    }

    // Protected Methods
    // =========================================================================

}
