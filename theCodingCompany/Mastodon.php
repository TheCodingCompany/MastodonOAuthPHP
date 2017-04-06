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
     * Holds our current user_id for :id in API calls
     * @var type 
     */
    private $mastodon_user_id = null;
    
    /**
     * Holds our current userinfo
     * @var type 
     */
    private $mastodon_userinfo = null;
    
    /**
     * Construct new Mastodon class
     */
    public function __construct($domainname = "mastodon.social") {        
        
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
     * Authenticate the user
     * @param type $username
     * @param type $password
     */
    public function authenticate($username = null, $password = null) {
        $this->authUser($username, $password);
        
        //Set current working userid
        $this->mastodon_userinfo = $this->getUser();
        
        return $this; //For method chaining
    }
    
    /**
     * Get mastodon user
     * @param type $username
     * @param type $password
     */
    public function getUser(){        
        if(empty($this->mastodon_userinfo)){
            //Create our object
            $http = HttpRequest::Instance($this->getApiURL());
            $user_info = $http::Get(
                "api/v1/accounts/verify_credentials",
                null,
                $this->getHeaders()
            );
            if(is_array($user_info) && isset($user_info["username"])){
                $this->mastodon_user_id = (int)$user_info["id"];
                return $user_info;
            }
        }
        return $this->mastodon_userinfo;
    }
    
    /**
     * Get current user's followers
     */
    public function getFollowers(){
        if($this->mastodon_user_id > 0){
            
            //Create our object
            $http = HttpRequest::Instance($this->getApiURL());
            $accounts = $http::Get(
                "api/v1/accounts/{$this->mastodon_user_id}/followers",
                null,
                $this->getHeaders()
            );
            if(is_array($accounts) && count($accounts) > 0){
                return $accounts;
            }
            
        }
        return false;
    }
    
    /**
     * Get current user's following
     */
    public function getFollowing(){
        if($this->mastodon_user_id > 0){
            
            //Create our object
            $http = HttpRequest::Instance($this->getApiURL());
            $accounts = $http::Get(
                "api/v1/accounts/{$this->mastodon_user_id}/following",
                null,
                $this->getHeaders()
            );
            if(is_array($accounts) && count($accounts) > 0){
                return $accounts;
            }
            
        }
        return false;
    }
    
    /**
     * Get current user's statuses
     */
    public function getStatuses(){
        if($this->mastodon_user_id > 0){
            
            //Create our object
            $http = HttpRequest::Instance($this->getApiURL());
            $statusses = $http::Get(
                "api/v1/accounts/{$this->mastodon_user_id}/statuses",
                null,
                $this->getHeaders()
            );
            if(is_array($statusses) && count($statusses) > 0){
                return $statusses;
            }
            
        }
        return false;
    }
    
}