<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2017-09-15
 * Time: 9:33 PM
 */

namespace CST21\Lib;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Str;

class MetaAttributeBelongsToMany extends MetaAttributeHasOne
{
    /**
     * @var MetaPivot
     */
    private $pivot;

    private $myPivotIndex;


    public function __construct(
        Column $column,
        MetaPivot $pivot,
        string $pivotIndex
    ) {

        $this->myPivotIndex = $pivotIndex;
        $foreignKeyConstrain = $pivot->getRelatedForignKeyConstraint($pivotIndex);

        if($pivotIndex === MetaPivot::INDEX_A) {
            $relatedClass = $pivot->getRelatedClass(MetaPivot::INDEX_B);
        } elseif($pivotIndex === MetaPivot::INDEX_B) {
            $relatedClass = $pivot->getRelatedClass(MetaPivot::INDEX_A);
        } else {
            throw new \Exception('Invalid pivot index '.$pivotIndex);
        }
        parent::__construct($column, $relatedClass, $foreignKeyConstrain);

        $this->pivot = $pivot;
    }

    public function getRelationshipDefinition(array $classMap):?array
    {
        $relatedClasseFk = $this->pivot->getRelatedForignKeyConstraint($this->myPivotIndex === MetaPivot::INDEX_A ? MetaPivot::INDEX_B : MetaPivot::INDEX_A);
        $model = $classMap[$this->getRelatedClass()->getTableName()] ?? null;
        return [
            $this->getRelatedClass()->getTableName() => [
//            $this->getRelationshipName($this->getRelatedForignKeyConstraint()->getName()) => [
                'rel' => 'belongsToMany',
                'pivot' => $this->pivot->getPivotClass()->getTableName(),
                'model' => $model,
                'local_col' => $this->getRelatedForignKeyConstraint()->getLocalColumns()[0],
                'foreign_col' => $relatedClasseFk->getLocalColumns()[0]
            ]
        ];
    }

    public function getSetMethodData()
    {
        $data = MetaAttribute::getSetMethodData();
        $data['target'] =  $this->getRelationshipName($this->getRelatedForignKeyConstraint()->getName());
        $data['args'] = $this->getPhpFieldType().' $'.Str::singular($this->getPhpAttributeName());
        return $data;
    }

    protected function transAttToMethod($fieldName, $mode)
    {
        if($mode === self::METHOD_SET_MODE) {
            return $this->getMethodModePrefix($mode) . Str::singular($this->getRelationshipName($fieldName));
        } else {
            return $this->getMethodModePrefix($mode) . Str::plural($this->getRelationshipName($fieldName));
        }
    }

    public function getGetMethodData()
    {
        $data = parent::getGetMethodData();
        $returnType = $this->getPhpFieldType().'[]';
        if(!$this->getDoctrineColunm()->getNotnull()) {
            $returnType .= '|null';
        }
        $data['type'] = $returnType;
        $data['target'] = $this->getRelationshipName($this->getRelatedForignKeyConstraint()->getName());
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
        return Str::plural(parent::getRelationshipName($this->getRelatedForignKeyConstraint()->getName()));
    }

}