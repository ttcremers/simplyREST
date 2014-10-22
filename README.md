SimplyREST
==========

SimplyREST is a Lightweight PHP RESTful router with helpers which prefers convention over configuration. It's a single script routing file which can be used with a rewrite rule in Apache/Nginx or with the integrated php5+ HTTP server. It supports all HTTP methods and has helpers for generating JSON and Javascript responses and decoding JSON POST/PUT bodies. The code is well documented and comes with an example REST controller/resource which can get you up and running quickly.

At the moment simplyREST is use as a backend for an EmberJS single page web application and is as such designed with that usage in mind. It also serves as a backend for a KnockoutJS based application which implements REST loosly, something SimplyREST also supports. 

**NOTE: simplyREST does not fully support nested resources**

SimplyREST is Memcache ready which means it will cache route configuration in Memcache so it doesn't have to go through the php tokenizer on each request. To enable Memcache support it expects Memcache to be loaded in $memcache (a pretty sane asumption).
To keep the requests as small as possible the responses served are gziped.

Install
=======
Clone or download this repository and put it in a nice place.

Usage
=====
When starting up I suggest just using the PHP build in HTTP server.
``` 
php -S 0.0.0.0:4000 simplyrest.php
```
PHP doesn't recommend this for production so you'll have to with some sort of rewrite rule there. 
```
AddDefaultCharset UTF-8

RewriteEngine on

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-l

RewriteRule .* simplyrest.php [L]
```
The above will do nicely.

For convienence SimplyREST has Composer setup and ready to use. SimplyREST itself doesn't require any external dependencies. Everything you include with Composer will also be exposed in your rest controllers/resources. Nice and clean.

Which brings us to...

## Controllers (or resources)
As stated earlier SimplyREST prefers convention over configuration. This means you should have very little configuring to do to get you started coding, infact SimplyREST only has one config option which is the path relative to the document root you want to run your api under. The default is the document root itself and in most cases this should be just fine. However you van change it by editing the folowing line in `simplyrest.php`

```php
define("BASE_PATH", '/'); // Path relative to document root
```
Feel free to open `simplyrest.php` as it contains a lot of comments of what it actually does.

As you can see in the `example.php` file the scheme SimplyREST uses to map url's to resources or script is very simpel.

The url `http://example.com/myresource` will map to a equally named php file, in this case that is `myresource.php`. Don't worry SimpyREST.php checks for evil. As expected a get on a REST controller/resource will look for a index function. This is how SimplyRED does that.

```
function <HTTP Method>_index() {}
```
So for the above example you would add a function `get_index` (don't worry a quick glance at the `example.php` controller and it will be instantly clear)

When you have SimplyREST running you can easily tests if it's all working by opening up

```
http://0.0.0.0:4000/example
```
Look how friendly it greets you! Now go on and open up example.php. You'll find enough examples with plenty of documentation. If you're planning on adding authentication to your API be sure to check out the before_filter example. 

Conclusion
==========
SimplyREST certainly isn't a full blown PHP REST server, but then again it doesn't want to be. A lot of the time we only need a small subset and we're stuck with a big bulky framework. SimpyREST is really ment to help out with single page apps and does this very well. If you're looking for something that will support your already exsisting big and complicated data set filled with nested resources (relations) then SimplyREST probably isn't for you. 

On the otherhand, if you're looking for something that will support your CMS based on EmberJS or something along those lines then you might want to try out SimplyREST.

Thomas Cremers http://vicinitysoftware.com
