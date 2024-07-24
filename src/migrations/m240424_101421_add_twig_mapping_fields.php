<?php

namespace needletail\needletail\migrations;

use Craft;
use craft\db\Migration;

/**
 * m240424_101421_add_twig_mapping_fields migration.
 */
class m240424_101421_add_twig_mapping_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%needletail_buckets}}', 'customMappingFile')) {
            $this->addColumn('{{%needletail_buckets}}', 'customMappingFile', $this->boolean()->defaultValue(false)->after('siteId'));
        }

        if (!$this->db->columnExists('{{%needletail_buckets}}', 'mappingTwigFile')) {
            $this->addColumn('{{%needletail_buckets}}', 'mappingTwigFile', $this->string()->after('customMappingFile'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropColumn('{{%needletail_buckets}}', 'customMappingFile');
        $this->dropColumn('{{%needletail_buckets}}', 'mappingTwigFile');

        return true;
    }
}
