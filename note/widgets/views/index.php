<?php
use kartik\form\ActiveForm;
use andreev1024\note\assets\NoteAsset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ListView;
use yii\widgets\Pjax;

NoteAsset::register($this);

//  selectors
$s = [
    'id' => [
        'noteBtn' => 'noteBtn',
        'noteContainer' => 'noteContainer',
        'listContainer' => 'listContainer',
        'pjax' => 'notePjax',
        'sendNoteFormContainer' => 'sendNoteFormContainer',
        'sendNoteFormCloseBtn' => 'sendNoteFormCloseBtn',
    ],
    'class' => [
        'noteListContentEditArea' => 'noteListContentEditArea',
        'noteListContentViewArea' => 'noteListContentViewArea',
        'noteListItem' => 'noteListItem',
        'deleteAction' => 'noteItemDeleteAction',
        'updateAction' => 'noteItemUpdateAction',
        'update' => 'noteItemUpdate',
        'cancel' => 'noteItemCancel',
        'itemForm' => 'itemForm',
        'hidden' => 'hidden',
    ]
];

if ($model->hasErrors()) {
    $formClass = '';
    $listClass = $s['class']['hidden'];
} else {
    $formClass = $s['class']['hidden'];
    $listClass = '';
}

$pjaxTimeout = 5000;

?>
<div id="<?= $s['id']['noteContainer'] ?>">

    <?php Pjax::begin(['id' => $s['id']['pjax'], 'timeout' => $pjaxTimeout]); ?>

    <div class="row col-sm-12">
        <a id="<?= $s['id']['noteBtn'] ?>" class="btn btn-primary">
            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>note
        </a>
    </div>

    <div id="<?= $s['id']['sendNoteFormContainer'] ?>" class="small-header col-sm-12 <?= $formClass ?>">
        <div class="form-group">

            <?php
            $form = ActiveForm::begin([
                'type' => ActiveForm::TYPE_VERTICAL,
                'options' => [
                    'data-pjax' => true
                ]
            ]);
            ?>
                <?= $form
                    ->field($model, 'content')
                    ->label(false)
                    ->textArea([
                        'rows' => '6',
                        'class' => 'form-control margin-top-10',
                    ]);
                ?>

                <?= $form
                    ->field($model, 'parent_id')
                    ->label(false)
                    ->hiddenInput();
                ?>

                <div class="text-right">
                    <?= Html::button('close', [
                        'class' => 'btn btn-default',
                        'id' => $s['id']['sendNoteFormCloseBtn'],
                    ]) ?>
                    <?= Html::submitButton('add', [
                        'class' => 'btn btn-success',
                    ]) ?>
                </div>

            <?php
            $form->end();
            ?>
        </div>
    </div>

    <div id="<?= $s['id']['listContainer'] ?>" class="col-sm-12 row <?= $listClass ?>">
        <div>
            <?= ListView::widget([
                'dataProvider' => $dataProvider,
                'itemView' => '_note',
                'viewParams' => [
                    's' => $s,
                    'deleteAction' => $deleteAction,
                    'updateAction' => $updateAction,
                ],
            ]); ?>
        <div>
    </div>
    <?php Pjax::end(); ?>
</div>

<?php
$s = Json::encode($s);
$js = <<<JS
if (typeof note === "undefined") {
    var note = {
        s : {$s},
        pjaxTimeout : {$pjaxTimeout}
    };
}
JS;
$this->registerJs($js, $this::POS_HEAD, 'noteSelectors');
