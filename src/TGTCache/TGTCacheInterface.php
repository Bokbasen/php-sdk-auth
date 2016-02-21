<?php
namespace Bokbasen\Auth\TGTCache;
/**
 * 
 * @author Ketil Stadskleiv <ks@arkon.no>
 * @since Oct 6, 2015
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