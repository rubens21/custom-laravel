<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2017-09-15
 * Time: 9:33 PM
 */

namespace CST21\Lib;

use Illuminate\Support\Str;

class MetaAttributeHasMany extends MetaAttributeHasOne
{
    public function getRelationshipDefinition(array $classMap):?array
    {
        $model = $classMap[$this->getRelatedClass()->getTableName()] ?? null;
        return [
//            $this->getRelationshipName($this->getRelatedFieldName()) => [
                $this->getRelatedClass()->getTableName() => [
                'rel' => 'hasMany',
                'model' => $model,
                'local_col' => $this->getFieldName(),
                'foreign_col' => $this->getRelatedForignKeyConstraint()->getLocalColumns()[0]
            ]
        ];
    }

    public function getGetMethodData()
    {
        $data = parent::getGetMethodData();
        $returnType = $this->getPhpFieldType().'[]';
        if(!$this->getDoctrineColunm()->getNotnull()) {
            $returnType .= '|null';
        }
        $data['type'] = $returnType;
        $data['target'] = $this->getRelationshipName($this->getRelatedForignKeyConstraint());
        return $data;
    }

    protected function getMethodModePrefix($mode)
    {
        if($mode === self::METHOD_GET_MODE) {
            return 'list';
        } else {
            return 'add';
        }
    }

    public function getRelatedModelName()
    {
        return $this->getRelatedClass()->getClassName();
    }

    protected function getRelationshipName($fieldName)
    {
        return Str::plural(parent::getRelationshipName(''));
    }

}