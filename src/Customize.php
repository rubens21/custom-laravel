<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2017-09-09
 * Time: 2:35 PM
 */

namespace CST21;

use CST21\Lib\MetaAttribute;
use CST21\Lib\MetaAttributeBelongsTo;
use CST21\Lib\MetaAttributeHasMany;
use CST21\Lib\MetaAttributeHasOne;
use CST21\Lib\MetaClass;
use Illuminate\Database\MySqlConnection;

class Customize
{
    /**
     * @var MySqlConnection
     */
    private $connection;

    private $ignoreTableList = ['migrations'];

    /**
     * @var MetaClass[]
     */
    private $classMap = [];

    /**
     * @var MetaAttribute[][]
     */
    private $referenciedTables = [];

    /**
     * @var \Doctrine\DBAL\Schema\Table[]
     */
    private $tables = [];

    /**
     * Customize constructor.
     *
     * @param MySqlConnection $connection
     */
    public function __construct(MySqlConnection $connection)
    {
        $this->connection = $connection;
    }


    public function map()
    {


        $this->connection
            ->getDoctrineConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');

        $tables = array_diff($this->connection->getDoctrineSchemaManager()->listTableNames(), $this->ignoreTableList);
        $this->mapTables($tables);
        $this->mapFields($tables);
//        $this->identifyRelationships();
    }

    private function mapTables(array $tables)
    {
        $tableComments = $this->getTablesComment();
        foreach ($tables as $tableName) {
            if(!in_array($tableName, $this->ignoreTableList)) {
                $this->tables[$tableName] = $this->connection->getDoctrineSchemaManager()->listTableDetails($tableName);
                $metaClass = new MetaClass($tableName);
                $metaClass->setComment($tableComments[$tableName]);
                $this->classMap[$tableName] = $metaClass;
            }
        }
    }

    private function mapFields(array $tables)
    {
        foreach ($tables as $tableName) {
            $metaClass = $this->classMap[$tableName];
            $uniqueFields = $this->getUniqueFields($tableName);
            $specializedField = [];
            foreach ($this->tables[$tableName]->getForeignKeys() as $fk) {
                foreach ($fk->getLocalColumns() as $fieldName) {
                    $specializedField[] = $fieldName;
                    $colunm = $this->tables[$tableName]->getColumn($fieldName);
                    $referenciedMetaClass = $this->classMap[$fk->getForeignTableName()];
                    //checar aqui se é um pivot, pois nesse caso será preciso add um meta attributo nas tabelas vizinhas
                    //e um hasMany ou HasOne na outr tabela
                    $metaClass->addField(new MetaAttributeBelongsTo($colunm, $referenciedMetaClass, $fk));
                    $docrineReferenciedTable = $this->connection->getDoctrineSchemaManager()->listTableDetails($fk->getForeignTableName());
                    $refericiedCol = $docrineReferenciedTable->getColumn($fk->getForeignColumns()[0]);
                    if(in_array($fieldName, $uniqueFields)) {
                        //hasOne!
                        $referenciedMetaClass->addField(new MetaAttributeHasOne($refericiedCol, $metaClass, $fk));
                    } else {
                        $referenciedMetaClass->addField(new MetaAttributeHasMany($refericiedCol, $metaClass, $fk));
                    }
                }
            }

            foreach ($this->tables[$tableName]->getColumns() as $col) {
                if(!in_array($col->getName(), $specializedField)) {
                    $metaClass->addField(new MetaAttribute($col));
                }
            }
        }

    }

    /**
     * @return array
     */
    protected function getTablesComment()
    {
        //SELECT TABLE_COMMENT FROM information_schema.TABLES
        //show table status where name='table_name';
        $comments = [];
        foreach ($this->connection->getPdo()->query('SHOW TABLE STATUS')->fetchAll() as $tableStatus){
            $comments[$tableStatus['Name']] = $tableStatus['Comment'];
        }
        return $comments;
    }

    public function getClasses()
    {
        return $this->classMap;
    }

    /**
     * Return all unique fields of a talbe
     * @todo It is not eveluating multiple field unique indexes.
     *
     * @param $tableName
     * @return array
     */
    private function getUniqueFields($tableName)
    {
        $fields = [];
        foreach ($this->tables[$tableName]->getIndexes() as $index) {
            if($index->isUnique() && count($index->getColumns()) == 1) {
                $fields[] = $index->getColumns()[0];
            }
        }
//        if(count($fields) > 1) {
//            dd($fields);
//        }
        return $fields;
    }

}