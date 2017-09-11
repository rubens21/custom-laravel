<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2017-09-09
 * Time: 3:17 PM
 */

namespace CST21\Lib;
use Illuminate\Support\Str;

class MetaClass
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var array
     */
    private $tableMetaData;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var MetaAttribute[]
     */
    private $fields = [];

    /**
     * @var MetaAttribute[]
     */
    private $fieldReference = [];

    /**
     * @var array
     */
    private $uses = [];

    const IGNORE_FIELDS = ['id', 'created_at', 'updated_at'];

    const DEFAULT_NS = ['App'];

    const DEFAULT_PARENT = ['CST21', 'Shareables', 'BaseModel'];

    /**
     * MetaClass constructor.
     * @param string $tableName
     */
    public function __construct(string $tableName)
    {
        $this->table = $tableName;
    }

    /**
     * @return string
     */
    public function getName():string
    {
        return Str::studly($this->table);
    }
    /**
     * @return string
     */
    public function getTableName():string
    {
        return $this->table;
    }

    /**
     * @param string $table
     * @return MetaClass
     */
    public function setTable(string $table):MetaClass
    {
        $this->table = $table;
        return $this;
    }

    public function getFileName()
    {
        return $this->getName().'.php';
    }

    public function generateCode()
    {
        $template = file_get_contents(__DIR__.'/../Templates/model');
       // $template = str_replace('{{date}}', Carbon::now()->toRssString(), $template);
        $template = str_replace('{{namespace}}', $this->getNamespace(), $template);
        $template = str_replace('{{parent_full}}', $this->getParentClass(), $template);
        $template = str_replace('{{parent}}', basename(str_replace('\\', '/', $this->getParentClass())), $template);
        $template = str_replace('{{class}}', $this->getClassName(), $template);
        $template = str_replace('{{body}}', $this->getBodyAttributes(), $template);
        $template = str_replace('{{properties}}', $this->getProperties(), $template);
//        $template = str_replace('{{imports}}', $this->getImports($model), $template);
        return $template;
    }

    /**
     * @return array
     */
    public function getTableMetaData(): array
    {
        return $this->tableMetaData;
    }

    /**
     * @param array $tableMetaData
     */
    public function setTableMetaData(array $tableMetaData)
    {
        $this->tableMetaData = $tableMetaData;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return MetaAttribute[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param $colunmName
     * @return MetaAttribute
     */
    public function getField($colunmName): MetaAttribute
    {
        return $this->fields[$colunmName];
    }

    /**
     * @param MetaAttribute $metaAttribute
     * @return $this
     */
    public function addField(MetaAttribute $metaAttribute)
    {
        $this->fields[$metaAttribute->getFieldName()] = $metaAttribute;
        return $this;
    }

    private function getMetaData()
    {
        return json_decode($this->getComment(), true) ?? null;
    }

    public function getNamespace()
    {
        $meta = $this->getMetaData();
        $ns = self::DEFAULT_NS;
        if($meta && isset($meta['ns'])) {
            $ns[] = $meta['ns'];
        }
        return implode('\\', $ns);
    }

    private function getClassName()
    {
        return Str::singular($this->getName());
    }

    private function getParentClass()
    {
        return implode('\\', self::DEFAULT_PARENT);
    }

    private function getProperties()
    {
        $properties = [];
        $longest = 0;
        foreach ($this->getFields() as $fieldName => $metaAttribute) {
            if(!in_array($fieldName, self::IGNORE_FIELDS)) {
                $get = $metaAttribute->getGetMethodData();
                $set = $metaAttribute->getSetMethodData();
                $longest = strlen($get['type']) > $longest ? strlen($get['type']) : $longest;
                $longest = strlen($set['type']) > $longest ? strlen($set['type']) : $longest;
                $properties[] = $set;
                $properties[] = $get;
            }
        }
        $phpDoc = [];
        foreach ($properties as $property){
            $phpDoc[] = " * @method ".str_pad($property['type'],$longest, ' ').' '.$property['signature'];
        }

        return implode("\n", $phpDoc);
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @return MetaClass|null
     */
    public function getFieldReference(string $tableName, string $fieldName): ?MetaClass
    {
        if(!isset($this->fieldReference[$tableName]) || !isset($this->fieldReference[$tableName][$fieldName])) {
            return null;
        }
        return $this->fieldReference[$tableName][$fieldName];
    }

    /**
     * @param string $fieldName
     * @param MetaClass $metaClass
     * @return $this
     */
    public function addFieldReference(string $fieldName, MetaClass $metaClass, bool $oneToOne)
    {
        $this->uses[$metaClass->getNamespace()] = true;
        $this->fieldReference[$metaClass->getName()] = [
            'local_col' => $fieldName,
            'metaClass' => $metaClass,
            'oneToOne' => $oneToOne
            ];
        return $this;
    }

    private function getRelationShips()
    {
        $relations = [];
        foreach ($this->getFields() as $field) {
            if($field->isForeingKey()) {
                $fk = $field->getForeignKeyConstraint();
                $relations[$field->getFieldName()] = [
                    'rel' => 'belongsTo',
                    'model' => $field->getForeignKeyMetaClass()->getClassName(),
                    'local_col' => $field->getFieldName(),
                    'foreign_col' => $fk->getForeignColumns()[0]
                ];
            }
        }

        foreach ($this->fieldReference as $metaClassName => $relationship) {
            /** @var MetaClass $metaClass */
            $metaClass = $relationship['metaClass'];
            $relations[$metaClass->getTableName()] = [
                'rel' => $relationship['oneToOne'] ? 'hasOne' : 'hasMany',
                'model' => $metaClass->getClassName(),
                'local_col' => 'id',
                'foreign_col' => $relationship['local_col']
            ];
        }
        return $relations;
    }

    private function getBodyAttributes()
    {
        $relationshipd = $this->getRelationShips();

        return 'const RELATIONSHIPS = '.var_export($relationshipd, true);


    }


}