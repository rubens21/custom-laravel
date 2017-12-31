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

        //@todo se essa substituição fosse baseada em regex daria para permitir personalização
        return parent::getRelationshipName(str_replace('fk_', '', $fieldName));
    }


    protected function getPhpFieldType()
    {
        return $this->getRelatedModelName();
    }

    public function getPhpAttributeName()
    {
        return $this->getRelatedModelName();
    }

    public function getRelationshipDefinition(array $classMap):?array
    {
        $model = $classMap[$this->getRelatedClass()->getTableName()] ?? null;
        return [
              $this->getRelatedClass()->getTableName() => [
                'rel' => 'belongsTo',
                'model' => $model ,
                'local_col' => $this->getFieldName(),
                'foreign_col' => $this->getForeignKeyConstraint()->getForeignColumns()[0]
            ]
        ];
    }
    public function getSetMethodData(array $classMap)
    {
        $data = parent::getSetMethodData();
		$model = $classMap[$this->getRelatedClass()->getTableName()] ?? null;
        $data['target'] = $model;
        return $data;
    }

    public function getGetMethodData(array $classMap)
    {
        $data = parent::getGetMethodData();
		$model = $classMap[$this->getRelatedClass()->getTableName()] ?? null;
		$data['target'] = $model;
        return $data;
    }

    public function getRelatedModelName()
    {
//        return MetaClass::convertTableNameToClassName($this->getForeignKeyConstraint()->getForeignTableName());
        return $this->relatedClass->getClassName();
    }

    public function getImportClasses(): array
    {
        return [$this->getRelatedClass()->getFullClassName()];
    }

}