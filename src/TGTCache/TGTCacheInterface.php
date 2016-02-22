<?php
namespace Bokbasen\Auth\TGTCache;
/**
 * TGT Cache interface can be used to implement TGT caching with any kind of persistance mechanism 
 *  
 * @license https://opensource.org/licenses/MIT
 */
interface TGTCacheInterface{
    
    /**
     * Set TGT, must also set the created unix timestamp using time()
     * 
     * @param string $tgt
     */
    public function setTGT($tgt);
    /**
     * Get the TGT, return null if not set
     * 
     * @return string
     */
    public function getTGT();
    /**
     * Return the Unix timestamp for when the TGT was set, null if not set
     * 
     * @return string
     */
    public function getCreatedUnixTimestamp();
}