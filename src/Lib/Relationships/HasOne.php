<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2017-09-13
 * Time: 10:21 PM
 */

namespace CST21\Lib\Relationships;


use CST21\Lib\MetaAttribute;
use CST21\Lib\MetaClass;

class HasOne implements Relationship
{
    /**
     * @var MetaAttribute
     */
    private $localField;
    /**
     * @var MetaClass
     */
    private $modelTarget;

    public function __construct(MetaAttribute $localField, MetaClass $modelTarget)
    {
        $this->localField = $localField;
        $this->modelTarget = $modelTarget;
    }


    public function getPhpDocGetMethod(): array
    {
        return ['type' => $this->modelTarget->getClassName();
signature];
    }

    public function getPhpDocSetMethod(): array
    {
        // TODO: Implement getPhpDocSetMethod() method.
    }

    public function getSetAttributes(): string
    {
        // TODO: Implement getSetAttributes() method.
    }

    public function getGetAttributes(): string
    {
        // TODO: Implement getGetAttributes() method.
    }

    public function getRelAttributes(): string
    {
        // TODO: Implement getRelAttributes() method.
    }


}