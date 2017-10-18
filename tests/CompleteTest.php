<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2017-09-09
 * Time: 2:51 PM
 */

namespace Tests;



use App\Author;
use App\Comment;
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
            include_once (__DIR__.'/sample/Comment.php');
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
        $this->assertTrue($aluno->save(), 'Set testBelongsTo failed');
        $alunoId = $aluno->getId();

        $alunoRefreshed = Author::find($alunoId);
        $revisor = $alunoRefreshed->getRevisor();

        $this->assertInstanceof(Author::class,$revisor);
        $this->assertEquals($professorId,$revisor->getId(), 'Get testBelongsTo failed');
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
        $this->assertTrue($aluno->save(), 'Set testHasOne failed');
        $alunoId = $aluno->getId();

        $professorRefreshed = Author::find($professorId);
        $dependente = $professorRefreshed->getDependent();

        $this->assertInstanceof(Author::class,$professorRefreshed);
        $this->assertEquals($alunoId,$dependente->getId(), 'Get testHasOne failed');
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
    public function testBelongsToMany()
    {

        //region Criating objects
        $autor = new Author();
        $autor->setName('Revisor '.rand(0, 9999));
        $autor->setType('beginner');
        $autor->save();

        $post1 = new Post();
        $post1->setAuthor($autor);
        $post1->setTitle('Test 1 - '.rand(0, 9999));
        $post1->setContent('CONTENT  '.rand(0, 9999));
        $post1->setPublicatedAt(Carbon::now()->addDays(2));
        $post1->save();

        $tag1 = new Tag();
        $tag1->setLabel('Test 1 - '.rand(0, 9999));
        $tag1->save();

        $post2 = new Post();
        $post2->setAuthor($autor);
        $post2->setTitle('Test 1 - '.rand(0, 9999));
        $post2->setContent('CONTENT  '.rand(0, 9999));
        $post2->setPublicatedAt(Carbon::now()->addDays(2));
        $post2->save();

        $tag2 = new Tag();
        $tag2->setLabel('Test 2 - '.rand(0, 9999));
        $tag2->save();
        //endregion

        $post1->addTag($tag1);
        $this->assertCount(1, $post1->listTags(), '1A- Number o of Belongs to maany failed');
        $this->assertCount(1, $tag1->listPosts(), '1B- Number o of Belongs to maany failed');
        $this->assertCount(0, $tag2->listPosts(), '1C- Number o of Belongs to maany failed');
        $this->assertCount(1, $post1->listTags()[0]->listPosts(), '1D- Number o of Belongs to maany failed');
        $this->assertEquals($post1->listTags()[0]->getId(), $tag1->getId(), '1- The listed element should be the same was add');

        $post2->addTag($tag1);
        $this->assertCount(1, $post1->listTags(), '2A- Number o of Belongs to maany failed');
        $this->assertCount(2, $tag1->listPosts(), '2B- Number o of Belongs to maany failed');
        $this->assertCount(0, $tag2->listPosts(), '2C- Number o of Belongs to maany failed');
        $this->assertCount(2, $post1->listTags()[0]->listPosts(), '2D- Number o of Belongs to maany failed');
        $this->assertEquals($post2->listTags()[0]->getId(), $tag1->getId(), '2- The listed element should be the same was add');

        $tag2->addPost($post1);
        $this->assertCount(2, $post1->listTags(), '3A- Number o of Belongs to maany failed');
        $this->assertCount(2, $tag1->listPosts(), '3B- Number o of Belongs to maany failed');
        $this->assertCount(1, $tag2->listPosts(), '3C- Number o of Belongs to maany failed');
        $this->assertCount(2, $post1->listTags()[0]->listPosts(), '3D- Number o of Belongs to maany failed');
        $this->assertEquals($tag2->listPosts()[0]->getId(), $post1->getId(), '3- The listed element should be the same was add');

        $tag2->addPost($post2);
        $this->assertCount(2, $post1->listTags(), '4A- Number o of Belongs to maany failed');
        $this->assertCount(2, $tag1->listPosts(), '4B- Number o of Belongs to maany failed');
        $this->assertCount(2, $tag2->listPosts(), '4C- Number o of Belongs to maany failed');
        $this->assertCount(2, $post1->listTags()[0]->listPosts(), '4D- Number o of Belongs to maany failed');
        $this->assertEquals($tag2->listPosts()[1]->getId(), $post2->getId(), '4- The listed element should be the same was add');

        try {
            $tag1->addPost($post1);
            $this->fail('Should not accept add twice');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testCustomizedName()
    {
        //region Criating objects
        $autor = new Author();
        $autor->setName('Revisor '.rand(0, 9999));
        $autor->setType('beginner');
        $autor->save();

        $post1 = new Post();
        $post1->setAuthor($autor);
        $post1->setTitle('Test 1 - '.rand(0, 9999));
        $post1->setContent('CONTENT  '.rand(0, 9999));
        $post1->setPublicatedAt(Carbon::now()->addDays(2));
        $post1->save();

        //endregion

        $comment = new Comment();
        $comment->setPost($post1)->setName('José da Silva')->setComment('CST21 é legalzinho');
        $this->assertTrue($comment->save());
        $commentId = $comment->getId();

        $commentRefreshed = Comment::find($commentId);

        $this->assertEquals($commentRefreshed->getName(), 'José da Silva');
        $this->assertEquals($commentRefreshed->getId(), $commentId);
        $this->assertEquals($commentRefreshed->getPost()->getId(), $post1->getId());

    }


}
