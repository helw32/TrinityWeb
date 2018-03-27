<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use common\modules\forum\widgets\gridview\ActionColumn;
use common\modules\forum\widgets\gridview\GridView;
use yii\grid\CheckboxColumn;
use yii\helpers\Html;

$this->title = Yii::t('view', 'Subscriptions');
Yii::$app->params['breadcrumbs'][] = ['label' => Yii::t('view', 'My Profile'), 'url' => ['profile/index']];
Yii::$app->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-md-3 col-sm-4">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'subscriptions']) ?>
    </div>
    <div class="col-md-9 col-sm-8">
        <h4><?= Yii::t('view', 'Subscriptions') ?></h4>
        <?= Html::beginForm(); ?>
<?= GridView::widget([
    'dataProvider'   => $dataProvider,
    'columns' => [
        [
            'class' => CheckboxColumn::className(),
            'headerOptions' => ['class' => 'col-sm-1 text-center'],
            'contentOptions' => ['class' => 'col-sm-1 text-center'],
            'checkboxOptions' => function($model) {
                return ['value' => $model->id];
            }
        ],
        [
            'attribute' => 'thread.name',
            'label' => Yii::t('view', "Thread's Name"),
            'format' => 'raw',
            'value' => function ($model) {
                return Html::a($model->thread->name, ['forum/show', 'id' => $model->thread->latest->id], ['class' => 'center-block']);
            },
        ],
        [
            'attribute' => 'post_seen',
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
            'label' => Yii::t('view', 'New Posts'),
            'format' => 'raw',
            'value' => function ($model) {
                return $model->post_seen ? '' : '<span class="glyphicon glyphicon-ok-sign"></span>';
            },
        ],
        [
            'class' => ActionColumn::className(),
            'template' => '{mark} {delete}',
            'buttons' => [
                'mark' => function($url, $model) {
                    if ($model->post_seen) {
                        return Html::a('<span class="glyphicon glyphicon-eye-close"></span> <span class="hidden-sm">' . Yii::t('view', 'Mark unseen') . '</span>', $url, [
                            'class' => 'btn btn-warning btn-xs'
                        ]);
                    }
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span> <span class="hidden-sm">' . Yii::t('view', 'Mark seen') . '</span>', $url, [
                        'class' => 'btn btn-success btn-xs'
                    ]);
                },
                'delete' => function($url, $model) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span> <span class="hidden-sm">' . Yii::t('view', 'Unsubscribe') . '</span>', $url, [
                        'class' => 'btn btn-danger btn-xs'
                    ]);
                },
            ],
        ]
    ],
]); ?>
            <div class="row">
                <div class="col-sm-12">
                    <?= Html::submitButton('<span class="glyphicon glyphicon-trash"></span> ' . Yii::t('view', 'Unsubscribe Selected Threads'), ['class' => 'btn btn-danger btn-sm', 'name' => 'delete-button']) ?>
                </div>
            </div>
        <?= Html::endForm(); ?>
    </div>
</div><br>
