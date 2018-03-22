<?php
/**
 * Created by PhpStorm.
 * User: dxc1993
 * Date: 2018/3/20
 * Time: 18:09
 */

namespace App\GraphQL\Query;

use App\Post;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class PostQuery extends Query
{

    protected $attributes = [
        'name' => 'post'
    ];

    public function type()
    {

        return Type::listOf(GraphQl::type('post'));
    }


    public function args()
    {
        return [
            'id' => ['name' => 'id', Type::int()],
            'email' => ['name' => 'email', Type::string()],
        ];
    }

    public function resolve($root, $args)
    {

        $query = Post::query();
        $post = new Post();
        if (isset($args['id'])) {
            $post = $query->where('id', $args['id']);
        }

        if (isset($args['email'])) {
            $post = $query->where('email', $args['email']);
        }


        return $post->get();

    }


}