<?php
/**
 * Intellectual Property of Svensk Coding Company AB - Sweden All rights reserved.
 * 
 * @copyright (c) 2016, Svensk Coding Company AB
 * @author V.A. (Victor) Angelier <victor@thecodingcompany.se>
 * @version 1.0
 * @license http://www.apache.org/licenses/GPL-compatibility.html GPL
 * 
 */
namespace theCodingCompany;

/**
 * Mastodon main class
 */
class Mastodon
{
    //Mastodon oAuth
    use \theCodingCompany\oAuth;
    
    /**
     * Filename with our credentials
     */
    const API_CREDENTIALS_FILENAME = "api_credentials.json";
        
    /**
     * Construct new Mastodon class
     */
    public function __construct($domainname = "mastodon.social") {        
        //Look for credentials file
        $this->_check_credentials();
        
        //Set the domain name to use
        $this->setMastodonDomain($domainname);
    }
    
    /**
     * Check credentials
     * @return boolean
     */
    private function _check_credentials(){
        //Check credentials
        $filename = realpath(__DIR__."/../".self::API_CREDENTIALS_FILENAME);
        echo $filename."\r\n";
        if(file_exists($filename)){
            $this->credentials = json_decode(file_get_contents($filename), TRUE); //Force array
            return true;
        }
        exit(1);
        echo "No credentials found.";
        return false;
    }
    
    public function saveCredentials(){
        if(!is_array($this->credentials) || !isset($this->credentials["client_id"])){
            $this->getAppConfig();
        }
        $this->saveCredentials();
    }
}