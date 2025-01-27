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

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\LogScheduler;


/**
 * LogSchedulerSearch represents the model behind the search form about `app\models\LogScheduler`.
 * @package app\models\search
 */
class LogSchedulerSearch extends LogScheduler
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'schedule_id', 'node_id'], 'integer'],
            [['userid', 'time', 'severity', 'action', 'message', 'node_name', 'date_from', 'date_to', 'page_size'], 'safe'],
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
     * @return ActiveDataProvider
     */
    public function search($params)
    {

        $query = LogScheduler::find();

        $query->joinWith(['node n']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ]
        ]);

        $this->load($params);

        /** Set page size dynamically */
        $dataProvider->pagination->pageSize = $this->page_size;

        /** Time interval search  */
        if (!empty($this->date_from) && !empty($this->date_to)) {
            $query->andFilterWhere(['between', 'time', $this->date_from, $this->date_to]);
		
        }
        elseif (!empty($this->date_from)) {
            $query->andFilterWhere(['>=', 'time', $this->date_from]);
        }
        elseif (!empty($this->date_to)) {
            $query->andFilterWhere(['<=', 'time', $this->date_to]);
        }

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id'          => $this->id,
            'time'        => $this->time,
            'schedule_id' => $this->schedule_id,
            'node_id'     => $this->node_id,
        ]);

        $query->andFilterWhere(['like', 'userid', $this->userid])
            ->andFilterWhere(['like', 'severity', $this->severity])
            ->andFilterWhere(['like', 'action', $this->action])
            ->andFilterWhere(['like', 'n.hostname', $this->node_name])
            ->andFilterWhere(['like', 'message', $this->message]);
	
	//echo "<pre>";
	//print_r($dataProvider);
	//exit();

        return $dataProvider;

    }
}
