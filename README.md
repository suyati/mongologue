[![Build Status](https://api.travis-ci.org/suyati/mongologue.svg)](https://travis-ci.org/suyati/mongologue)

#Mongologue


A PHP Library to help build Microblogging Servers using Mongo DB

Supports:
* Multi Media Posts
* Groups
* Follow, Unfollow and Block Actions for Users and Groups
* Commenting on Posts
* Liking Posts


##Installation

*Mongologue* is available as a [Composer Package](https://packagist.org/packages/suyati/mongologue). 

Just add the following to your composer.json file:
```javascript
require : {"suyati/mongologue": "dev-develop"}
```  
##Using Mongologue

###Initialize
```php
$factory = new \Mongologue\Factory();
$mongologue = $factory->createMongologue(new \MongoClient("mongodb://127.0.0.1"), "MyTestDB");
```

###Add Users
```php
$user = array(
  "id"=>"1238899884791",
  "handle"=>"jdoe_1",
  "email"=>"jdoe1@x.com",
  "firstName"=>"John_1",
  "lastName"=>"Doe"
);

$mongologue->user('register', new \Mongologue\Models\User($user));
```

###Create Posts
```php
$post = array(
  "userId"=>$userId,
  "datetime"=>time(),
  "content"=>"user one",
  "category" => 1,
  "filesToBeAdded" => array(
      __DIR__."/../resources/sherlock.jpg"=>array(
          "type"=>"jpeg",
          "size"=>"100"
      )
  )
);

$mongologue->post('create', new \Mongologue\Models\Post($post));
```

###Groups
```php
$group1 = array(
  "name" => "Cool Group 1"
);
$groupId = $mongologue->group('register', new \Mongologue\Models\Group($group1));
$mongologue->group('join', $groupId, $userId);
```


