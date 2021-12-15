# ICHI PHP ROUTER

ICHI PHP ROUTER is aimed to be the fast PHP Router with ease to use and protecting the security vulnerabilities

## License

This package is Open Source According to [MIT license](LICENSE.md)

## Table Of Content

* [Installation](#installation)
* [Single Route](#single-route)
* [Paramter Route](#parameter-route)
* [Resource Route](#resource-route)
* [Prefix Route](#prefix-route)
* [CSRF Token Authentication](#csrf-token-authentication)
* [Dependency Injection](#dependency-injection)
* [Middleware](#middleware)
* [CORS](#cors)
* [Caching Route](#caching-route)
* [Customized Functions](#customized-functions)

## Installation

```php

```

## Single Route

You can add routes with "get","post","put","delete" and "head" functions.

<b>Each function represents the each route method</b>

You can add route with closure function or the function of controller class.

<i>With closure function</i>
```php
use JiJiHoHoCoCo\IchiRoute\Router\Route;

$route=new Route;
$route->get('items',function(){
	echo "show items";
});
```

<i>With Controller class</i>
```php

$route->get('items','App\Controllers\ItemController@show');

```
<b>You must autoload your the controller folder before using the route function in your composer.json</b>

```json
"autoload": {
	"psr-4": {
		"App\\": "app/"
	}
}
```

If you have the same controller path for all controllers, you can set base controller path for all routes before adding routes.

```php

$route->setBaseControllerPath('App\Controllers');

```

To run all of your routes, you must

```php

$route->run();

```

## Parameter Route

In many cases, you have a time to make parameter route

```php

$route->get('items/show/{id}','App\Controllers\ItemController@show');

```

In your controller class

```php
namespace App\Controllers;

class ItemController{

	// 'items/show/{id}' //
	public function show($id){
		echo $id;
	}
}

```
You can also do with closure function

```php

$route->get('items/show/{id}',function($id){
	echo $id;
})

```

## Resource Route

You can CRUD routes with one route method

```php

$route->resource('items','App\Controllers\ItemController');

```

In your controller class

```php
namespace App\Controllers;

class ItemController{

	// GET METHOD //
	// 'items' //
	public function index(){

	}

	// GET METHOD //
	// 'items/create' //
	public function create(){

	}


	// GET METHOD //
	// 'items/{id}/edit'
	public function edit($id){

	}

	// PUT METHOD //
	// 'items/{id}/edit' //
	public function update($id){

	}

	// DELETE METHOD //
	// 'items/{id}/destroy' //
	public function destroy($id){

	}


}
```

## Prefix Route

You can use prefix route to make groups

```php
$route->group(['url_group'=>'admin'],function(){
	$this->get('items','App\Controllers\ItemController@getItems');
});
```

So the below url is able to use

```php
'admin/items'
```

You can add the single routes and parameter routes in the group closure function.

## CSRF Token Authentication



## Dependency Injection

You can make dependency injection with controller class. 

You must have interface and class according to the below format

<b>Your interface and class must be autoloaded</b>

| Interface               | Class           |
|-------------------------|-----------------|
| ItemInterface           | Item            |
| ItemRepositoryInterface | Item Repository |

The functions of class that dependency injected will automatically run

In your controller class

```php
namespace App\Controllers;

use App\Repositories\ItemRepositoryInteface;
class ItemController{

	public $item;
	public function __construct(ItemRepositoryInteface $item){
		$this->item=$item;
	}
}
```
You can also add another dependency injection in your repositories.

```php
namespace App\Repositories;

use App\Repositories\{ItemRepositoryInteface,BrandRepositoryInterface};

class ItemRepository implements ItemRepositoryInteface{

	public $brand;

	public function __construct(BrandRepositoryInterface $brand){
		$this->brand=$brand;
	}
}
```


## Middleware

## CORS

## Caching Route



