<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "auth_item_node".
 *
 * @property string $auth_item_name
 * @property int $node_id
 * @property string $created_at
 */
class AuthItemNode extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_item_node';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['auth_item_name', 'node_id'], 'required'],
            [['node_id'], 'integer'],
            [['created_at'], 'safe'],
            [['auth_item_name'], 'string', 'max' => 64],
            [['auth_item_name', 'node_id'], 'unique', 'targetAttribute' => ['auth_item_name', 'node_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'auth_item_name' => 'Auth Item Name',
            'node_id' => 'Node ID',
            'created_at' => 'Created At',
        ];
    }
}
