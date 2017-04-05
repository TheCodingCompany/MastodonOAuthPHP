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

use \theCodingCompany\HttpRequest;

/**
 * Mastodon main class
 */
class Mastodon
{
    //Mastodon oAuth
    use \theCodingCompany\oAuth;
    
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
     * Create an App and get client_id and client_secret
     * @param type $name
     * @param type $website_url
     */
    public function createApp($name, $website_url){
        if(!empty($name) && !empty($website_url)){
            
            //Set our info
            $this->app_config["client_name"] = $name;
            $this->app_config["website"]     = $website_url;
            
            return $this->getAppConfig();
        }
        return false;
    }
    
    /**
     * Get mastodon user
     * @param type $username
     * @param type $password
     */
    public function getUser($username, $password){
        //Authenticate the user
        $this->authUser($username, $password);        
        
        //Create our object
        $http = HttpRequest::Instance($this->getApiURL());
        $user_info = $http::Get(
            "api/v1/accounts/verify_credentials",
            null,
            $this->getHeaders()
        );
        if(is_array($user_info) && isset($user_info["username"])){
            return $user_info;
        }
        return false;
    }
    
    
    
}