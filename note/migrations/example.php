<?php

use yii\db\Schema;
use jamband\schemadump\Migration;

/**
 * Class-example
 *
 * 1.   create new migration;
 * 2.   paste in new migration this class content;
 * 3.   edit $parentName;
 * 4.   run migration;
 */
class Example extends Migration
{
    //  Edit only this property
    public $parentName = 'organization';

    protected $tableSchema;
    protected $parentTableSchema;

    public function init()
    {
        parent::init();
        $this->tableSchema = $this->db->schema->getTableSchema($this->getTableName(), true);
        $this->parentTableSchema = $this->db->schema->getTableSchema($this->getParentTableName(), true);
    }

    public function safeUp()
    {
        if ($this->tableSchema || !$this->parentTableSchema) {
            return true;
        }

        $this->createTable($this->getTableName(), [
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer()->notNull(),
            'content' => $this->text()->notNull(),
            'created_at' => $this->timestamp()->notNull() . ' DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => $this->timestamp()->notNull()->defaultValue('0000-00-00 00:00:00'),
            'created_by' => $this->integer() . ' DEFAULT NULL',
            'updated_by' => $this->integer() . ' DEFAULT NULL',
        ], $this->tableOptions);

        $this->addForeignKey(
            $this->getFkName(),
            $this->getTableName(),
            'parent_id',
            $this->getParentTableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        if ($this->tableSchema) {
            $this->dropForeignKey($this->getFkName(), $this->getTableName());
            $this->dropTable($this->getTableName());
        }
    }

    public function getTableName()
    {
        return "{{%{$this->parentName}_note}}";
    }

    public function getParentTableName()
    {
        return "{{%{$this->parentName}}}";
    }

    public function getFkName()
    {
        return "fk__{$this->parentName}_note__parent_id";
    }
}
