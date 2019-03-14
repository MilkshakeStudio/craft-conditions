<?php
/**
 * Conditions plugin for Craft CMS 3.x
 *
 * conditions on fields
 *
 * @link      http://mobile.everybyte.in/
 * @copyright Copyright (c) 2019 W3care
 */

namespace craftconditions\conditions\services;

use craftconditions\conditions\Conditions;

use Craft;
use craft\base\Component;

use craftconditions\conditions\records\ConditionalsRecord as ConditionalsRecord;
/**
 * ConditionsService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    W3care
 * @package   Conditions
 * @since     1.0.0
 */
class ConditionsService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     Conditions::$plugin->conditionsService->exampleService()
     *
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';

        return $result;
    }
	  /**
     * @param ConditionalsModel $model
     * @return bool
     * @throws \Exception
     */
	
     public function saveConditionals( $model)
    {
		$record1 = new ConditionalsRecord();
		$result = $record1->find()->where(array('fieldLayoutId'=>$model->fieldLayoutId))->all();
		foreach($result as $row)
		{
			$row->delete();
		}
		if($model->conditionals != "")
		{
			$record = new ConditionalsRecord();
			$record->find()->where(array('fieldLayoutId'=>$model->fieldLayoutId))->all();
			$record->fieldLayoutId = $model->fieldLayoutId;
			$record->conditionals = $model->conditionals;
			$record->validate();
			$record->save();
			return true;
		}
		
        return false;

    }
	
}
