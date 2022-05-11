<?php

namespace common\models\admin;


/**
 * This is the ActiveQuery class for [[Field]].
 *
 * @see Field
 */
class UserQuery extends \app\components\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return Field[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Field|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
