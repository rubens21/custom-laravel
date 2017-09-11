<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2017-09-09
 * Time: 2:35 PM
 */

namespace CST21;

use CST21\Lib\MetaAttribute;
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

    private $constrains = [];

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
        $tables = $this->connection->getDoctrineSchemaManager()->listTableNames();

        $this->connection
            ->getDoctrineConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');

        $tableComments = $this->getTablesComment();

        foreach ($tables as $tableName) {
            if(!in_array($tableName, $this->ignoreTableList)) {
                $this->tables[$tableName] = $this->connection->getDoctrineSchemaManager()->listTableDetails($tableName);
                $metaClass = new MetaClass($tableName);
                $metaClass->setComment($tableComments[$tableName]);

                foreach ($this->tables[$tableName]->getColumns() as $col) {
                    $metaClass->addField(new MetaAttribute($col));
                }

                foreach ($this->tables[$tableName]->getForeignKeys() as $fk) {
                    foreach ($fk->getLocalColumns() as $local) {
                        $metaClass->getField($local)->setForeignKeyConstraint($fk);
                        $this->constrains[$tableName][$local] = $fk;
                    }
                }
                $this->classMap[$tableName] = $metaClass;
            }
        }
        $this->identifyRelationships();
    }

    private function identifyRelationships()
    {
        foreach ($this->constrains as $tableName => $rel) {
            $uniqueFields = $this->getUniqueFields($tableName);
            echo $tableName;
            foreach ($rel as $localCol => $fk) {/** @var \Doctrine\DBAL\Schema\ForeignKeyConstraint $fk */

                $referencedMetaClass = $this->classMap[$fk->getForeignTableName()];
                //let's tell to the class what is the other class it refereces to
                $this->classMap[$tableName]->getField($localCol)->setForeignKeyMetaClass($referencedMetaClass);

                $foreignColName = $fk->getForeignColumns()[0];//more fields in the future MAYBE!
                $referencedMetaClass->addFieldReference($localCol, $this->classMap[$tableName], in_array($localCol, $uniqueFields));

                    //next steps: pegar pivot
//                $this->belongsToMany('App\Role', 'role_user', 'user_id', 'role_id');
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
        if(count($fields) > 1) {
            dd($fields);
        }
        return $fields;
    }


}