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
        $this->identifyRelationships();
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

                    //checar aqui se é um pivot, pois nesse caso será preciso add um meta attributo nas tabelas vizinhas
                    //e um hasMany ou HasOne na outr tabela
                    $metaClass->addField(new MetaAttributeBelongsTo($colunm, $fk));
                    echo "\n";
                    echo $fk->getForeignTableName();
                    echo "\n";
                    echo $fk->getForeignColumns()[0];
                    echo "\n";
                    $docrineTable = $this->connection->getDoctrineSchemaManager()->listTableDetails($fk->getForeignTableName());
                    $refericiedCol = $docrineTable->getColumn($fk->getForeignColumns()[0]);
                    if(in_array($fieldName, $uniqueFields)) {
                        //hasOne!
                        $this->referenciedTables[$fk->getForeignTableName()][] = new MetaAttributeHasOne($refericiedCol, $metaClass, $fk, $fieldName);
                    } else {
                        $this->referenciedTables[$fk->getForeignTableName()][] = new MetaAttributeHasMany($refericiedCol, $metaClass, $fk, $fieldName);
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

    private function identifyRelationships()
    {
        foreach ($this->referenciedTables as $tableName => $metaAttributes) {
            foreach ($metaAttributes as $metaAttribute)
            $this->classMap[$tableName]->addField($metaAttribute);
//            foreach ($rel as $localCol => $fk) {/** @var \Doctrine\DBAL\Schema\ForeignKeyConstraint $fk */
//                $metaClass = $this->classMap[$tableName];
//                $referencedMetaClass = $this->classMap[$fk->getForeignTableName()];
//
//                //let's tell to the class what is the other class it refereces to
//                $this->classMap[$tableName]->getField($localCol)->setForeignKeyMetaClass($referencedMetaClass);
//                if($metaClass->isAPivot()) {
//                    //it will be run twice because a pivot references to 2 tables
//
//                    $referencedTables = $metaClass->getPivotedTalbes();
//
//
//                    $otherReferencedTableName = ($referencedTables[0] == $referencedMetaClass->getTableName()) ? $referencedTables[1] : $referencedTables[0];
//                    $otherReferencedMetaClass = $this->classMap[$otherReferencedTableName];
//                    foreach ($metaClass->getFields() as $fieldName => $field){
////                        if($field->isForeingKey() && $field->getForeignKeyMetaClass()->getTableName() === $otherReferencedTableName){
////                            foreach ($field->getForeignKeyMetaClass()->)
////
////                        }
////                        $otherTableFieldName =
//                    }
//
//                    $referencedMetaClass->addPivotedFieldReference($otherReferencedMetaClass, $metaClass, $fk->getForeignColumns()[0], 'dd', $localCol, 'dududu' );
//
//                    //$this->belongsToMany('App\Role', 'role_user', 'user_id', 'role_id')->using('App\UserRole');;
//                }else {
//                    $referencedMetaClass->addFieldReference($localCol, $metaClass, in_array($localCol, $uniqueFields));
//                }
//
//                //$foreignColName = $fk->getForeignColumns()[0];//more fields in the future MAYBE!
//
//
//                    //next steps: pegar pivot
////                $this->belongsToMany('App\Role', 'role_user', 'user_id', 'role_id');
//           }
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