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

class MetaAttributeHasOne extends MetaAttribute
{
    /**
     * @var MetaClass
     */
    private $relatedClass;

    /**
     * @var ForeignKeyConstraint
     */
    private $relatedForignKeyConstraint;

    public function __construct(Column $column, MetaClass $relatedClass, ForeignKeyConstraint $relatedForignKeyConstraint)
    {
        parent::__construct($column);
        $this->setRelatedClass($relatedClass);
        $this->relatedForignKeyConstraint = $relatedForignKeyConstraint;
    }


    /**
     * @return ForeignKeyConstraint
     */
    public function getRelatedForignKeyConstraint(): ForeignKeyConstraint
    {
        return $this->relatedForignKeyConstraint;
    }


    /**
     * @return MetaClass
     */
    public function getRelatedClass(): MetaClass
    {
        return $this->relatedClass;
    }

    /**
     * @param MetaClass $relatedClass
     */
    public function setRelatedClass(MetaClass $relatedClass)
    {
        $this->relatedClass = $relatedClass;
    }
    protected function getRelationshipName($fieldName)
    {
        return parent::getRelationshipName($this->getRelatedForignKeyConstraint()->getName());
    }

    public function getSetMethodData()
    {
        return [];
    }

    protected function getPhpFieldType()
    {
        return $this->getRelatedModelName();
    }

    public function getPhpAttributeName()
    {
        return $this->getRelatedForignKeyConstraint()->getName();
    }

    public function getRelationshipDefinition():?array
    {
        return [
//            $this->getRelationshipName($this->getRelatedFieldName()) => [
            $this->getRelatedForignKeyConstraint()->getName() => [
                'rel' => 'hasOne',
                'model' => $this->getRelatedModelName(),
                'local_col' => $this->getFieldName(),
                'foreign_col' => $this->getRelatedForignKeyConstraint()->getLocalColumns()[0]
            ]
        ];
    }

    public function getRelatedModelName()
    {
        return $this->getRelatedClass()->getClassName();
    }

    public function getImportClasses(): array
    {
        return [$this->getRelatedClass()->getFullClassName()];
    }

}