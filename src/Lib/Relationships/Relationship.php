<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2017-09-13
 * Time: 10:16 PM
 */

namespace CST21\Lib\Relationships;


use CST21\Lib\Code;
use CST21\Lib\MetaAttribute;

interface Relationship
{
    public function getPhpDocGetMethod():array ;
    public function getPhpDocSetMethod():array ;
    public function getSetAttributes():string;
    public function getGetAttributes():string;
    public function getRelAttributes():string;
    public function getImportList():array;

    public function getMetaAttribute():MetaAttribute;
    public function setMetaAttribute(MetaAttribute $metaAttribute):self;


}