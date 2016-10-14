<?php
namespace Bokbasen\Auth\Tests\Integration;

use Bokbasen\Auth\Login;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class LoginTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var array
     */
    protected $config;

    /**
     *
     * @var \Bokbasen\Auth\Login
     */
    protected $auth;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->config = parse_ini_file(__DIR__ . '/config.ini');
    }

    public function testHttpClientsLogin()
    {
        // Using default autodetected client
        $this->auth = new Login($this->config['username'], $this->config['password'], null, $this->config['url']);
        $this->assertNotEmpty($this->auth->getTgt());
        
        $adapter = new \Http\Adapter\Guzzle6\Client();
        
        $this->auth = new Login($this->config['username'], $this->config['password'], null, $this->config['url'], $adapter);
        $this->assertNotEmpty($this->auth->getTgt());
    }

    public function testLogin()
    {
        $this->auth = new Login($this->config['username'], $this->config['password'], null, $this->config['url']);
        $this->assertNotEmpty($this->auth->getTgt());
    }

    public function testFailedLogin()
    {
        $this->expectException(\Bokbasen\Auth\Exceptions\BokbasenAuthException::class);
        $auth = new Login('dsds', 'fsdfsdfo8s', null, $this->config['url']);
        $auth->authenticate();
    }

    public function testCache()
    {
        $cache = new FilesystemAdapter(null, 0, $this->config['fileCacheDir']);
        $auth = new Login($this->config['username'], $this->config['password'], $cache, $this->config['url']);
        $this->assertNotEmpty($auth->getTgt());
        
        // rerun auth with cache to see that we get the same TGT
        $auth2 = new Login($this->config['username'], $this->config['password'], $cache, $this->config['url']);
        $this->assertEquals($auth->getTgt(), $auth2->getTgt());
    }
}