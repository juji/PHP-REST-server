PHP-REST-server
=========

---

PHP-REST-server is  lightweight json/html REST server to create API services.

####It works with:
- Apache web-server 
- Nginx
- php5


####Feature
- Support  [CORS](http://en.wikipedia.org/wiki/Cross-origin_resource_sharing)
- GET, POST, PUT, DELETE, OPTIONS, HEAD
- Will return `Content-Type: application/json` when called with the header `X-Requested-With: xmlHttpRequest`. Otherwise will return `Content-Type: text/html` with the payload in `#data` element.
- Returns data with the following format

```json
{
    "status": true, //or false
    "message": "the message" //String|Array|Object
}
```
- or for HTML data
```json
<div id="data">
	{"status": true,"message": "the message"}
</div>
```
---

How To
----
1. Set some variables,
2. include your libraries,
3. Create init script,
4. Add module,
5. Go home and sleep.


---

1. Set some variables
--
Set the variables in `_settings.php`. Below are all the settings and it's default value

```
// use CORS 
static $CORS = true;

// allowed domain
// '*' means allow for all
static $CORS_ALLOW = '*';

// CORS allowed method
static $CORS_METHODS = 'GET, POST, PUT, DELETE, OPTIONS, HEAD';

// CORS allowed header
static $CORS_HEADERS = 'X-Requested-With, Origin, X-CSRFToken, Content-Type, Accept';

// Root directory of API endpoint
// example: 
// http://www.sample.com/api/v3 -> '/api/v3/'
// http://api.sample.com/ -> '/'
static $ROOT_DIR = '/api/';

// Error handling
static $ERROR_EMAIL = false;
static $ERROR_EMAIL_SUBJECT = 'API error';

// generic error message
// leave blank to send system error text to client
static $ERROR_MSSG = '';
static $BACKTRACE_LIMIT = 5;

static $CONTENT_TYPE = '';

// encapsulate data for an html response in html tag
// ISPs just love to inject scripts
static $HTMLELEMENT_ID = 'data';
```

---

2. Include your libraries
---

Include your own libraries in `_includes.php`, or you can do that on *per-module* basis.

---

3. Create init script
---

Create you initialization logic in `_init.php`.  Useful for Authentication, rate-limit, etc.

---

4. Add module
---

A module is your API resource. Add your module in the `_module` directory.

###A module is a PHP file, or a directory with an `index.php` file.

*module/__user__.php* -> is the `user` modul

*module/__item__/index.php* -> is the `item` modul


###A module is included based on the REQUEST_URI

Example:
```
GET /user/123
# `_module/user.php` will be included

GET /item/234/profile
# `_module/item/index.php` will be included
```
---


###A module consists of Route definitions

For example, in *user.php*
```php
Router::add('user', function($d){
    // do stuf here
});

Router::add('get', 'user/:id', function($d){
    // do stuff
});
```

To learn more about the Router, read the [Router Documentation](https://github.com/juji/PHP-Router)


##Request::

You can read info about current request using the `Request` class
```php
$URI = Request::uri();          // array, with URI path
$METHOD = Request::method();    // GET, POST, etc
$DATA = Request::data();        // The request payload

// you can also access data payload with 
// $_GET, $_POST, $_PUT, $_DELETE
```


##Response::
You can send response using the `Response` class
```php
Response::ok('Nice One!');
```
```php
Response::error('Something bad is happening!');
```
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