<?php

namespace andreev1024\note\actions;

use Yii;
use yii\base\Action;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;

/**
 * Update note action class.
 */
class UpdateAction extends Action
{
    /**
     * @var string
     */
    public $modelClass;

    /**
     * Update model.
     *
     * @author Andreev <andreev1024@gmail.com>
     * @throws MethodNotAllowedHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function run()
    {
        if (!Yii::$app->request->isPost) {
            throw new MethodNotAllowedHttpException();
        }

        $modelClass = $this->modelClass;
        $postData = Yii::$app->request->post((new $modelClass)->formName());
        $id = isset($postData['id']) ? $postData['id'] : null;
        $model = $modelClass::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if (!$model->load(Yii::$app->request->post()) || !$model->save()) {
            throw new \Exception('Save data error!');
        }
    }
}
