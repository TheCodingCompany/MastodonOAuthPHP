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

use theCodingCompany\HttpRequest;

/**
 * oAuth class for use at Mastodon
 */
trait oAuth
{
    /**
     * Filename with our credentials
     */
    public static $_API_CREDENTIALS_FILENAME = "api_credentials.json";
    
    /**
     * Our API to use
     * @var type 
     */
    private $mastodon_api_url = "mastodon.social";
        
    /**
     * Holds our client_id and secret
     * @var array 
     */
    public $credentials = array(
        "client_id"     => "",
        "client_secret" => "",
        "bearer"        => ""
    );
    
    /**
     * Start at getting or creating app
     */
    private function getAppConfig(){
        //Get singleton instance
        $http = HttpRequest::Instance("https://{$this->mastodon_api_url}");
        $config = $http::post(
            "api/v1/apps", //Endpoint
            array(
                "client_name"   => "MastoTweet",
                "redirect_uris" => "urn:ietf:wg:oauth:2.0:oob",
                "scopes"        => "read write",
                "website"       => "https://www.thecodingcompany.se"
            )
        );
        //Check and set our credentials
        if(!empty($config) && isset($config["client_id"]) && isset($config["client_secret"])){
            array_merge($this->credentials, $config);
            return $this->credentials;
        }else{
            return false;
        }
    }
    
    /**
     * Save our credentials (tokens) to file
     * @param type $config
     */
    private function _save_credentials($config){
        //Set filename to save our credentials to
        $filename = realpath(__DIR__."/../".self::$_API_CREDENTIALS_FILENAME);
        if(!file_put_contents($filename, json_encode($config))){
            echo "Can't write our credentials to file. File: {$filename}";
            return false;
        }
        return true;
    }
    
    /**
     * Set the correct domain name
     * @param type $domainname
     */
    public function setMastodonDomain($domainname = ""){
        if(!empty($domainname)){
            $this->mastodon_api_url = $domainname;
        }
    }
    
    /**
     * Check credentials as file
     * @return boolean
     */
    private function _check_credentials(){
        //Check credentials
        $filename = realpath(__DIR__."/../".self::$_API_CREDENTIALS_FILENAME);
        if(file_exists($filename)){
            $this->credentials = json_decode(file_get_contents($filename), TRUE); //Force array
            return true;
        }
        
        echo "No credentials found. File: {$filename}";
        return false;
    }
    
    /**
     * Get our credentials either from file to by creating a new APP
     */
    private function _get_credentials(){
        if(!is_array($this->credentials) || !isset($this->credentials["client_id"])){
            
            //Check for existing or create new
            if($this->_check_credentials() === FALSE){
                //Get new credentials
                $this->getAppConfig();
            }
            
            //Save to file
            $this->_save_credentials($this->credentials);
        }
        return $this->credentials;
    }
    
    /**
     * Create authorization url
     */
    public function getAuthUrl(){
        //Get the API credentials
        $credentials = $this->_get_credentials();

        if(is_array($credentials) && isset($credentials["client_id"])){
            
            //Return the Authorization URL
            return "https://{$this->mastodon_api_url}/oauth/authorize/?".http_build_query(array(
                    "response_type"    => "code",
                    "redirect_uri"     => "urn:ietf:wg:oauth:2.0:oob",
                    "scope"            => "read write",
                    "client_id"        => $credentials["client_id"]
                ));
        }        
        return false;        
    }
    
    /**
     * Get access token
     * @param type $auth_code
     */
    public function getAccessToken($auth_code = ""){
        //Get the API credentials
        $credentials = $this->_get_credentials();
        
        if(is_array($credentials) && isset($credentials["client_id"])){
            
            //Request access token in exchange for our Authorization token
            $http = HttpRequest::Instance("https://{$this->mastodon_api_url}");
            $token_info = $http::Post(
                "oauth/token",
                array(
                    "grant_type"    => "authorization_code",
                    "redirect_uri"  => "urn:ietf:wg:oauth:2.0:oob",
                    "client_id"     => $credentials["client_id"],
                    "client_secret" => $credentials["client_secret"],
                    "code"          => $auth_code
                )
            );
            
            if(isset($token_info["access_token"])){
                
                //Add to our credentials
                $credentials["bearer"] = $token_info["access_token"];
                $this->credentials = $credentials;

                //Save to file
                $this->_save_credentials($credentials);
                return $token_info["access_token"];
            }
        }
        return false;
    }
}