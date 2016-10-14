<?php
namespace Bokbasen\Http;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Client\HttpClient;
use Psr\Http\Message\ResponseInterface;
use Bokbasen\Auth\Login;

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
/**
 * Collection of http methods shared across Bokbasen PHP libraries ensuring support for HTTP discovery and HTTP client injection
 *
 * @license https://opensource.org/licenses/MIT
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

    /**
     * Check if the auth client should attempt reauthetication based on response.
     * Will only run reauth once.
     *
     * @param ResponseInterface $response            
     * @return boolean
     */
    protected function needReAuthentication(ResponseInterface $response)
    {
        if ($response->getStatusCode() == 401 && ! $this->auth->isReAuthAttempted()) {
            $this->auth->reAuthenticate();
            return true;
        } else {
            return false;
        }
    }
}