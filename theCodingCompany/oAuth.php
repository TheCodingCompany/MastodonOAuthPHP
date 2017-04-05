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
     * Our API to use
     * @var type 
     */
    private $mastodon_api_url = "mastodon.social";
    
    /**
     * Holds our final token
     * @var type 
     */
    private static $bearer_token = "";
    
    /**
     * Holds our client_id and secret
     * @var array 
     */
    public $credentials = array(
        "client_id"     => "",
        "client_secret" => ""
    );
    
    /**
     * Start at getting or creating app
     */
    private function getAppConfig(){
        //Get singleton instance
        $http = HttpRequest::Instance("https://{$this->mastodon_api_url}");
        $config = $http::post(
            "/api/v1/apps", //Endpoint
            array(
                "client_name"   => "MastoTweet",
                "redirect_uris" => "urn:ietf:wg:oauth:2.0:oob",
                "scopes"        => "read write",
                "website"       => "https://www.thecodingcompany.se"
            )
        );
        if(!empty($config) && isset($config["client_id"]) && isset($config["client_secret"])){
            array_merge($this->credentials, $config);
            return $this->credentials;
        }else{
            return false;
        }
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
    
    public function saveCredentials(){
        echo __DIR__;
    }
}