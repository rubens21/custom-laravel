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

    public function __construct(Column $column, ForeignKeyConstraint $fk)
    {
        parent::__construct($column);
        $this->setForeignKeyConstraint($fk);
    }

    /**
     * @var ForeignKeyConstraint
     */
    private $foreignKeyConstraint;

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
    public function setForeignKeyConstraint(ForeignKeyConstraint $foreignKeyConstraint)
    {
        $this->foreignKeyConstraint = $foreignKeyConstraint;
    }

    /**
     * Translate the name of the attribute to a method name
     *
     * @param $name
     * @return string
     */
    protected function transAttToMethod($name, $mode)
    {
        return parent::transAttToMethod(str_replace('fk_', '', $name), $mode);
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

    protected function getPhpAttributeName()
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

}