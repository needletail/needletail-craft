<?php

namespace needletail\needletail\records;

use craft\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string $handle
 * @property string $elementType
 * @property mixed $elementData
 * @property mixed $fieldMapping
 * @property bool $customMappingFile
 * @property string|null $mappingTwigFile
 * @property int $siteId
 * @property \DateTime $dateCreated
 * @property \DateTime $dateUpdated
 * @property string $uid
 */
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