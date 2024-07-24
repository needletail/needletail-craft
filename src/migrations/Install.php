<?php
/**
 * Needletail plugin for Craft CMS 3.x
 *
 * Needletail Search and Index package for Craft 3.x
 *
 * @link      https://needletail.io
 * @copyright Copyright (c) 2019 Needletail
 */

namespace needletail\needletail\migrations;

use needletail\needletail\Needletail;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;
use needletail\needletail\records\BucketRecord;

/**
 * Needletail Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    Needletail
 * @package   Needletail
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

    // needletail_needletailrecord table
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%needletail_buckets}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%needletail_buckets}}',
                [
                    'id' => $this->primaryKey(),
                    'name' => $this->string()->notNull(),
                    'handle' => $this->string(),
                    'elementType' => $this->string(),
                    'elementData' => $this->text(),
                    'fieldMapping' => $this->text(),
                    'siteId' => $this->integer(),
                    'customMappingFile' => $this->boolean()->defaultValue(false),
                    'mappingTwigFile' => $this->string(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {
    // needletail_needletailrecord table
//        $this->createIndex(
//            $this->db->getIndexName(
//                '{{%needletail_needletailrecord}}',
//                'some_field',
//                true
//            ),
//            '{{%needletail_needletailrecord}}',
//            'some_field',
//            true
//        );
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(
            $this->db->getForeignKeyName(BucketRecord::tableName(), 'siteId'),
            BucketRecord::tableName(),
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
    // needletail_needletailrecord table
        $this->dropTableIfExists('{{%needletail_buckets}}');
    }
}
