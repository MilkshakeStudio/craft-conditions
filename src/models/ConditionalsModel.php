<?php
/**
 * Conditions plugin for Craft CMS 3.x
 *
 * conditions on fields
 *
 * @link      http://mobile.everybyte.in/
 * @copyright Copyright (c) 2019 W3care
 */

namespace craftconditions\conditions\models;

use craftconditions\conditions\Conditions;

use Craft;
use craft\base\Model;

/**
 * ConditionalsModel Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    W3care
 * @package   Conditions
 * @since     1.0.0
 */
class ConditionalsModel extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some model attribute
     *
     * @var string
     */
    public $someAttribute = 'Some Default';
	public $fieldLayoutId = 'Some Default';
	public $conditionals = 'Some Default';

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['someAttribute', 'string'],
            ['someAttribute', 'default', 'value' => 'Some Default'],
        ];
    }
	protected function defineAttributes()
    {
        return array(
            'id' => AttributeType::Number,
            'fieldLayoutId' => AttributeType::Number,
            'conditionals' => AttributeType::Mixed,
        );
    }
	
}
