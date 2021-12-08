<?php
namespace MangoPay;

/**
 * Default storage strategy implementation.
 */
class DefaultStorageStrategy implements IStorageStrategy {
    
    private $_prefixContent = '<?php exit(); ?>';
    private $_fileName = 'MangoPaySdkStorage.tmp.php';
    private $_config;
    
    function __construct($config) {
        $this->_config = $config;
    }
    
    /**
     * Gets the current authorization token.
     * @return \MangoPay\OAuthToken Currently stored token instance or null.
     */
    public function Get() {
        
        $filename = $this->GetPathToFile();
        if (!file_exists($filename))
            return null;
        
        $data = file_get_contents($filename);
        if ($data === false)
            return null;
        
        $serialized = str_replace($this->_prefixContent, '', $data);
        return unserialize($serialized);
    }

    /**
     * Stores authorization token passed as an argument.
     * @param \MangoPay\OAuthToken $token Token instance to be stored.
     */
    public function Store($token) {
        
        if (!is_writable($this->GetPathToTemporaryFolder()))
            throw new \MangoPay\Exception('Cannot create or write to file ' . $this->GetPathToTemporaryFolder());
        
        $serialized = serialize($token);
        $result = file_put_contents($this->GetPathToFile(), $this->_prefixContent . $serialized, LOCK_EX);
        if ($result === false)
            throw new \MangoPay\Exception('Cannot put token to file');
    }
    
    /**
     * Get path to storage file 
     * @return string
     */
    private function GetPathToFile() {
        return $this->GetPathToTemporaryFolder() . DIRECTORY_SEPARATOR . $this->_fileName;
    }
    
    /**
     * Get path to temporary folder  
     * @return string
     */
    private function GetPathToTemporaryFolder() {
        
        if (is_null($this->_config->TemporaryFolder))
            throw new \MangoPay\Exception('Path to temporary folder is not defined');
        
        return $this->_config->TemporaryFolder;
    }
}