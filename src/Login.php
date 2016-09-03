<?php
namespace Bokbasen\Auth;

use Bokbasen\Auth\TGTCache\TGTCacheInterface;
use GuzzleHttp\RequestOptions;
use Bokbasen\Auth\Exceptions\BokbasenAuthException;

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
 * Authentication class to use against Bokbasen's web services
 *
 * @link https://bokbasen.jira.com/wiki/display/api/Authentication+Service API Documentation
 * @license https://opensource.org/licenses/MIT
 */
class Login
{

    /**
     *
     * @var string
     */
    protected $tgt;

    /**
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     *
     * @var TGTCacheInterface
     */
    protected $tgtCache;

    /**
     * Number of minutes before a TGT should be refreshed
     *
     * @var int
     */
    protected $tgtExpireMinutes = self::DEFAULT_TGT_EXPIRE_TIME_MINUTES;

    const URL_PROD = 'https://login.boknett.no/v1/tickets';

    const URL_TEST = 'https://login.boknett.webbe.no/v1/tickets';

    const HEADER_TGT = 'Boknett-TGT';

    const BEARER_NAME = 'Boknett';

    const HTTP_HEADER_DATE_FORMAT = 'D, d M Y H:i:s e';

    const DEFAULT_TGT_EXPIRE_TIME_MINUTES = 115;

    /**
     *
     * @param string $url            
     * @param string $username            
     * @param string $password            
     * @param TGTCacheInterface $tgtCache            
     */
    public function __construct($url, $username, $password, TGTCacheInterface $tgtCache = null)
    {
        $this->url = $url;
        $this->tgtCache = $tgtCache;
        
        if (! $this->isCachedTGT($tgtCache)) {
            $this->auth($username, $password, $tgtCache);
        }
    }

    /**
     * Check if TGT is cached and if cache is valid, will set $this->tgt to cached value if true
     *
     * @param TGTCacheInterface $tgtCache            
     * @return bool
     */
    protected function isCachedTGT(TGTCacheInterface $tgtCache = null)
    {
        if (is_null($tgtCache) || empty($tgtCache->getTGT()) || $this->isTGTSoonExpired($tgtCache)) {
            return false;
        } else {
            $this->tgt = $tgtCache->getTGT();
            return true;
        }
    }

    /**
     * Authorize user and store TGT
     *
     * @param string $username            
     * @param string $password            
     * @param array $httpOptions            
     * @throws BokbasenAuthException
     */
    protected function auth($username, $password, array $httpOptions = [])
    {
        $httpOptions = array_merge([
            RequestOptions::ALLOW_REDIRECTS => false
        ], $httpOptions);
        $this->httpClient = new \GuzzleHttp\Client($httpOptions);
        
        $response = $this->httpClient->request('POST', $this->url, [
            RequestOptions::FORM_PARAMS => [
                'username' => $username,
                'password' => $password
            ]
        ]);
        
        if ($response->getStatusCode() != 201) {
            throw new BokbasenAuthException('Ticket not created. HTTP: ' . $response->getStatusCode() . ' Body:' . $response->getBody());
        }
        $tgtHeaders = $response->getHeader(self::HEADER_TGT);
        $this->tgt = array_pop($tgtHeaders);
        
        if (! is_null($this->tgtCache)) {
            $this->tgtCache->setTGT($this->tgt);
        }
    }

    /**
     *
     * @param TGTCacheInterface $tgtCache            
     * @return bool
     */
    public function isTGTSoonExpired(TGTCacheInterface $tgtCache = null)
    {
        $dateTime = time() - $this->tgtExpireMinutes * 60;
        
        return $dateTime > $tgtCache->getCreatedUnixTimestamp();
    }

    /**
     *
     * @return the $tgt
     */
    public function getTgt()
    {
        return $this->tgt;
    }

    /**
     *
     * @param number $tgtExpireMinutes            
     */
    public function setTgtExpireMinutes($tgtExpireMinutes)
    {
        $this->tgtExpireMinutes = $tgtExpireMinutes;
    }

    /**
     * Invalidates current TGT for future use
     */
    public function logout()
    {
        $this->httpClient->delete($this->url . '/' . $this->tgt);
    }

    /**
     * Get authorzation headers as array
     *
     * @return array
     */
    public function getAuthHeadersAsArray()
    {
        return [
            'Authorization' => self::BEARER_NAME . ' ' . $this->tgt,
            'Date' => gmdate(self::HTTP_HEADER_DATE_FORMAT)
        ];
    }

    /**
     * If no TGT cache is defined, then destruct will perform a HTTP DELETE call to clear the ticket
     */
    public function __destruct()
    {
        if (! empty($this->tgt) && empty($this->tgtCache)) {
            $this->logout();
            unset($this->tgt);
        }
    }
}