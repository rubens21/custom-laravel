<?php

namespace App\Posts;

use CST21\Shareables\BaseModel;
use App\Author;
use Carbon\Carbon;
use App\Posts\Tag;

/**
 * Class Post
 *
 * @method $this       setAuthor(Author $Author)
 * @method Author      getAuthor()
 * @method $this       setTitle(string $title)
 * @method string      getTitle()
 * @method $this       setContent(string $content)
 * @method string      getContent()
 * @method $this       setPublicatedAt(Carbon $publicatedAt = null)
 * @method Carbon|null getPublicatedAt()
 * @method $this       setAproved(bool $aproved = null)
 * @method bool|null   isAproved()
 * @method $this       addTag(Tag $tag)
 * @method Tag[]       listTags()
 *
 * @package App\Posts
 */
class Post extends BaseModel
{
protected static $__relationships = array (
  'Author' => 
  array (
    'rel' => 'belongsTo',
    'model' => 'App\\Author',
    'local_col' => 'fk_author',
    'foreign_col' => 'id',
  ),
  'Tags' => 
  array (
    'rel' => 'belongsToMany',
    'pivot' => 'tagged',
    'model' => 'App\\Posts\\Tag',
    'local_col' => 'fk_post',
    'foreign_col' => 'fk_tag',
  ),
);
protected static $__attSet = array (
  'setAuthor' => 'Author',
  'setTitle' => 'title',
  'setContent' => 'content',
  'setPublicatedAt' => 'publicated_at',
  'setAproved' => 'aproved',
  'addTag' => 'Tags',
);
protected static $__attGet = array (
  'getAuthor' => 'Author',
  'getTitle' => 'title',
  'getContent' => 'content',
  'getPublicatedAt' => 'publicated_at',
  'isAproved' => 'aproved',
  'listTags' => 'Tags',
);
}
