<?php
namespace Bokbasen\Auth\TGTCache;

/**
 * Basic session cache class using the $_SESSION variable to cache TGT
 *
 *
 * @license https://opensource.org/licenses/MIT
 */
class Session implements TGTCacheInterface
{

    /**
     *
     * @var string
     */
    protected $namespace;

    /**
     * Create a new instance of session TGT cache, $_SESSION namespace can be set
     *
     * @param system $namespace            
     */
    public function __construct($namespace = __CLASS__)
    {
        $this->namespace = $namespace;
    }

    public function setTGT($tgt)
    {
        $_SESSION[$this->namespace]['tgt'] = $tgt;
        $_SESSION[$this->namespace]['timestamp'] = time();
    }

    public function getTGT()
    {
        return isset($_SESSION[$this->namespace]['tgt']) ? $_SESSION[$this->namespace]['tgt'] : null;
    }

    public function getCreatedUnixTimestamp()
    {
        return isset($_SESSION[$this->namespace]['timestamp']) ? $_SESSION[$this->namespace]['timestamp'] : null;
    }
}