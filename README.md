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
* [CSRF Token Authentication](#csrf-token-authentication)
* [API Request Authentication](#api-request-authentication)
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

To run all of your routes, you must use "run()" function.

<b>You must use "run()" function after declaring all routes for your system</b>

```php

$route->run();

```

After running route function, the routes (URL) are able to run

```php

'items/' (GET METHOD)

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

After running route function, the routes (URL) are able to run

```php

'items/show/1' (GET METHOD)
'items/show/2' (GET METHOD)

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

	// POST METHOD //
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

After running route function, the routes (URL) are able to run

```php
	
	'items/' (GET METHOD) // Go to to get items' list
	'items/create' (GET METHOD) // Go to create item
	'items/create' (POST METHOD) // Create items
	'items/1/edit' (GET METHOD) // Go to update item
	'items/1/edit' (PUT METHOD) // Update item
	'items/1/destroy' (DELETE METHOD) // Delete item

```

## Prefix Route

You can use prefix route to make groups

```php
$route->group(['url_group'=>'admin'],function(){
	
	$this->get('items','App\Controllers\ItemController@getItems');
	$this->get('brands','App\Controllers\BrandController@getBrands');
});
```

So the below url are able to use

```php

'admin/items' (GET METHOD)
'admin/brands' (GET METHOD)

```

You can add the single routes and parameter routes in the group closure function.

<b>Don't include '/' in declaring "url_group"</b>

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

You can add middlewares in single route like below

```php

$route->get('order','App\Controllers\OrderController@order',[
'App\Middlewares\OrderMiddleware'
]);

```
You must delcare middleware class

<b>Your middleware classes must be autoloaded in composer as we mentioned before</b>
```php

namespace App\Middlewares;

use JiJiHoHoCoCo\IchiRoute\Middleware\MainMiddleware;

class OrderMiddleware extends MainMiddleware{

	public function handle(){
		//--your business login--//
		return $this->next();
	}
}
```
You must extend <b>JiJiHoHoCoCo\IchiRoute\Middleware\MainMiddleware</b> and add "handle()" function in your "handle" function, you must always return "next()" function. You can check your business transactions in this "handle" function.

You can add multiple middleware classes

```php

$route->get('order','App\Controllers\OrderController@order',[
	'App\Middlewares\LoginMiddleware',
	'App\Middlewares\OrderMiddleware'
]);

```
Those middlewares will be loaded sequently because of using "next()" function in each "handle()" function.

You can add parameters in middleware with your parameter routes

```php

$route->get('items/{id}','App\Controllers\ItemController@getItems',[
	'App\Middlewares\CheckItemMiddleware:id'
]);

```

In your middleware class

```php
namespace App\Middlewares;

use JiJiHoHoCoCo\IchiRoute\Middleware\MainMiddleware;

class CheckItemMiddleware extends MainMiddleware{

	public function handle($id){
		//--your business login--//
		return $this->next();
	}

}
```

You can add multiple parameters in middleware with your parameter routes

```php

$route->get('items/{id}/{stock_id}',
	'App\Controllers\ItemController@getItems',[
	'App\Middlewares\CheckItemMiddleware:id,stock_id'
]);

```
In your middleware class

```php
namespace App\Middlewares;

use JiJiHoHoCoCo\IchiRoute\Middleware\MainMiddleware;

class CheckItemMiddleware extends MainMiddleware{

	public function handle($id,$stock){
		//--your business login--//
		return $this->next();
	}

}
```

You can add middlewares in prefix routes like the way you do in single routes and parameter routes

```php

$route->group(['url_group' => 'admin' , 
	'middleware' => ['App\Middlewares\CheckAdminMiddleware']
 ],function(){
 	$this->resource('items','App\Controllers\ItemController');
 });

```

You can add only middleware in prefix routes.

```php

$route->group(['middleare' => ['App\Middlewares\CheckUserMiddleware'] ],function(){
	$this->get('order','App\Controllers\OrderController@order');
});

```

If you have the same middleware path for all middlewares, you can set base middleware path for all routes before adding routes.

```php

$route->setBaseControllerPath('App\Middlewares');

```

<b>There are some middlewares that already written in this library.
For those middlewares you must declare their middleware path completely.</b>

## CSRF Token Authentication

You can protect create and update route with CSRF Token Authentication

You must generate CSRF Token before declaring the routes

```php

use JiJiHoHoCoCo\IchiRoute\Router\Route;

generateCSRFToken();

$route=new Route;
$route->post('items','App\ItemController@create',[
'JiJiHoHoCoCo\IchiRoute\Middleware\CSRFMiddleware'
]);
```

You can add <b>JiJiHoHoCoCo\IchiRoute\Middleware\CSRFMiddleware</b> in your prefix routes too.

```php

use JiJiHoHoCoCo\IchiRoute\Router\Route;

generateCSRFToken();

$route=new Route;
$route->group(['middleare' => 
	['JiJiHoHoCoCo\IchiRoute\Middleware\CSRFMiddleware'] ],function(){
	$this->post('items','App\ItemController@create');
});

```
## API Request Authentication

You can add middleware to accept only API request for your route

```php

$route->get('items_api','App\Controllers\ItemController@getItems',[
	'JiJiHoHoCoCo\IchiRoute\Middleware\APIMiddleware'
]);

```

## CORS

You can make CORS to get your data from another domains

```php

$route->get('items_api','App\Controllers\ItemController@getItems',[
	'JiJiHoHoCoCo\IchiRoute\Middleware\CORSMiddleware'
]);

```

You can make options for your CORS with <b>JiJiHoHoCoCo\IchiRoute\Setting\CORS</b>

```php
 
use JiJiHoHoCoCo\IchiRoute\Setting\CORS;

CORS::setAvialableSites(['http://domain-one.com','http://domain-two.com']); 
// To set Access Control Allow Origins (default is '*')

CORS::setAvailableSitesRegex(['/w3schools/']);
// To set Access Control Allow Origins according to regex

CORS::setAvialableMethods(['GET','POST']);
// To set Access Control Allow Origin Methods (default is '*')

CORS::setAvailableHeaders(['X-Custom-Header','Upgrade-Insecure-Requests']);
// To set Access Control Allow Origin Headers (default is '*')

CORS::setToAllowCredential();
// To set Access Control Allow Credentials to TRUE. (default is 'false')

CORS::setMaxAge(3600);
// To set Access Control Max Age (default is 0)
```
## Caching Route



