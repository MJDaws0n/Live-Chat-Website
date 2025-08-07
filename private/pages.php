<?php
namespace Net\MJDawson\LiveChat;
use Exception;

class Pages{
    public function get($name){
        if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.$name.'.html')){
            return file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.$name.'.html');
        } else{
            throw new Exception($name.' page not found at '.dirname(__FILE__).DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.$name.'.html');
        }
    }
}