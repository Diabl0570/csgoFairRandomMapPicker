#!/usr/bin/env php
<?php

require_once('websockets.php');

class echoServer extends WebSocketServer {
  //protected $maxBufferSize = 1048576; //1MB... overkill for an echo server, but potentially plausible for other applications.
  private $userList = array();
  
  private $commandos = array(
        "!de_dust" => "Dust II",
        "!de_cble" => "Cobblestone",
        "!de_mira" => "Mirage",
    );
  
  protected function process($user, $message)
    {
        $this->stdout($message);
        if (substr($message, 0, 1) === "!") {

            if(array_key_exists($message, $this->commandos))
            {
                $this->commandoex = "Select: ".$this->commandos[$message];
            }
            else{
                $this->commandoex = "Commando not recognized";
            }
            if (count($this->userList) > 0) {
                foreach ($this->userList as $u) {
                    $this->send($u, "!".$user->id.", ".$this->commandoex);
                }
            } else {
                $this->send($u, "!".$user->id.", Select: ".$this->commandoex);
            }
        }
        else {
            if (count($this->userList) > 0) {
                foreach ($this->userList as $u) {
                    $this->send($u, "User" . $user->id . " says: " . $message);
                }
            } else {
                $this->send($user, "User" . $user->id . " says: " . $message);
            }
        }
    }

  
  protected function connected ($user) {
    // Do nothing: This is just an echo server, there's no need to track the user.
    // However, if we did care about the users, we would probably have a cookie to
    // parse at this step, would be looking them up in permanent storage, etc.
	$this->userList[]=$user;
  }
  
  protected function closed ($user) {
    // Do nothing: This is where cleanup would go, in case the user had any sort of
    // open files or other objects associated with them.  This runs after the socket 
    // has been closed, so there is no need to clean up the socket itself here.
  }
}

$echo = new echoServer("0.0.0.0","64000");

try {
  $echo->run();
}
catch (Exception $e) {
  $echo->stdout($e->getMessage());
}