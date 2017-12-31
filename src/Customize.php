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
use CST21\Shareables\BaseModel;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\MySqlConnection;

class Customize
{
    /**
     * @var MySqlConnection
     */
    private $connection;

    private $ignoreTableList = ['migrations', 'SequelizeMeta'];

    /**
     * @var MetaClass[]
     */
    private $metaClassMap = [];

    /**
     * @var array
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
        'namespaces' => ['App'],
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

    /**
     * @throws \Exception
     */
    public function map()
    {
        echo "\n\n======Nao esquece de dar dumpautoload ANTES ========\n\n";
		$this->mapClasses();
		$this->mapDb();
    }

    /**
     * @throws \Exception
     */
    private function mapClasses()
	{
		$this->loadExpectedClasses();
		foreach( get_declared_classes() as $class ){
			if( is_subclass_of( $class, BaseModel::class ) ) {
				/** @var BaseModel $class */
				if($class::isDefer()) {
				    if(isset($this->classMap[$class::getStaticTable()])) {
				        throw new \Exception('Only one model can represent a table (consider using an abstract class)');
                    }
                    $this->classMap[$class::getStaticTable()] = $class;
                } else {
				    echo "\nIgnoring rejected model class $class";
                }
			}
		}
	}

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function mapDb()
	{
		$this->connection
		  ->getDoctrineConnection()
		  ->getDatabasePlatform()
		  ->registerDoctrineTypeMapping('enum', 'string');

		$tables = array_diff($this->connection->getDoctrineSchemaManager()->listTableNames(), $this->ignoreTableList);
		$this->mapTables($tables);
		$this->mapFields($tables);
	}

    public function saveFiles($defaultNSDir, $mapPath)
    {
        foreach ($this->getClasses() as $tableName => $metaClass){
            $this->saveClassFile($tableName, $defaultNSDir);
        }
        $this->saveMapFile($mapPath);
    }

    public function saveClassFile($tableName, $defaultNSDir)
	{
		$classes = $this->getClasses();
		if(!isset($classes[$tableName])) {
			throw new \Exception('Table inexistent: '.$tableName);
		}
		$relativeFilePath = $defaultNSDir.DIRECTORY_SEPARATOR.$classes[$tableName]->getRelativeFilePath();
		$this->createDirIfNotExist(pathinfo($relativeFilePath, PATHINFO_DIRNAME));
		if(file_put_contents($relativeFilePath, $classes[$tableName]->generateCode()) !== false){
			return [
			  'class_name' => $classes[$tableName]->getFullClassName(),
			  'path' => $relativeFilePath
			];
		} else {
			return false;
		}
	}

	public function saveMapFile($mapPath)
	{
		$mapCode = [];
		foreach ($this->getClasses() as $tableName => $metaClass){
			$mapCode[] = "'".$metaClass->getTableName()."' => [\n".$this->arrayToSourceCode($metaClass->getRelMap($this->classMap), 2)."\n\t]";
		}
		$code = '<?php '."\nreturn [\n\t".implode(",\n\t", $mapCode)."\n];";
		if(file_put_contents($mapPath, $code) !== false){
			return $mapPath;
		} else {
			return false;
		}
	}

	private function arrayToSourceCode(array $arr, $level = 0)
	{
		$code = [];
		$ident = str_repeat("\t", $level);
		foreach ($arr as $key => $val )
		{
			$string = $ident."'$key' => ";
			if(is_array($val)) {
				$string .= "[\n".$this->arrayToSourceCode($val, $level+1)."\n$ident],";
			} else {
				$string .= var_export($val, true).',';
			}
			$code[] = $string;
		}
		return implode("\n", $code);
	}

    private function mapTables(array $tables)
    {
        $tableComments = $this->getTablesComment();
        foreach ($tables as $tableName) {
            if(!in_array($tableName, $this->ignoreTableList)) {
                $this->tables[$tableName] = $this->connection->getDoctrineSchemaManager()->listTableDetails($tableName);
                $metaClass = new MetaClass($tableName);
                $metaClass->setComment($tableComments[$tableName]);
                $this->metaClassMap[$tableName] = $metaClass;
            }
        }
    }

    private function mapFields(array $tables)
    {
        foreach ($tables as $tableName) {
            $metaClass = $this->metaClassMap[$tableName];
            $uniqueFields = $this->getUniqueFields($tableName);
            $specializedField = [];
            foreach ($this->tables[$tableName]->getForeignKeys() as $fk) {
                foreach ($fk->getLocalColumns() as $fieldName) {
                    $specializedField[] = $fieldName;
                    $referenciedMetaClass = $this->metaClassMap[$fk->getForeignTableName()];

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
        return $this->metaClassMap;
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
        $fkConstrainNames = $this->metaClassMap[$tableName]->getPivotedConstrainNames();
        $metaClass = $this->metaClassMap[$tableName];
        $relFkA = $this->tables[$tableName]->getForeignKey($fkConstrainNames[0]);
        $relClassA = $this->metaClassMap[$relFkA->getForeignTableName()];

        $relFkB = $this->tables[$tableName]->getForeignKey($fkConstrainNames[1]);
        $relClassB = $this->metaClassMap[$relFkB->getForeignTableName()];
        return new MetaPivot($metaClass, $relClassA, $relFkA, $relClassB, $relFkB);
    }

    private function loadExpectedClasses()
    {
        /** @var \Composer\Autoload\ClassLoader $autoLoader */
        $autoLoader = require base_path('/vendor/autoload.php');

        foreach ($this->config['namespaces'] as $nsAcceptable) {
            foreach ($autoLoader->getClassMap() as $namespace => $file) {
                if(preg_match("/^".addslashes($nsAcceptable)."/", $namespace)) {
                    require_once $file;
                }
            }
        }
    }


}