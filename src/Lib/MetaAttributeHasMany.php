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
    public function getRelationshipDefinition():?array
    {
        return [
//            $this->getRelationshipName($this->getRelatedFieldName()) => [
            $this->fk->getName() => [
                'rel' => 'hasMany',
                'model' => $this->getRelatedModelName(),
                'local_col' => $this->getFieldName(),
                'foreign_col' => $this->getRelatedFieldName()
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
        return Str::plural(parent::getRelationshipName($this->fk->getName()));
    }

}