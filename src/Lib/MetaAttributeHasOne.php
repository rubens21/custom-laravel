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
     * @var string
     */
    private $relatedFieldName;


    public function __construct(Column $column, MetaClass $relatedClass, ForeignKeyConstraint $fk, string $relatedFieldName)
    {
        parent::__construct($column);
        $this->setRelatedClass($relatedClass);
        $this->setRelatedFieldName($relatedFieldName);
        $this->fk = $fk;
    }

    /**
     * @return string
     */
    public function getRelatedFieldName(): string
    {
        return $this->relatedFieldName;
    }

    /**
     * @param string $relatedFieldName
     */
    public function setRelatedFieldName(string $relatedFieldName)
    {
        $this->relatedFieldName = $relatedFieldName;
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
        return parent::getRelationshipName($this->fk->getName());
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
        return $this->fk->getName();
    }

    public function getRelationshipDefinition():?array
    {
        return [
//            $this->getRelationshipName($this->getRelatedFieldName()) => [
            $this->fk->getName() => [
                'rel' => 'hasOne',
                'model' => $this->getRelatedModelName(),
                'local_col' => $this->getFieldName(),
                'foreign_col' => $this->getRelatedFieldName()
            ]
        ];
    }

    public function getRelatedModelName()
    {
        return $this->getRelatedClass()->getClassName();
    }

}