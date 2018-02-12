<?php

namespace frontend\modules\ladder\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;
use common\models\chars\ArenaTeam;
use common\models\chars\ArenaTeamMember;

/**
 * FormModel
 */
class SearchFormModel extends Model
{
    public $realm_id = null;
    public $type = null;
    
    public $_arr_types = [
        2 => '2x2',
        3 => '3x3',
        5 => '5x5'
    ];
    
    const TYPE_2 = 2;// 2vs2
    const TYPE_3 = 3;// 3vs3
    const TYPE_5 = 5;// 5vs5 / soloq
    
    public function __construct($config = array()) {
        parent::__construct($config);
        if($_id = Yii::$app->CharactersDbHelper->getServerId()) {
            $this->realm_id = $_id;
        }
        $this->type = $this->_arr_types[2];
    }
    
    public function formName() {
        return '';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['realm_id', 'type'], 'required'],
            [['realm_id', 'type'], 'integer'],
        ];
    }
    
    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'realm_id' => Yii::t('ladder', 'Игровой мир'),
            'type' => Yii::t('ladder', 'Тип')
        ];
    }
    
    public function search($params) {
        $query = ArenaTeam::find()->orderBy(['rating' => SORT_DESC])->with(['relationMembers.relationCharacter'])->asArray();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 25,
            ]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        
        $query->andFilterWhere([
            'type' => $this->type
        ]);

        return $dataProvider;
    }
}