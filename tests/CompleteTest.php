<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2017-09-09
 * Time: 2:51 PM
 */

namespace Tests;



use App\Author;
use App\Posts\Tag;
use CST21\Customize;

class CompleteTest extends TestCase
{
    public function testDef()
    {
        $Cust = new Customize($this->getConnection());
        $Cust->map();
        $Cust->saveFiles(__DIR__.'/sample');
//        foreach ($Cust->getClasses() as $metaClass){
//            echo $metaClass->();
//            echo "\n\n";
//        }
    }
    public function testSimpleAttributes()
    {
        require (__DIR__.'/../src/Shareables/BaseModel.php');
        require (__DIR__.'/sample/Posts/Tag.php');
        require (__DIR__.'/sample/Author.php');

//        $label = "my-nice-tag-".rand(0, 99);
//        $tag = new Tag();
//        $tag->setLabel($label);
//        $this->assertTrue($tag->save());
//
//        $tag2 = Tag::where(['label' => $label])->first();
//        $this->assertEquals($label, $tag2->getLabel());

        $professor = new Author();
        $professor->setName('Revisor '.rand(0, 9999));
        $professor->setType('beginner');
        $this->assertTrue($professor->save());

        //parou quando ia fazer o metodo set para o belongs to
        //$this->user()->associate($user);

        $aluno = new Author();
        $aluno->setName('Revisado '.rand(0, 9999));
        $aluno->setType('beginner');
        $aluno->setRevisor($professor);
        $this->assertTrue($aluno->save());
    }

}
