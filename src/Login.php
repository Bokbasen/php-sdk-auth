<?php
namespace Bokbasen\Auth;

use Bokbasen\Auth\TGTCache\TGTCacheInterface;

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
            'Authorization' => 'Boknett ' . $this->tgt,
            'Date' => gmdate('D, d M Y H:i:s e')
        ];
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
     * Authorize user and store TGT
     *
     * @param string $username            
     * @param string $password            
     * @throws \Exception
     */
    protected function auth($username, $password)
    {
        $this->httpClient = new \GuzzleHttp\Client([
            'allow_redirects' => false
        ]);
        
        $response = $this->httpClient->request('POST', $this->url, [
            'form_params' => [
                'username' => $username,
                'password' => $password
            ]
        ]);
        
        if ($response->getStatusCode() != 201) {
            throw new \Exception('Ticket not created. HTTP: ' . $response->getStatusCode() . ' Body:' . $response->getBody());
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