#Note component 

This component adds Note functionality to your Yii2-application.

##Configuration

I'll try try to explain how to use this component on example. 
We have `Organization` and I going to add Note component.

*   Migration:
    *   create new migration;
    *   paste in new migration content from `noteComponent/migrations/example.php`;
    *   carefully read `example.php` description and follow all instructions;
    *   run migration;

*   Create Note model

```
    use andreev1024\note\models\BaseNote;
    
    class OrganizationNote extends BaseNote
    {
        public static function tableName()
        {
            return '{{%organization_note}}';
        }
    
        public function getParentClass()
        {
            return Organization::className();
        }
    }
```

*   Suppose, we want to add Note in `viewAction`. Then we should modify action similar to:

```
public function actionView()
{
    ...
    $noteModel = new OrganizationNote(['parent_id' => $model->id]);
    $noteDataProvider = new ActiveDataProvider([
        'query' => OrganizationNote::find()->where(['parent_id' => $model->id]),
        'pagination' => [
            'pageSize' => 10,
        ],
    ]);

    if (Yii::$app->request->isPost) {
        if ($noteModel->load(Yii::$app->request->post()) && $noteModel->save()) {
            $noteModel = new OrganizationNote(['parent_id' => $model->id]);   //reset model
        }
    }
    ...
}
```

*   Add actions in controller
```
    public function actions()
    {
        return [
            ...
            'delete-note' => [
                'class' => DeleteAction::className(),
                'modelClass' => OrganizationNote::className(),
            ],
            'update-note' => [
                'class' => UpdateAction::className(),
                'modelClass' => OrganizationNote::className(),
            ],
            ...
        ];
    }
```

*   Don't forget add actions to `access` and `verb` controller behavior; 

*   Add widget in view

```
    <?= NoteWidget::widget([
        'model' => $noteModel,
        'dataProvider' => $noteDataProvider,
    ]) ?>
```