<?php

namespace andreev1024\note\actions;

use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;

/**
 * Delete note action class.
 */
class DeleteAction extends Action
{
    /**
     * @var stirng
     */
    public $modelClass;

    /**
     * Delete model.
     *
     * @author Andreev <andreev1024@gmail.com>
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws MethodNotAllowedHttpException
     * @throws NotFoundHttpException
     */
    public function run()
    {
        if (!Yii::$app->request->isPost) {
           throw new MethodNotAllowedHttpException();
        }

        if ($id = Yii::$app->request->post('id')) {

            if (!$this->modelClass) {
                throw new InvalidConfigException();
            }

            $class = $this->modelClass;
            if(!$model = $class::findOne($id)) {
                throw new NotFoundHttpException('The requested item does not exist.');
            }

            $model->delete();

        } else {
            throw new BadRequestHttpException('The requested parameter (id) is missed.');
        }
    }
}
