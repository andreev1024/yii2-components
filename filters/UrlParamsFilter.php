<?php
namespace andreev1024\filters;

/*
 * @author Andreev <andreev1024@gmail.com>
 * @since 2015-07-02
 * @version 1.0.5
 *
 * This filter checks url parameters according some criteria
 * and limit access to Controller Action.
 *
 * The Url parameter can be configured as required or not.
 * Its values can be checked in several ways:
 * - within callback func.;
 * - to equality with certain values;
 * - to existing in database.
 *
 * Configuration:
 *      Method
 *          Request methods fro which will applies a filter.
 *          For example: you set `get`, but request page by `post`. It's means that
 *          filter doesn't performed.
 *
 * Example:
 *
 *      'urlParamsFilter' => [
 *          'class' => UrlParamsFilter::className(),
 *          'only' => ['create'],                                       //  action
 *          'method' => 'post',                                         //  string, array (post, get)
 *          'config' => [
 *              'create' => [                                           //  action id
 *                  'firstParameter' => [                               //  parameter name
 *                      'values' => [                                   //  array/callback
 *                          'first allowed value',
 *                          'second allowed value',
 *                      ],
 *                      'model' => [                                    //  seacrh url parameter in db
 *                          'targetAttribute' => 'id',
 *                          'targetClass' => Invoice::className(),
 *                      ],
 *                      'required' => true                              //  parameter is required
 *                  ],
 *                  'secondParameter' => [
 *                      'values' => function($attribute) {
 *                          return is_numeric($attribut) ? true : false;
 *                      }
 *                  ]
 *              ]
 *          ]
 *      ],
 */

use Yii;
use yii\base\ActionFilter;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;

class UrlParamsFilter extends ActionFilter
{
    /**
     * @var array
     */
    public $config;

    /**
     * @var string request method
     */
    public $method = 'get';

    /**
     * @var array
     */
    protected $request;

    /**
     * Initialized.
     *
     * @author Andreev <andreev1024@gmail.com>
     */
    public function init()
    {
        if (!is_array($this->method)) {
            $this->method = [$this->method];
        }
    }

    /**
     * This method triggered before action run.
     *
     * @author Andreev <andreev1024@gmail.com>
     * @param \yii\base\Action $action
     *
     * @return bool
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function beforeAction($action)
    {
        if (!$this->config) {
            throw new InvalidConfigException('The "config" attribute must be set.');
        }

        if (!isset($this->config[$action->id])) {
            throw new InvalidConfigException('You forgot add config params for this action');
        }

        $method = strtolower(Yii::$app->request->method);
        if (!in_array($method, $this->method)) {
            return true;
        }

        $this->request = Yii::$app->request->$method();
        foreach ($this->config[$action->id] as $paramName => $paramConfig) {
            if (!isset($this->request[$paramName])) {
                if (!isset($paramConfig['required']) || !$paramConfig['required']) {
                    continue;
                }
                throw new BadRequestHttpException("Required parameter `{$paramName}` is skiped.");
            }

            if (isset($paramConfig['values'])) {
                $this->validateByValues($paramName, $paramConfig['values']);
            }

            if (isset($paramConfig['model'])) {
                $this->validateByModel($paramName, $paramConfig['model']);
            }
        }

        return parent::beforeAction($action);
    }

    /**
     * Validate the value to equality with certain values or callback func.
     *
     * @author Andreev <andreev1024@gmail.com>
     * @param string $paramName
     * @param mixed $paramConfig
     */
    protected function validateByValues($paramName, $paramConfig)
    {

        $validator = function ($value, $paramName, $paramConfig) {
            if (is_callable($paramConfig)) {
                if (!call_user_func($paramConfig, $value)) {
                    throw new BadRequestHttpException("The `{$paramName}` has invalid value.");
                }
            } elseif (is_array($paramConfig)) {
                if (!in_array($value, $paramConfig)) {
                    throw new BadRequestHttpException("The `{$paramName}` has invalid value.");
                }
            } else {
                throw new BadRequestHttpException("`values` can be only an array or callback func.");
            }
        };

        if (is_array($this->request[$paramName])) {
            foreach ($this->request[$paramName] as $value) {
                $validator($value, $paramName, $paramConfig);
            }
        } else {
            $validator($this->request[$paramName], $paramName, $paramConfig);
        }
    }

    /**
     * Check value existing in DB.
     *
     * @author Andreev <andreev1024@gmail.com>
     * @param string $paramName
     * @param mixed $paramConfig
     *
     * @throws BadRequestHttpException
     */
    protected function validateByModel($paramName, $paramConfig)
    {
        $class = $paramConfig['targetClass'];
        $attribute = $paramConfig['targetAttribute'];

        if (!$class::find()->where([$attribute => $this->request[$paramName]])->count()) {
            throw new BadRequestHttpException("The `{$paramName}` has invalid value.");
        }
    }
}
