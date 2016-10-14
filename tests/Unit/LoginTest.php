<?php
namespace Bokbasen\Auth\Tests\Unit;

use Bokbasen\Auth\Login;
use Http\Mock\Client;

class LoginTest extends \PHPUnit_Framework_TestCase
{

    public function testLogin()
    {
        $client = new Client();
        
        $response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        
        $tgt = 'TGT-152-leeshOABMDJE41s55z9WBLq7d7kk2ONUQozYHOF2FimxI5a9D9Z-login.boknett.no';
        $response->method('getHeaderLine')->willReturn($tgt);
        $response->method('getStatusCode')->willReturn('201');
        
        $client->addResponse($response);
        
        $auth = new Login('test', 'test', null, null, $client);
        $this->assertEquals($auth->getTgt(), $tgt);
    }

    public function testFailedLogin()
    {
        $this->expectException(\Bokbasen\Auth\Exceptions\BokbasenAuthException::class);
        
        $client = new Client();
        
        $response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        
        $tgt = null;
        $response->method('getHeaderLine')->willReturn($tgt);
        $response->method('getStatusCode')->willReturn('400');
        
        $client->addResponse($response);
        
        $auth = new Login('test', 'test', null, null, $client);
        $auth->authenticate();
    }
}