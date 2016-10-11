<?php
namespace Bokbasen\Auth\Http;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Client\HttpClient;
use Bokbasen\Auth\Login;

/**
 * Collection of http methods shared across Bokbasen PHP libraries
 *
 * @author Ketil Stadskleiv <ketil@bokbasen.no>
 */
trait HttpMethodsTrait{

    /**
     *
     * @var \Http\Client\HttpClient
     */
    protected $httpClient;

    /**
     *
     * @var \Http\Discovery\MessageFactory
     */
    protected $messageFactory;

    /**
     *
     * @var \Bokbasen\Auth\Login
     */
    protected $auth;

    /**
     *
     * @var string
     */
    protected $url;

    /**
     * Set HTTP client, if none is given autodetection is attempted
     *
     * @param HttpClient $httpClient            
     */
    public function setHttpClient(HttpClient $httpClient = null)
    {
        if (is_null($httpClient)) {
            $this->httpClient = HttpClientDiscovery::find();
        } else {
            $this->httpClient = $httpClient;
        }
    }

    /**
     * Create a message factory
     *
     * @return \Http\Discovery\MessageFactory
     */
    protected function getMessageFactory()
    {
        if (is_null($this->messageFactory)) {
            $this->messageFactory = MessageFactoryDiscovery::find();
        }
        
        return $this->messageFactory;
    }

    /**
     *
     * @param Login $auth            
     * @param array $customHeaders            
     */
    protected function makeHeadersArray(Login $auth, array $customHeaders = [])
    {
        return array_merge($auth->getAuthHeadersAsArray(), $customHeaders);
    }
}