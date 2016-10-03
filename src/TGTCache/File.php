<?php
namespace Bokbasen\Auth\TGTCache;

use Bokbasen\Auth\Exceptions\BokbasenAuthException;

/**
 * Basic session cache class using the $_SESSION variable to cache TGT
 *
 *
 * @license https://opensource.org/licenses/MIT
 */
class File implements TGTCacheInterface
{

    /**
     *
     * @var string
     */
    protected $pathToFile;

    /**
     *
     * @var array
     */
    protected $dataInFile;

    const FILE_DELIMITER = ';';

    /**
     *
     * @param string $pathTofile            
     */
    public function __construct($pathTofile)
    {
        $this->pathToFile = $pathTofile;
    }

    protected function getDataAsArray()
    {
        if (empty($this->dataInFile)) {
            $data = file_get_contents($this->pathToFile);
            
            if (empty($data)) {
                return [];
            }
            
            $this->dataInFile = explode(self::FILE_DELIMITER, $data);
            
            if (count($this->dataInFile) != 2) {
                throw new BokbasenAuthException('Invalid data in file, explode gave more than 2 elements: ' . $this->pathToFile);
            }
        }
        
        return $this->dataInFile;
    }

    public function setTGT($tgt)
    {
        $data = $tgt . self::FILE_DELIMITER . time();
        $bytes = file_put_contents($this->pathToFile, $data);
        
        if ($bytes === false) {
            throw new BokbasenAuthException('Could not write to file: ' . $this->pathToFile);
        }
    }

    public function getTGT()
    {
        $data = $this->getDataAsArray();
        return isset($data[0]) ? $data[0] : null;
    }

    public function getCreatedUnixTimestamp()
    {
        $data = $this->getDataAsArray();
        return isset($data[1]) ? $data[1] : null;
    }
}