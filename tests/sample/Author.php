<?php

namespace App;

use CST21\Shareables\BaseModel;
use Carbon\Carbon;
use App\Posts\Post;

/**
 * Class Author
 *
 * @method $this       setRevisor(Author $Author = null)
 * @method Author|null getRevisor()
 * @method Author      getDependent()
 * @method $this       setName(string $name)
 * @method string      getName()
 * @method $this       setType(string $type)
 * @method string      getType()
 * @method Post[]      listPosts()
 *
 * @package App
 */
class Author extends BaseModel
{
protected static $__relationships = array (
  'Revisor' => 
  array (
    'rel' => 'belongsTo',
    'model' => 'App\\Author',
    'local_col' => 'fk_revisor',
    'foreign_col' => 'id',
  ),
  'Dependent' => 
  array (
    'rel' => 'hasOne',
    'model' => 'App\\Author',
    'local_col' => 'id',
    'foreign_col' => 'fk_revisor',
  ),
  'Posts' => 
  array (
    'rel' => 'hasMany',
    'model' => 'App\\Posts\\Post',
    'local_col' => 'id',
    'foreign_col' => 'fk_author',
  ),
);
protected static $__attSet = array (
  'setRevisor' => 'Revisor',
  'setName' => 'name',
  'setType' => 'type',
);
protected static $__attGet = array (
  'getRevisor' => 'Revisor',
  'getDependent' => 'Dependent',
  'getName' => 'name',
  'getType' => 'type',
  'listPosts' => 'Posts',
);
}
