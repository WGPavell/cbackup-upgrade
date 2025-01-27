<?php
/**
 * This file is part of cBackup, network equipment configuration backup tool
 * Copyright (C) 2017, Oļegs Čapligins, Imants Černovs, Dmitrijs Galočkins
 *
 * cBackup is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace app\models;

use Yii;
use \yii\db\ActiveRecord;
use \yii\behaviors\TimestampBehavior;
use \yii\db\Expression;


/**
 * This is the model class for table "{{%out_ospf_backup}}".
 *
 * @property integer $id
 * @property string $time
 * @property integer $node_id
 * @property string $hash
 * @property string $ospf_config
 *
 * @property Node $node
 *
 * @package app\models
 */
class OutOspfBackup extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%out_ospf_backup}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['time'], 'safe'],
            [['node_id', 'hash'], 'required'],
            [['node_id'], 'integer'],
	    [['ospf_config'], 'string'],
            [['hash'], 'string', 'max' => 255],
            [['node_id'], 'unique'],
            [['node_id'], 'exist', 'skipOnError' => true, 'targetClass' => Node::class, 'targetAttribute' => ['node_id' => 'id']],
            [['ospf_config'], 'default', 'value' => null],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'      => Yii::t('app', 'ID'),
            'node_id' => Yii::t('app', 'Node ID'),
            'time'    => Yii::t('app', 'Time'),
            'hash'    => Yii::t('app', 'Hash'),
            'config'  => Yii::t('app', 'Config'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNode()
    {
        return $this->hasOne(Node::class, ['id' => 'node_id']);
    }

    /**
     * Behaviors
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'time',
                'updatedAtAttribute' => 'time',
                'value' => new Expression('NOW()'),
            ],
        ];
    }
}

