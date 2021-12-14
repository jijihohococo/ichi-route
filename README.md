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
* [Dependency Injection](#dependency-injection)
* [Middleware](#middleware)
* [Caching Route](#caching-route)
* [Customized Functions](#customized-functions)

## Installation

```php

```

## Single Route

You can add routes with "get","post","put","delete" and "head" functions.

<b>Each function represents the each route method</b>

You can add route with closure function or the function of controller class.

```php
use JiJiHoHoCoCo\IchiRoute\Router\Route;

$route=new Route;
$route->get('items',function(){
	echo "show items";
});
$route->get('items','App\Controllers\ItemController@show');
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

## Dependency Injection

## Middleware

## Caching Route



