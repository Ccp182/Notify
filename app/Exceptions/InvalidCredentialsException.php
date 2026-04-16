<?php

namespace App\Exceptions;

use Exception;

class InvalidCredentialsException extends Exception
{
    protected $message;
    protected $title;
    protected $link;

    public function __construct($message = "", $code = 0,$link=null,$title=null, Exception $previous = null) {
        $this->message = $message;
        $this->link = $link;
        $this->title = $title;
        parent::__construct($message, $code, $previous);
    }

    public function errorMessage() {
        return $this->message;
    }

    public function getLink(){
        return $this->link;
    }
    public function getTitle(){
        return $this->title;
    }
}
