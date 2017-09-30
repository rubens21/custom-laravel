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
        require (__DIR__.'/../src/Shareables/BaseModel.php');
        require (__DIR__.'/sample/Posts/Tag.php');
        require (__DIR__.'/sample/Author.php');
    }
    public function testBelongsTo()
    {
//        $professor = new Author();
//        $professor->setName('Revisor '.rand(0, 9999));
//        $professor->setType('beginner');
//        $this->assertTrue($professor->save());
//
//        $aluno = new Author();
//        $aluno->setName('Revisado '.rand(0, 9999));
//        $aluno->setType('beginner');
//        $aluno->setRevisor($professor);
//        $this->assertTrue($aluno->save());
//        $alunoId = $aluno->getId();

        $alunoRefreshed = Author::find(19);
        $revisor = $alunoRefreshed->getRevisor();

        $this->assertInstanceof(Author::class,$revisor);
    }

}
