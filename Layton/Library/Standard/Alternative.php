<?php
namespace Layton\Library\Standard;

class Alternative
{
    /**
     * In order to be compatible with Apache Functions getAllHeaders().
     * 
     * {@link http://www.php.net/manual/en/function.getallheaders.php}
     * 
     * @return array
     */
    public function getAllHeaders()
    {
        $headers = []; 
        foreach ($_SERVER as $name => $value)  { 
            if (substr($name, 0, 5) == 'HTTP_') { 
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
            } 
        } 
        return $headers;
    }
}