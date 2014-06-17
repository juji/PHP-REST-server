PHP-REST-server
=========

---

PHP-REST-server is  lightweight json/html REST server to create API services.

####It works with:
- Apache web-server 
- php5


####Feature
- Support  [CORS](http://en.wikipedia.org/wiki/Cross-origin_resource_sharing)
- GET, POST, PUT, DELETE
- Will return `Content-Type: application/json` when called with the header `X-Requested-With: xmlHttpRequest`. Otherwise will return `Content-Type: text/html` with the payload in `#data` element.
- Always return with the following format

```json
{
    "status": true, //or false
    "message": "the message" //String|Array|Object
}
```
---

How
----
1. Set some variables,
2. include your libraries,
3. Create init script,
4. Add module,
5. Go home and sleep.


---

1. Set some variables
--
Set the variables in `settings.php`. These variables are settings for [CORS](http://en.wikipedia.org/wiki/Cross-origin_resource_sharing)

---

2. Create init scripts
---

Create you initialization logic in `init.php`.  Useful for Authentication.

---

3. Include your libraries
---

Include your own libraries in `includes.php`, or you can do that on *per-module* basis.

---

4. Add module
---

A modul is your API resource. Add your module in the `module` directory.

###A modul is a PHP file, or a directory with an `index.php` file.
*module/__user__.php* -> will be called when we need the `user` modul

*module/__item__/index.php* -> will be called when we need the `item` modul


###A modul is included based on the REQUEST_URI

_HTTP://api.yoursite.com/**user**/jhon/_  -> will include the module `user`

_HTTP://api.yoursite.com/**item**/34/_  -> will include the module `item`

```php
// GET HTTP://api.yoursite.com/user/jhon/
// we will automatically include user.php or user/index.php
// just like the code below.

if( file_exists( 'module/user.php' ) ) include 'module/user.php';
else if( file_exists( 'module/user/index.php' ) ) include 'module/user/index.php';
else fileNotFound();

```


###A modul consists of Route definitions

For example, in *user.php*
```php
Router::add('POST', 'user', function($d){
    // add user to databse here
});

Router::add('user', function($d){
    // do stuf here
});

Router::add('user/:id', function($d){
    // do stuf here
});
    
```

To learn more about the Router, read the [Router Documentation](https://github.com/juji/PHP-Router)



###You can read info about current request using the `Request` class
```php
$URI = Request::uri();          // array, with URI path
$METHOD = Request::method();    // GET || POST || PUT || DELETE
$DATA = Request::data();        // The request payload

// you can access payload with $_GET, $_POST, $_PUT, $_DELETE
```
Read the below Documentation about the `Request` class

###You can send response using the `Response` class
```php
Response::ok('Nice One!');
```
```php
Response::error('Something bad is happening!');
```

###All Exceptions and errors will be sent to the client.
```php
$bad = isBadStuff(); // true
if($bad) throw new Exception ('Bad things are happening..');
```

Will Result in
```json
{
    "status": false,
    "message": "Bad things are happening.."
}
```

PHP Run-time errors are logged to `logs/error_log`. Only send Error # to client.

---


##Documentation


####Request

```php
static Request::uri();              // REQUEST_URI [ Array ]
static Request::method();           // get || post || put || delete [ String ]
static Request::data();             // request payload [ Array ]
static Request::query();            // request query string [ String ]
static Request::xhr();              // is using ajax? [ Boolean ]
static Request::origin();           // CORS Origin header [ String || Boolean ]
static Request::originDomain();     // CORS Origin domain [ String || Boolean ]
```


####Response
```php
static Request::ok($message);              // send $message with status true
static Request::error($message);           // send $message with status false
```

####Router
Too lazy to write. Read the [Router Documentation](https://github.com/juji/PHP-Router)