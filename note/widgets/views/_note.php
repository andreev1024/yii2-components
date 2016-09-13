<?php
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="row <?= $s['class']['noteListItem'] ?>">
    <div class="col-sm-12">
        <div class="hpanel hgreen margin-top-20">
            <div class="panel-heading"></div>
            <div class="panel-body">
                <div class="<?= $s['class']['noteListContentViewArea'] ?>">
                    <div class="text-right">

                        <?= Html::a(
                            '',
                            Url::to('#'),
                            [
                                'class' => 'glyphicon glyphicon-pencil ' . $s['class']['update'],
                                'aria-hidden' => 'true'
                            ]
                        ); ?>

                         <?= Html::a(
                            '',
                                Url::to([$deleteAction]),
                                [
                                    'data' => [
                                        'custom-confirm' => 'Are you sure you want to delete this item?',
                                        'note-id' => $model->id,
                                    ],
                                    'class' => 'glyphicon glyphicon-remove text-danger ' . $s['class']['deleteAction'],
                                    'aria-hidden' => 'true'
                                ]
                         ); ?>

                    </div>
                    <div><?= $model->content ?></div>
                </div>
                <div class="<?= $s['class']['hidden'], ' ', $s['class']['noteListContentEditArea'] ?>">

                    <?php
                    $form = ActiveForm::begin([
                        'type' => ActiveForm::TYPE_VERTICAL,
                        'action' => Url::to([$updateAction]),
                        'options' => [
                            'class' => 'itemForm'
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
                        ->field($model, 'id')
                        ->label(false)
                        ->hiddenInput();
                    ?>

                    <div class="text-right">
                        <?= Html::submitButton('update', [
                            'class' => "btn btn-primary btn-xs {$s['class']['updateAction']}",
                        ]) ?>
                        <?= Html::button('cancel', [
                            'class' => "btn btn-danger btn-xs {$s['class']['cancel']}",
                        ]) ?>
                    </div>

                    <?php $form->end(); ?>

                </div>
            </div>

            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-9"></div>
                    <div class="col-sm-3 text-right">
                        <small>
                            <?= Yii::$app->formatter->asDate($model->updated_at, 'medium'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
