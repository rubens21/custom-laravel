<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2017-09-09
 * Time: 10:50 PM
 */

namespace CST21\Lib;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Illuminate\Support\Str;

class MetaAttribute
{
    /**
     * @var Column
     */
    private $column;

//    /**
//     * @var MetaClass
//     */
//    private $foreignKeyMetaClass;
//
//    /**
//     * @var string
//     */
//    private $relType;

    const METHOD_SET_MODE = 'set';

    const METHOD_GET_MODE = 'get';

    /**
     * MetaAttribute constructor.
     * @param Column $column
     */
    public function __construct(Column $column)
    {
        $this->column = $column;
    }

    public function getFieldName()
    {
        return $this->column->getName();
    }

    public function getDoctrineColunm()
    {
        return $this->column;
    }

    /**
     * @param Column $column
     * @return MetaAttribute
     */
    public function setDoctrineColunm(Column $column):self
    {
        $this->column = $column;
        return $this;
    }


    public function getSetMethodData()
    {
        $args = $this->getPhpFieldType().' $'.$this->getPhpAttributeName();
        if(!$this->column->getNotnull()) {
            $args .= ' = null';
        }
        $signature = $this->transAttToMethod($this->getFieldName(), self::METHOD_SET_MODE).'('.$args.')';
        return ['type' => '$this', 'signature' => $signature, 'nullable' => $this->column->getNotnull()];
    }


    public function getGetMethodData()
    {
        $signature = $this->transAttToMethod($this->getFieldName(), self::METHOD_GET_MODE).'()';
        $returnType = $this->getPhpFieldType();
        if(!$this->column->getNotnull()) {
            $returnType .= '|null';
        }
        return ['type' => $returnType, 'signature' => $signature, 'nullable' => $this->column->getNotnull()];
    }

    /**
     * Translate the name of the attribute to a method name
     *
     * @param $fieldName
     * @return string
     */
    protected function transAttToMethod($fieldName, $mode)
    {
        return $this->getMethodModePrefix($mode) . $this->getRelationshipName($fieldName);
    }

    protected function getRelationshipName($fieldName)
    {
        return studly_case($fieldName);
    }

    protected function getMethodModePrefix($mode)
    {
        if($mode === self::METHOD_GET_MODE) {
            if($this->getPhpFieldType() === 'bool') {
                return 'is';
            }
            return 'get';
        } else {
            return 'set';
        }
    }


    protected function transCastMysqlToPhp($type)
    {
        $type = strtolower(str_replace(['(', ')'], '', $type));
        switch ($type) {
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
            case 'float':
            case 'double':
            case 'decimal':
            case 'year':
                return 'int';
            case 'bit':
            case 'tinyint':
            case 'boolean':
                return 'bool';
            case 'char':
            case 'varchar':
            case 'tinytext':
            case 'text':
            case 'mediumtext':
            case 'longtext':
            case 'binary':
            case 'varbinary':
            case 'tinyblob':
            case 'blob':
            case 'mediumblob':
            case 'longblob':
            case 'enum':
                return 'string';
            case 'date':
            case 'datetime':
            case 'time':
            case 'timestamp':
                return 'Carbon';
            default:
                return $type;
        }
    }

    protected function getPhpFieldType()
    {
        return $this->transCastMysqlToPhp($this->column->getType());
    }

    public function getPhpAttributeName()
    {
        return Str::camel($this->getFieldName());
    }

    public function getRelationshipDefinition():?array
    {
        return null;
    }

//    /**
//     * @return string
//     */
//    public function getRelType(): string
//    {
//        return $this->relType;
//    }
//
//    /**
//     * @param string $relType
//     */
//    public function setRelType(string $relType)
//    {
//        $this->relType = $relType;
//    }
//
//    /**
//     * @return MetaClass
//     */
//    public function getForeignKeyMetaClass(): ?MetaClass
//    {
//        return $this->foreignKeyMetaClass;
//    }
//
//    /**
//     * @param MetaClass $foreignKeyMetaClass
//     */
//    public function setForeignKeyMetaClass(MetaClass $foreignKeyMetaClass)
//    {
//        $this->foreignKeyMetaClass = $foreignKeyMetaClass;
//    }
//
//    public function getImportClasses()
//    {
//        return $this->getForeignKeyMetaClass()->getNamespace();
//    }

}