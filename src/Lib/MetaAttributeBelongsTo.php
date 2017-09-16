<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2017-09-15
 * Time: 9:33 PM
 */

namespace CST21\Lib;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;

class MetaAttributeBelongsTo extends MetaAttribute
{
    /**
     * @var MetaClass
     */
    private $relatedClass;

    public function __construct(Column $column, MetaClass $relatedClass, ForeignKeyConstraint $fk)
    {
        parent::__construct($column);
        $this->setForeignKeyConstraint($fk);
        $this->relatedClass = $relatedClass;
    }

    /**
     * @var ForeignKeyConstraint
     */
    private $foreignKeyConstraint;

    /**
     * @return MetaClass
     */
    public function getRelatedClass(): MetaClass
    {
        return $this->relatedClass;
    }

    /**
     * @return ForeignKeyConstraint
     */
    public function getForeignKeyConstraint(): ForeignKeyConstraint
    {
        return $this->foreignKeyConstraint;
    }

    /**
     * @param ForeignKeyConstraint $foreignKeyConstraint
     */
    public function setForeignKeyConstraint(ForeignKeyConstraint $foreignKeyConstraint):self
    {
        $this->foreignKeyConstraint = $foreignKeyConstraint;
        return $this;
    }

    protected function getRelationshipName($fieldName)
    {
        return parent::getRelationshipName(str_replace('fk_', '', $fieldName));
    }

//    public function getSetMethodData()
//    {
//        $args = $this->getRelatedModelName(). ' $'.$this->getRelatedModelName();// $this->getPhpFieldType().' $'.$this->getPhpAttributeName();
//        if(!$this->getDoctrineColunm()->getNotnull()) {
//            $args .= ' = null';
//        }
//        $signature = $this->transAttToMethod($this->getFieldName(), self::METHOD_SET_MODE).'('.$args.')';
//        return ['type' => '$this', 'signature' => $signature, 'nullable' => $this->getDoctrineColunm()->getNotnull()];
//    }

    protected function getPhpFieldType()
    {
        return $this->getRelatedModelName();
    }

    public function getPhpAttributeName()
    {
        return $this->getRelatedModelName();
    }

    public function getRelationshipDefinition():?array
    {
        return [
            $this->getForeignKeyConstraint()->getForeignTableName() => [
                'rel' => 'belongsTo',
                'model' => $this->getRelatedModelName(),
                'local_col' => $this->getFieldName(),
                'foreign_col' => $this->getForeignKeyConstraint()->getForeignColumns()[0]
            ]
        ];
    }

    public function getRelatedModelName()
    {
        return MetaClass::convertTableNameToClassName($this->getForeignKeyConstraint()->getForeignTableName());
    }

    public function getImportClasses(): array
    {
        return [$this->getRelatedClass()->getFullClassName()];
    }

}