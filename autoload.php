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
define('CLASS_DIR', __DIR__);
set_include_path(get_include_path().PATH_SEPARATOR.CLASS_DIR);
    
spl_autoload_register(function($name){
    //For namespaces we replace \ with / to correct the Path
    $filename = str_replace("\\", "/", $name);
    require_once "{$filename}.php";    
});