<?php
namespace Confident\Http;

class Request
{
    private $body;

    public function __construct()
    {
        $this->loadBody();
    }

    private function loadBody()
    {
        $this->body = new Body();
    }
    
    public static function AcceptLanguage()
    {
        if (!isset(apache_request_headers()['Accept-Language'])) {
            $lang = "Language not sended, lenguaje no enviado";
        } else {
            $lang = apache_request_headers()['Accept-Language'];
            if (is_null($lang) || $lang=='') {
                $lang = "";
            }
        }
        return $lang;
    }

    public function getParameters()
    {
    }

    public function getURLParameters()
    {
    }

    public function getBody()
    {
        return $this->body;
    }
}
