<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2017-09-09
 * Time: 2:51 PM
 */

namespace Tests;



use App\Author;
use App\Posts\Post;
use App\Posts\Tag;
use Carbon\Carbon;
use CST21\Customize;

class CompleteTest extends TestCase
{

    private static $wasSettedUp = false;

    public function setup()
    {
        parent::setup();
        if(!self::$wasSettedUp) {
            self::$wasSettedUp = true;
            $Cust = new Customize($this->getConnection());
            $Cust->map();
            $Cust->saveFiles(__DIR__.'/sample');
            include_once  (__DIR__.'/../src/Shareables/BaseModel.php');
            include_once (__DIR__.'/sample/Posts/Tag.php');
            include_once (__DIR__.'/sample/Posts/Post.php');
            include_once (__DIR__.'/sample/Author.php');
        }

    }
    public function testBelongsTo()
    {
        $professor = new Author();
        $professor->setName('Revisor '.rand(0, 9999));
        $professor->setType('beginner');
        $this->assertTrue($professor->save());
        $professorId = $professor->getId();

        $aluno = new Author();
        $aluno->setName('Revisado '.rand(0, 9999));
        $aluno->setType('beginner');
        $aluno->setRevisor($professor);
        $this->assertTrue($aluno->save());
        $alunoId = $aluno->getId();

        $alunoRefreshed = Author::find($alunoId);
        $revisor = $alunoRefreshed->getRevisor();

        $this->assertInstanceof(Author::class,$revisor);
        $this->assertEquals($professorId,$revisor->getId());
    }


    public function testHasOne()
    {
        $professor = new Author();
        $professor->setName('Revisor '.rand(0, 9999));
        $professor->setType('beginner');
        $this->assertTrue($professor->save());
        $professorId = $professor->getId();

        $aluno = new Author();
        $aluno->setName('Revisado'.rand(0, 9999));
        $aluno->setType('beginner');
        $aluno->setRevisor($professor);
        $this->assertTrue($aluno->save());
        $alunoId = $aluno->getId();

        $professorRefreshed = Author::find($professorId);
        $dependente = $professorRefreshed->getDependent();

        $this->assertInstanceof(Author::class,$professorRefreshed);
        $this->assertEquals($alunoId,$dependente->getId());
        echo $dependente->getName();
    }

    public function testHasMany()
    {
        $autor = new Author();
        $autor->setName('Revisor '.rand(0, 9999));
        $autor->setType('beginner');
        $this->assertTrue($autor->save());
        $autorId = $autor->getId();

        $post1 = new Post();
        $post1->setAuthor($autor);
        $post1->setTitle('Test 1 - '.rand(0, 9999));
        $post1->setContent('CONTENT  '.rand(0, 9999));
        $post1->setPublicatedAt(Carbon::now()->addDays(2));
        $post1->save();
        $post1Id = $post1->getId();

        $post2 = new Post();
        $post2->setAuthor($autor);
        $post2->setTitle('Test 2 - '.rand(0, 9999));
        $post2->setContent('CONTENT  '.rand(0, 9999));
        $post2->setPublicatedAt(Carbon::now()->addDays(2));
        $post2->save();
        $post2Id = $post2->getId();

        $posts = $autor->listPosts();
        $this->assertCount(2, $posts);

        $post1Refreshed = Post::find($post1Id);
        $this->assertInstanceof(Post::class,$post1Refreshed);
        $this->assertEquals($autorId,$post1Refreshed->getAuthor()->getId());

        $post2Refreshed = Post::find($post2Id);
        $this->assertInstanceof(Post::class,$post2Refreshed);
        $this->assertEquals($autorId,$post2Refreshed->getAuthor()->getId());

    }

}
