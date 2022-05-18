<?php
/**
 * Needletail plugin for Craft CMS 3.x
 *
 * Needletail Search and Index package for Craft 3.x
 *
 * @link      https://needletail.io
 * @copyright Copyright (c) 2019 Needletail
 */

namespace needletail\needletail\models;

use Craft;
use craft\base\Model;

/**
 * Needletail Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Needletail
 * @package   Needletail
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * The public api key used for searching Needletail
     *
     * @var string
     */
    public $apiReadKey = '';

    /**
     * The private api key used for index new documents
     *
     * @var string
     */
    public $apiWriteKey = '';

    /**
     * Prefix bucket names
     *
     * @var string
     */
    public $bucketPrefix = '';

    /**
     * Setting to process single element actions, such as save or delete
     * via the Craft queue
     *
     * @var bool
     */
    public $processSingleElementsViaQueue = true;

    /**
     * Prefix bucket names
     *
     * @var string
     */
    public $disableIndexingOnNonProduction = false;

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['apiReadKey', 'apiWriteKey', 'bucketPrefix'], 'string'],
            [['disableIndexingOnNonProduction', 'processSingleElementsViaQueue'], 'boolean'],
        ];
    }

    /**
     * API Keys can be set in environment variable. Therefore
     * we need to parse the keys on retrieval.
     *
     * @return bool|string|null
     */
    public function getApiReadKey()
    {
        return Craft::parseEnv($this->apiReadKey);
    }

    /**
     * API Keys can be set in environment variable. Therefore
     * we need to parse the keys on retrieval.
     *
     * @return bool|string|null
     */
    public function getApiWriteKey()
    {
        return Craft::parseEnv($this->apiWriteKey);
    }

    /**
     * Bucket Prefix can be set in environment variable. Therefore
     * we need to parse the keys on retrieval.
     *
     * @return bool|string|null
     */
    public function getBucketPrefix()
    {
        return trim(Craft::parseEnv($this->bucketPrefix));
    }
}
