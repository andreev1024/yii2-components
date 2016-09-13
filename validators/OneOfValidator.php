<?php

namespace andreev1024\validators;

use yii\helpers\Json;
use yii\validators\Validator;
use yii\base\InvalidConfigException;
use Yii;

/**
 * This validator helps you do `required` only one field from group.
 * It's behavior similar at Html radio button.
 * If you determine defaultValue, then fields with this value equal
 * empty.
 *
 * Example:
 *
 *      public function rules()
 *      {
 *          return [
 *              ['qty_in', 'qty_out'],
 *                  OneOfValidator::className(),
 *                  'group' => ['qty_in', 'qty_out'],
 *                  'skipOnEmpty' => false,
 *                  'defaultValue' => 0     //  if we have field with val. = 1 and some
 *                                          //  fields with value = 0 - form will be valide.
 *              ],
 *          ];
 *      }
 *
 */

class OneOfValidator extends Validator
{
    /**
     * Array with attribute names.
     * @var array
     */
    public $group = [];

    public $defaultValue = null;

    //protected $jsDefaultValue

    /**
     * Initialization.
     * @author Andreev <andreev1024@gmail.com>
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (!$this->group) {
            throw new InvalidConfigException('Attribute `group` is missed.');
        }
    }

    /**
     * Serverside validation.
     * @author Andreev <andreev1024@gmail.com>
     * @param \yii\base\Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $count = 0;

        foreach ($this->group as $item) {
            $model->$item = trim($model->$item);
            if ((string) $model->$item === (string) $this->defaultValue) {
                continue;
            }

            if (isset($model->$item) && $model->$item !== '') {
               $count++;
            }
        }

        if (!$count && $this->skipOnEmpty) {
            return;
        }

        if ($count !== 1) {
            $model->addError($attribute, $this->getMessage($model));
        }
    }

    /**
     * Clientside validation.
     *
     * @author Andreev <andreev1024@gmail.com>
     * @param \yii\base\Model $model
     * @param string $attribute
     * @param \yii\web\View $view
     *
     * @return string
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        $formName = $model->formName();
        $group = Json::encode($this->group);
        $message = $this->getMessage($model);
        $defaultValue = (string) $this->defaultValue;
        $skipOnEmpty = $this->skipOnEmpty ? 'true' : 'false';

        $js = "
            var formName = '{$formName}',
                group = {$group},
                defaultValue = '{$defaultValue}',
                count = 0;

            for (var p in group) {
                var selector = formName + '[' + group[p] + ']',
                    element = $('[name=\'' + selector + '\']'),
                    value = element.val().trim();

                    if (value === defaultValue) {
                        return;
                    }

                    if (element.val()) {
                        count++;
                    }
            }

            if (count === 0 && {$skipOnEmpty}) {
                return;
            }

            if (count !== 1) {
                messages.push('{$message}');
            }
        ";

        return $js;
    }

    /**
     * Return error message.
     *
     * @author Andreev <andreev1024@gmail.com>
     * @param $model
     *
     * @return string
     */
    protected function getMessage($model)
    {
        $msg = Yii::$app->translate->t('You must fill only one attribute from the set');
        $labels = $model->attributeLabels();
        $group = [];
        foreach ($this->group as $item) {
            $group[] = $labels[$item];
        }

        return $msg . ' (' . join(', ',$group) . ')';
    }
}
