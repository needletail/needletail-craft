<?php

namespace needletail\needletail\records;

use craft\db\ActiveRecord;

class BucketRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%needletail_buckets}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'handle'], 'required'],
            [['handle'], 'unique']
        ];
    }
}