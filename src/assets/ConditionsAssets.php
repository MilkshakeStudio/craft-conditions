<?php
/**
 * Conditions plugin for Craft CMS 3.x
 *
 * Conditions
 *
 * @link      http://mobile.everybyte.in/
 * @copyright Copyright (c) 2019 Firoz Khan
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
 * @author    Firoz Khan
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
 		$cssFile = 'stylesheets/conditions.css';
        $jsFile = 'javascripts/conditions.js';
        $manifest = $this->getRevisionManifest();
        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
		
		$includeJsFile = $manifest ? $manifest->$jsFile : $jsFile;
		$includeCssFile = $manifest ? $manifest->$cssFile : $cssFile;
        $this->js = [
            $includeJsFile
        ];

        $this->css = [
            $includeCssFile
        ];

        parent::init();
    }
	  protected function getRevisionManifest()
    {
		
        $manifestPath = __DIR__. '/../resources/manifest.json';
		$result = file_get_contents($manifestPath);
		return json_decode($result);
       // return (IOHelper::fileExists($manifestPath) && $manifest = IOHelper::getFileContents($manifestPath)) ? json_decode($manifest) : false;
    }

    // Protected Methods
    // =========================================================================

}
