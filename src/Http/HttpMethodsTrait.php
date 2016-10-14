<?php
namespace Bokbasen\Http;

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

    const CONTENT_TYPE_HAL = 'application/hal+json';

    const CONTENT_TYPE_XML = 'application/xml';

    const CONTENT_TYPE_JPEG = 'image/jpeg';

    const CONTENT_TYPE_AUDIO_MPEG = 'audio/mpeg';

    const CONTENT_TYPE_PDF = 'application/pdf';

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