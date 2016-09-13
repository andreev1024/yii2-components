<?php

namespace andreev1024\note\widgets;

use Yii;
use yii\base\Widget;

/**
 * Note widget
 * @package andreev1024\note
 */
class NoteWidget extends Widget
{
    public $model;
    public $dataProvider;
    public $deleteAction = 'delete-note';
    public $updateAction = 'update-note';

    public function run()
    {
        return $this->render("index", [
            'model' => $this->model,
            'dataProvider' => $this->dataProvider,
            'deleteAction' => $this->deleteAction,
            'updateAction' => $this->updateAction,
        ]);
    }
}
