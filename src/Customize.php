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
use CST21\Lib\MetaAttributeBelongsToMany;
use CST21\Lib\MetaAttributeHasMany;
use CST21\Lib\MetaAttributeHasOne;
use CST21\Lib\MetaClass;
use CST21\Lib\MetaPivot;
use Illuminate\Database\DatabaseManager;
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


    private $config = [
        'path' => './',
        'namespace' => 'App',
    ];

    /**
     * Customize constructor.
     *
     * @param DatabaseManager $connection
     * @param array $config
     */
    public function __construct(DatabaseManager $connection, array $config = [])
    {
        $this->connection = $connection;
        if($config)
            $this->config = $config;
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
    }

    public function saveFiles($defaultNSDir)
    {
        foreach ($this->getClasses() as $metaClass){
            $relativeFilePath = $defaultNSDir.DIRECTORY_SEPARATOR.$metaClass->getRelativeFilePath();
            $this->createDirIfNotExist(pathinfo($relativeFilePath, PATHINFO_DIRNAME));
            file_put_contents($relativeFilePath, $metaClass->generateCode());
        }
    }

    private function mapTables(array $tables)
    {
        $tableComments = $this->getTablesComment();
        foreach ($tables as $tableName) {
            if(!in_array($tableName, $this->ignoreTableList)) {
                $this->tables[$tableName] = $this->connection->getDoctrineSchemaManager()->listTableDetails($tableName);
                $metaClass = new MetaClass($tableName);
                $metaClass->setBaseNamespace($this->config['namespace']);
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
                    $referenciedMetaClass = $this->classMap[$fk->getForeignTableName()];

                    $docrineReferenciedTable = $this->connection->getDoctrineSchemaManager()->listTableDetails($fk->getForeignTableName());
                    $refericiedCol = $docrineReferenciedTable->getColumn($fk->getForeignColumns()[0]);

                    if($metaClass->isAPivot()) {
                        $metaPivot = $this->getMetaPivot($tableName);
                        $metaAttribute = new MetaAttributeBelongsToMany($refericiedCol, $metaPivot, $metaPivot->getIndex($referenciedMetaClass));
                    } elseif(in_array($fieldName, $uniqueFields)) {
                        $metaAttribute = new MetaAttributeHasOne($refericiedCol, $metaClass, $fk);
                    } else {
                        $metaAttribute = new MetaAttributeHasMany($refericiedCol, $metaClass, $fk);
                    }

                    $metaClass->addField(new MetaAttributeBelongsTo($this->tables[$tableName]->getColumn($fieldName), $referenciedMetaClass, $fk));
                    $referenciedMetaClass->addField($metaAttribute);
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
        return $fields;
    }

    private function createDirIfNotExist($pathinfo)
    {
        if (is_dir($pathinfo)) {
            return true;
        } elseif ($this->createDirIfNotExist(dirname($pathinfo))) {
            return mkdir($pathinfo);
        } else {
            return false;
        }
    }

    private function getMetaPivot($tableName)
    {
        $fkConstrainNames = $this->classMap[$tableName]->getPivotedConstrainNames();
        $metaClass = $this->classMap[$tableName];
        $relFkA = $this->tables[$tableName]->getForeignKey($fkConstrainNames[0]);
        $relClassA = $this->classMap[$relFkA->getForeignTableName()];

        $relFkB = $this->tables[$tableName]->getForeignKey($fkConstrainNames[1]);
        $relClassB = $this->classMap[$relFkB->getForeignTableName()];
        return new MetaPivot($metaClass, $relClassA, $relFkA, $relClassB, $relFkB);
    }

}