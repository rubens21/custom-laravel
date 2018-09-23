<?php

namespace App\Posts;

use CST21\Shareables\BaseModel;
use App\Posts\Post;
use Carbon\Carbon;

/**
 * Class Tag
 *
 * @method $this  addPost(Post $post)
 * @method Post[] listPosts()
 * @method $this  setLabel(string $label)
 * @method string getLabel()
 *
 * @package App\Posts
 */
class Tag extends BaseModel
{
protected static $__relationships = array (
  'Posts' => 
  array (
    'rel' => 'belongsToMany',
    'pivot' => 'tagged',
    'model' => 'App\\Posts\\Post',
    'local_col' => 'fk_tag',
    'foreign_col' => 'fk_post',
  ),
);
protected static $__attSet = array (
  'addPost' => 'Posts',
  'setLabel' => 'label',
);
protected static $__attGet = array (
  'listPosts' => 'Posts',
  'getLabel' => 'label',
);
}
