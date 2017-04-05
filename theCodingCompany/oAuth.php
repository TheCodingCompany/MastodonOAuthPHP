<?php
/**
 * Intellectual Property of #Mastodon
 * 
 * @copyright (c) 2017, #Mastodon
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
     * Default headers for each request
     * @var type 
     */
    private $headers = array(
        "Content-Type" => "application/json; charset=utf-8", 
        "Accept" => "*/*"
    );
        
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
     * App config
     * @var type 
     */
    public $app_config = array(
        "client_name"   => "MastoTweet",
        "redirect_uris" => "urn:ietf:wg:oauth:2.0:oob",
        "scopes"        => "read write",
        "website"       => "https://www.thecodingcompany.se"
    );
    
    /**
     * Get the API endpoint
     * @return type
     */
    public function getApiURL(){
        return "https://{$this->mastodon_api_url}";
    }
    
    /**
     * Get Request headers
     * @return type
     */
    public function getHeaders(){
        if(isset($this->credentials["bearer"])){
            $this->headers["Authorization"] = "Bearer {$this->credentials["bearer"]}";
        }
        return $this->headers;
    }
    
    /**
     * Start at getting or creating app
     */
    public function getAppConfig(){
        //Get singleton instance
        $http = HttpRequest::Instance("https://{$this->mastodon_api_url}");
        $config = $http::post(
            "api/v1/apps", //Endpoint
            $this->app_config,
            $this->headers
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
     * Handle our bearer token info
     * @param type $token_info
     * @return boolean
     */
    private function _handle_bearer($token_info = null){
        if(!empty($token_info) && isset($token_info["access_token"])){
                
            //Add to our credentials
            $this->credentials["bearer"] = $token_info["access_token"];

            //Save to file
            $this->_save_credentials($this->credentials);
            return $token_info["access_token"];
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
                ),
                $this->headers
            );
            
            //Save our token info
            return $this->_handle_bearer($token_info);
        }
        return false;
    }
    
    /**
     * Authenticate a user by username and password
     * @param type $username usernam@domainname.com
     * @param type $password The password
     */
    public function authUser($username = null, $password = null){
        if(!empty($username) && stristr($username, "@") !== FALSE && !empty($password)){
            
            //Get the API credentials
            $credentials = $this->_get_credentials();

            if(is_array($credentials) && isset($credentials["client_id"])){

                //Request access token in exchange for our Authorization token
                $http = HttpRequest::Instance("https://{$this->mastodon_api_url}");
                $token_info = $http::Post(
                    "oauth/token",
                    array(
                        "grant_type"    => "password",
                        "client_id"     => $credentials["client_id"],
                        "client_secret" => $credentials["client_secret"],
                        "username"      => $username,
                        "password"      => $password,
                    ),
                    $this->headers
                );
                
                //Save our token info
                return $this->_handle_bearer($token_info);
            }
        }        
        return false;
    }
}