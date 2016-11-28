# PHP SDK for Bokbasen authentication service

This PHP SDK enables easy usage of Bokbasen's authentication service that is required for accessing any of Bokbasen's API such as digital distribution platform, metadata or orders. Bokbasen's APIs are not public and only available on commercial terms, you must have a username/password from Bokbasen in order to use this package.
 
The basic package enable creation of a TGT that can be used for further login to API services. The package also provides an interface for caching TGTs so one can get a more efficient flow, only renewing TGT when it is about to expire. For production usage this is highly recommended. The API documentation is available on [this page](https://bokbasen.jira.com/wiki/display/api/Authentication+Service).
 
## HTTP client 
The SDK has a dependency on the virtual package php-http/client-implementation which requires to you install an adapter, but we do not care which one. That is an implementation detail in your application. We also need a PSR-7 implementation and a message factory. 

This is based on [PHP-HTTP](http://docs.php-http.org/en/latest/index.html) that provides an implementation-independent plugin system to build pipelines regardless of the HTTP client implementation used. So basically you can plugin whichever HTTP implementation you would like to use.

### I do not care, I just want it to work!

By adding a compatible HTTP adapter to your project the SDK will automatically detect the package and use this adapter. As long as you do not need any specific HTTP settings injected (such as proxy settings etc.) this will work just fine.

```$ composer require php-http/guzzle6-adapter```

## Basic usage with auto detected http client
 
 ```php
 <?php
 use Bokbasen\Auth\Login;
 try{
 	$auth = new Login('my_username', 'my_password');
 	//To use TGT manually 
 	$tgt = $auth->getTgt();
 	//To get required auth HTTP headers as an array
 	$headers = $auth->getAuthHeadersAsArray();
 	//If you are using with a Bokbasen PHP SDK, then just pass the entire $auth object 
 } catch(\Exception $e){
 	//error handling
 }
 ```
 
## Use injected HTTP client
 
 ```php
 <?php
 use Bokbasen\Auth\Login;
 try{
 	//just an example, any client implementing \Http\Client\HttpClient\HttpClient will work
 	$client = new \Http\Adapter\Guzzle6\Client();
 	$auth = new Login('my_username', 'my_password', Login::URL_PROD, null, null, $client);
 } catch(\Exception $e){
 	//error handling
 }
 ```
  
## Use TGT cache

You can cache the TGT using any [PSR-6](http://www.php-fig.org/psr/psr-6/) compatible package. Example below is using Symphony's file caching. 

 ```php
 <?php
 use Bokbasen\Auth\Login;
 use Symfony\Component\Cache\Adapter\FilesystemAdapter;
 try{
 	$cache = new FilesystemAdapter();
 	$auth = new Login('my_username', 'my_password', Login::URL_PROD, $cache);
	//If the TGT is cached, the SDK will only call the Bokbasen login server when the token is set to expire
 } catch(\Exception $e){
 	//error handling
 }