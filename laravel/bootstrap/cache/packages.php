<?php return array (
  'barryvdh/laravel-ide-helper' => 
  array (
    'providers' => 
    array (
      0 => 'Barryvdh\\LaravelIdeHelper\\IdeHelperServiceProvider',
    ),
  ),
  'fideloper/proxy' => 
  array (
    'providers' => 
    array (
      0 => 'Fideloper\\Proxy\\TrustedProxyServiceProvider',
    ),
  ),
  'jacobcyl/ali-oss-storage' => 
  array (
    'providers' => 
    array (
      0 => 'Jacobcyl\\AliOSS\\AliOssServiceProvider',
    ),
  ),
  'laravel/tinker' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Tinker\\TinkerServiceProvider',
    ),
  ),
  'noh4ck/graphiql' => 
  array (
    'providers' => 
    array (
      0 => 'Graphiql\\GraphiqlServiceProvider',
    ),
  ),
  'nunomaduro/collision' => 
  array (
    'providers' => 
    array (
      0 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    ),
  ),
  'rebing/graphql-laravel' => 
  array (
    'providers' => 
    array (
      0 => 'Rebing\\GraphQL\\GraphQLServiceProvider',
    ),
    'aliases' => 
    array (
      'GraphQL' => 'Rebing\\GraphQL\\Support\\Facades\\GraphQL',
    ),
  ),
  'tymon/jwt-auth' => 
  array (
    'aliases' => 
    array (
      'JWTAuth' => 'Tymon\\JWTAuth\\Facades\\JWTAuth',
      'JWTFactory' => 'Tymon\\JWTAuth\\Facades\\JWTFactory',
    ),
    'providers' => 
    array (
      0 => 'Tymon\\JWTAuth\\Providers\\LaravelServiceProvider',
    ),
  ),
);