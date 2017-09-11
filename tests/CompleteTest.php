<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2017-09-09
 * Time: 2:51 PM
 */

namespace Tests;



use CST21\Customize;

class CompleteTest extends TestCase
{
    public function testDef()
    {
        $Cust = new Customize($this->getConnection());
        $Cust->map();
        foreach ($Cust->getClasses() as $metaClass){
            echo $metaClass->generateCode();
            echo "\n\n";
        }
    }

}
