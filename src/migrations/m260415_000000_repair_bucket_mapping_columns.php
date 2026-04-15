<?php

namespace needletail\needletail\migrations;

use Craft;
use craft\db\Migration;

/**
 * Repairs bucket mapping columns for installs created before Install.php included them.
 */
class m260415_000000_repair_bucket_mapping_columns extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%needletail_buckets}}', true);

        if ($tableSchema === null) {
            return true;
        }

        if (!isset($tableSchema->columns['customMappingFile'])) {
            $this->addColumn('{{%needletail_buckets}}', 'customMappingFile', $this->boolean()->defaultValue(false)->after('siteId'));
            Craft::$app->db->schema->refresh();
            $tableSchema = Craft::$app->db->schema->getTableSchema('{{%needletail_buckets}}', true);
        }

        if ($tableSchema !== null && !isset($tableSchema->columns['mappingTwigFile'])) {
            $this->addColumn('{{%needletail_buckets}}', 'mappingTwigFile', $this->string()->after('customMappingFile'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%needletail_buckets}}', true);

        if ($tableSchema === null) {
            return true;
        }

        if (isset($tableSchema->columns['mappingTwigFile'])) {
            $this->dropColumn('{{%needletail_buckets}}', 'mappingTwigFile');
            Craft::$app->db->schema->refresh();
            $tableSchema = Craft::$app->db->schema->getTableSchema('{{%needletail_buckets}}', true);
        }

        if ($tableSchema !== null && isset($tableSchema->columns['customMappingFile'])) {
            $this->dropColumn('{{%needletail_buckets}}', 'customMappingFile');
        }

        return true;
    }
}
