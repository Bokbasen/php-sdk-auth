<?php
namespace Bokbasen\Auth\Tests;

use Bokbasen\Auth\Login;

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

    public function setUp()
    {
        $this->config = parse_ini_file(__DIR__ . '/config.ini');
    }

    public function testLogin()
    {
        $this->auth = new Login($this->config['url'], $this->config['username'], $this->config['password']);
        $this->assertNotEmpty($this->auth->getTgt());
    }

    public function testFailedLogin()
    {
        $this->expectException(\GuzzleHttp\Exception\ClientException::class);
        
        try {
            $auth = new Login($this->config['url'], 'dsds', 'fsdfsdfo8s');
            $this->assertNotEmpty($auth->getTgt());
        } catch (\Exception $e) {
            $this->assertEquals(400, $e->getCode());
            throw $e;
        }
    }
}