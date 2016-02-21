<?php
namespace Bokbasen\Auth;

use Bokbasen\Auth\TGTCache\TGTCacheInterface;

/**
 * Authentication class to use against Bokbasen's web services
 *
 * @link https://bokbasen.jira.com/wiki/display/api/Authentication+Service API Documentation
 * @license https://opensource.org/licenses/MIT
 *         
 * @author Ketil Stadskleiv <ketil@bokbasen.no>
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
     *
     * @var int
     */
    protected $tgtExpireMinutes = self::TGT_EXPIRE_TIME_MINUTES;

    const URL_PROD = 'https://login.boknett.no/v1/tickets';

    const URL_TEST = 'https://login.boknett.webbe.no/v1/tickets';

    const HEADER_TGT = 'Boknett-TGT';

    const TGT_EXPIRE_TIME_MINUTES = 120;

    const TGT_RENEW_MARGIN = 5;

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
        return array(
            'Authorization' => 'Boknett ' . $this->tgt,
            'Date' => gmdate('D, d M Y H:i:s e')
        );
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
        $this->httpClient = new \GuzzleHttp\Client(array(
            'allow_redirects' => false
        ));
        
        $response = $this->httpClient->request('POST', $this->url, array(
            'form_params' => array(
                'username' => $username,
                'password' => $password
            )
        ));
        
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
        $dateTime = time() - ($this->tgtExpireMinutes - self::TGT_RENEW_MARGIN) * 60;
        
        return $dateTime > $tgtCache->getCreatedUnixTimestamp();
    }

    /**
     *
     * @param TGTCacheInterface $tgtCache            
     * @return bool
     */
    protected function isCachedTGT(TGTCacheInterface $tgtCache = null)
    {
        if (is_null($tgtCache) || empty($tgtCache->getTGT()) || $this->isTGTSoonExpired($tgtCache)) {
            return false;
        } else {
            $this->tgt = $tgtCache->getTGT('tgt');
        }
    }

    /**
     * If no TGT cache is defined, then destruc will perform a HTTP DELETE call to clear the ticket
     */
    public function __destruct()
    {
        // If no cache is define, clean up key
        if (! empty($this->tgt) && empty($this->tgtCache)) {
            $this->logout();
            unset($this->tgt);
        }
    }
}