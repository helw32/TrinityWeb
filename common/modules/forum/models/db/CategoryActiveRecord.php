<?php

namespace common\modules\forum\models\db;

use common\modules\forum\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\HtmlPurifier;
use common\modules\forum\Podium;
use common\modules\forum\slugs\PodiumSluggableBehavior;

/**
 * Category model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 *
 * @property integer $id
 * @property string $name
 * @property string $slug
 * @property string $keywords
 * @property string $description
 * @property integer $visible
 * @property integer $create_thread
 * @property integer $sort
 * @property integer $updated_at
 * @property integer $created_at
 */
class CategoryActiveRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%forum_category}}';
    }
    
    public function __construct($config = array()) {
        parent::__construct($config);
        $this->create_thread = 1;
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class' => Podium::getInstance()->slugGenerator,
                'attribute' => 'name',
                'type' => PodiumSluggableBehavior::CATEGORY
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'visible'], 'required'],
            ['visible', 'boolean'],
            ['name', 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process(trim($value));
            }],
            [['keywords', 'description'], 'string'],
            [['create_thread'], 'boolean'],
        ];
    }
}
