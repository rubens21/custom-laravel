<?php

namespace App;

use CST21\Shareables\BaseModel;
use App\Posts\Tag;
use App\Posts\Post;
use Carbon\Carbon;

/**
 * Class Tagged
 *
 * @method $this setTag(Tag $Tag)
 * @method Tag   getTag()
 * @method $this setPost(Post $Post)
 * @method Post  getPost()
 *
 * @package App
 */
class Tagged extends BaseModel
{
    protected static $__relationships = array(
  'Tag' =>
  array(
    'rel' => 'belongsTo',
    'model' => 'App\\Posts\\Tag',
    'local_col' => 'fk_tag',
    'foreign_col' => 'id',
  ),
  'Post' =>
  array(
    'rel' => 'belongsTo',
    'model' => 'App\\Posts\\Post',
    'local_col' => 'fk_post',
    'foreign_col' => 'id',
  ),
);
    protected static $__attSet = array(
  'setTag' => 'Tag',
  'setPost' => 'Post',
);
    protected static $__attGet = array(
  'getTag' => 'Tag',
  'getPost' => 'Post',
);
}
