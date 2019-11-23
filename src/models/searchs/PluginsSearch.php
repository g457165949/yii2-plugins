<?php

namespace zyh\plugins\models\searchs;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use zyh\plugins\models\Plugins;

/**
 * PluginsSearch represents the model behind the search form of `zyh\plugins\models\Plugins`.
 */
class PluginsSearch extends Plugins
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'state', 'category_id', 'create_time', 'update_time'], 'integer'],
            [['uid', 'title', 'name', 'author', 'intro', 'version', 'url'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
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
        $query = Plugins::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'state' => $this->state,
            'category_id' => $this->category_id,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
        ]);

        $query->andFilterWhere(['like', 'uid', $this->uid])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'author', $this->author])
            ->andFilterWhere(['like', 'intro', $this->intro])
            ->andFilterWhere(['like', 'version', $this->version])
            ->andFilterWhere(['like', 'url', $this->url]);

        return $dataProvider;
    }
}