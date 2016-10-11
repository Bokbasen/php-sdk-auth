# PHP SDK for Bokbasen authentication service

This PHP SDK enables easy usage of Bokbasen's authentication service that is required for accessing any of Bokbasen's API suchs as digital distribution platform, metadata or orders. Bokbasen's APIs are not public and only available on commercial terms, you must have a username/password from Bokbasen in order to use this package.
 
The basic package enable creation of a TGT that can be used for further login to API services. The package also provides an interface for caching TGTs so one one can get a more effecient flow, only renewing TGT when it is about to expire. The API documenation is available on [this page](https://bokbasen.jira.com/wiki/display/api/Authentication+Service).
 
## Basic usage
 
 ```php
 <?php
 use Bokbasen\Auth\Login;
 try{
 	$auth = new Login('my_username', 'my_password');
 	//To use TGT manually 
 	$tgt = $auth->getTgt();
 	//If you are using with a Bokbasen PHP SDK, then just pass the entire $auth object 
 } catch(\Exception $e){
 	//error handling
 }
 ```
 
## Use with proxy
 
 ```php
 <?php
 use Bokbasen\Auth\Login;
 use GuzzleHttp\RequestOptions;
 try{
 	$httpOptions = [RequestOptions::PROXY => 'https://urlToPRoxy:port'];
 	$auth = new Login('my_username', 'my_password', null, Login::URL_PROD, $httpOptions);
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
 	$auth = new Login('my_username', 'my_password', $cache);
	//If the TGT is cached, the SDK will only call the Bokbasen login server when the token is set to expire
 } catch(\Exception $e){
 	//error handling
 }