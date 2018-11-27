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

namespace app\models\search;

use yii\data\ArrayDataProvider;
use yii\base\Model;
use app\modules\rbac\models\AuthItem;
use app\models\AuthItemNode;


/**
 * RoleSearch represents the model behind the search form about `app\models\Device`.
 * @package app\models\search
 */
class RoleSearch extends AuthItem
{

    /**
     * @var string
     */
    public $node_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['page_size'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ArrayDataProvider
     */
    public function search($params)
    {

        $query = AuthItem::find()->select('name');

        $this->load($params);

        $query->andFilterWhere(['like', 'name', $this->name]);

        $data = [];

        $data = $query->orderBy(['created_at' => SORT_DESC])->asArray()->all();

        foreach ($data as $key => $entry) {
            $denied_exists = AuthItemNode::find()->where(['node_id' => $params['id'], 'auth_item_name' => $entry['name']])->exists();
            $data[$key] += ($denied_exists) ? ['role_has_node' => true] : ['role_has_node' => false];
        }

        $provider = new ArrayDataProvider([
            'allModels'  => $data,
        ]);

        /** Set page size dynamically */
        $provider->pagination->pageSize = $this->page_size;

        return $provider;

    }

}
