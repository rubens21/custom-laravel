<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2017-10-09
 * Time: 3:17 PM
 */

namespace CST21\Lib;


use Doctrine\DBAL\Schema\ForeignKeyConstraint as Fk;

class MetaPivot
{
    /**
     * @var MetaClass
     */
    private $pivotClass;

    /**
     * @var MetaClass[]
     */
    private $relatedClasses;

    /**
     * @var Fk[]
     */
    private $relatedForignKeyConstraints;

    const INDEX_A = 'A';
    const INDEX_B = 'B';

    public function __construct(MetaClass $metaClass, MetaClass $relClassA, Fk $relFkA, MetaClass $relClassB, Fk $relFkB)
    {
        $this->pivotClass = $metaClass;
        $this->relatedClasses[self::INDEX_A] = $relClassA;
        $this->relatedClasses[self::INDEX_B] = $relClassB;
        $this->relatedForignKeyConstraints[self::INDEX_A] = $relFkA;
        $this->relatedForignKeyConstraints[self::INDEX_B] = $relFkB;
    }

    /**
     * @return MetaClass
     */
    public function getPivotClass(): MetaClass
    {
        return $this->pivotClass;
    }

    /**
     * @return MetaClass
     */
    public function getRelatedClass($index): MetaClass
    {
        return $this->relatedClasses[$index];
    }

    /**
     * @return Fk
     */
    public function getRelatedForignKeyConstraint($index): Fk
    {
        return $this->relatedForignKeyConstraints[$index];
    }

    public function getIndex(MetaClass $metaClass)
    {
        if($metaClass->getTableName() === $this->getRelatedClass(self::INDEX_A)->getTableName()) {
            return self::INDEX_A;
        } else if($metaClass->getTableName() === $this->getRelatedClass(self::INDEX_B)->getTableName()) {
            return self::INDEX_B;
        } else {
            throw new \Exception('It is not a meta class of this pivot relationship');
        }
    }


}