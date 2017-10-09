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
     * @var MetaAttribute[][]
     */
    private $fields = [];

//    /**
//     * @var MetaAttribute[]
//     */
//    private $fieldReference = [];

    /**
     * @var array
     */
    private $uses = [];

    const IGNORE_ATTRIBUTES = ['id', 'createdAt', 'updatedAt'];

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
        return $this;
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
        return $this->getClassName().'.php';
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
        $template = str_replace('{{imports}}', $this->getImports(), $template);
        return $template;
    }

    public function getRelativeFilePath($removeDefaultNamespace = true)
    {
        $namesapeceLevels = $removeDefaultNamespace ? array_slice($this->getFullClassNameLevels(), 1) : $this->getFullClassNameLevels();
        return implode(DIRECTORY_SEPARATOR,$namesapeceLevels).'.php';
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

//    /**
//     * @param $colunmName
//     * @return MetaAttribute[]
//     */
//    public function getField($colunmName): array
//    {
//        return $this->fields[$colunmName];
//    }

    /**
     * @param MetaAttribute $metaAttribute
     * @return $this
     */
    public function addField(MetaAttribute $metaAttribute)
    {
        $this->fields[] = $metaAttribute;
        return $this;
    }

    private function getMetaData()
    {
        parse_str($this->getComment(), $output);
        return $output ?? null;
    }

    public function isAPivot():?bool
    {
        $meta = $this->getMetaData();
        var_dump($meta);
        return ($meta && isset($meta['pivot']));
    }

    public function getPivotedTables():array
    {
        return preg_split('/\|/',$this->getMetaData()['pivot']);
    }

    /**
     * @return string Class name including namespace
     */
    public function getFullClassName()
    {
        return implode('\\', $this->getFullClassNameLevels());
    }

    /**
     * @return array Class name including namespace and in array format
     */
    public function getFullClassNameLevels()
    {
        $meta = $this->getMetaData();
        $ns = self::DEFAULT_NS;
        if($meta && isset($meta['ns'])) {
            $ns = array_merge($ns, explode('\\', $meta['ns']));
        }
        $ns[] = $this->getClassName();
        return $ns;
    }

    public function getNamespace()
    {
        return implode('\\', array_slice($this->getFullClassNameLevels(), 0, -1));
    }

    public function getClassName()
    {
        return self::convertTableNameToClassName($this->table);
    }
    public static function convertTableNameToClassName(string $tableName)
    {
        return Str::ucfirst(Str::singular($tableName));
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
            if(!in_array($metaAttribute->getPhpAttributeName(), self::IGNORE_ATTRIBUTES)) {
                $get = $metaAttribute->getGetMethodData();
                $set = $metaAttribute->getSetMethodData();
                if($set) {
                    $longest = strlen($set['type']) > $longest ? strlen($set['type']) : $longest;
                    $properties[] = $set;
                }

                if($get) {
                    $longest = strlen($get['type']) > $longest ? strlen($get['type']) : $longest;
                    $properties[] = $get;
                }
            }
        }
        $phpDoc = [];
        foreach ($properties as $property){
            $phpDoc[] = " * @method ".str_pad($property['type'],$longest, ' ').' '.$property['name'].'('.$property['args'].')';
        }

        return implode("\n", $phpDoc);
    }

//    /**
//     * @param string $tableName
//     * @param string $fieldName
//     * @return MetaClass|null
//     */
//    public function getFieldReference(string $tableName, string $fieldName): ?MetaClass
//    {
//        if(!isset($this->fieldReference[$tableName]) || !isset($this->fieldReference[$tableName][$fieldName])) {
//            return null;
//        }
//        return $this->fieldReference[$tableName][$fieldName];
//    }

//    /**
//     * @param string $fieldName
//     * @param MetaClass $metaClass
//     * @return $this
//     */
//    public function addFieldReference(string $fieldName, MetaClass $metaClass, bool $oneToOne)
//    {
//        $this->uses[$metaClass->getNamespace()] = true;
//        $this->fieldReference[$metaClass->getName()] = [
//            'local_col' => $fieldName,
//            'metaClass' => $metaClass,
//            'relationshipt' => $oneToOne ? 'hasOne' : 'hasMany'
//            ];
//        return $this;
//    }
//    public function addPivotedFieldReference(MetaClass $metaClass, MetaClass $pivotMetaClass,$myFieldName,$otherFieldName ,$myRefFieldName, $otherRefFieldName)
//    {
//        $this->uses[$metaClass->getNamespace()] = true;
//        $this->fieldReference[$metaClass->getName()] = [
//            'metaClass' => $metaClass,
//            'pivot_table' => $pivotMetaClass->getTableName(),
//            'local_col' => $myFieldName,
//            'other_col' => $otherFieldName,
//            'foreign_col' => $myRefFieldName,
//            'other_pivot_col' => $otherRefFieldName,
//            'relationshipt' =>  'belongsToMany'
//            ];
//
//        //belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null
//        //$this->belongsToMany('App\Role', 'role_user', 'user_id', 'role_id')->using('App\UserRole');;
//        return $this;
//    }

    private function getRelationShips()
    {
        //@todo da pra melhorar isso mescaldno no ooutro metodo
        $relations = [];
        foreach ($this->getFields() as $field) {
            $relDef = $field->getRelationshipDefinition();
            if($relDef) {
                $relations = array_merge($relations, $relDef);
            }
        }
        return $relations;
    }
    private function getMethods()
    {
        $setters = [];
        $getters = [];

        foreach ($this->getFields() as $fieldName => $metaAttribute) {
            if(!in_array($metaAttribute->getPhpAttributeName(), self::IGNORE_ATTRIBUTES)) {
                $get = $metaAttribute->getGetMethodData();
                $set = $metaAttribute->getSetMethodData();

                if($set) {
                    $setters[$set['name']] = $set['target'];
                }

                if($get) {
                    $getters[$get['name']] = $get['target'];
                }
            }
        }

        return [
            'setters' => $setters,
            'getters' => $getters,
        ];
    }


    private function getBodyAttributes()
    {
        $atts = $this->getMethods();
        $attributes[] = 'protected static $__relationships = '.var_export($this->getRelationShips(), true).';';
        $attributes[] = 'protected static $__attSet = '.var_export($atts['setters'], true).';';
        $attributes[] = 'protected static $__attGet = '.var_export($atts['getters'], true).';';
        return implode("\n", $attributes);


    }

    private function getImports()
    {
        $imports = [];
        foreach ($this->getFields() as $field) {
            $imports = array_merge($imports, $field->getImportClasses());
        }
        $classes = [];
        foreach (array_unique($imports) as $class) {
            if($class !== $this->getFullClassName()) {
                $classes[] = 'use '.$class.';';
            }
        }

//        foreach ($this->fieldReference as $metaClassName => $relationship) {
//            /** @var MetaClass $metaClass */
//            $metaClass = $relationship['metaClass'];
//            $relations[$metaClass->getTableName()] = [
//                'rel' => $relationship['relationshipt'],
//                'model' => $metaClass->getClassName(),
//                'local_col' => 'id',
//                'foreign_col' => $relationship['local_col']
//            ];
//        }
        return implode("\n", $classes);
    }




}