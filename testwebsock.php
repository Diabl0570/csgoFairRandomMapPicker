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
  private $mapPool = array(
	"0" => array("name" => 'mirage',"checked" => true),
	"1" => array("name" => 'dust2',"checked" => true),
	"2" => array("name" => 'nuke',"checked" => true),
	"3" => array("name" => 'cobble',"checked" => true),
	"4" => array("name" => 'overpass',"checked" => true),
	"5" => array("name" => 'inferno',"checked" => true),
	"6" => array("name" => 'train',"checked" => true),
	"7" => array("name" => 'cache',"checked" => true)	
  );
  
  protected function process($user, $message)
    {
		
		if($this->isJson($message))
		{
			$msg = json_decode($message);
                        /**
                         * Process server methods
                         */
			if($msg->type=="server")
			{
                            if($msg->text == "getMapPool")
                            {
                                $this->stdout($message);
				$this->send($user, json_encode(array("type"=>"mapPool", "mapPool"=> $this->mapPool)));
                            }
                            else 
                            {
                                //EERST DE MAP, DAN DE WAARDE
                                $map = explode(":",$msg->text);
                                //$map[0]== map
                                $map[0];
                                foreach($this->mapPool as $i => $m)
                                {
                                    if($m['name']==$map[0])
                                    {
                                       // $m['checked'] = false;//$map[1];
										$this->mapPool[$i]['checked'] = $map[1] == "true";
                                    }
                                }
                                
								if (count($this->userList) > 0) {
                                    foreach ($this->userList as $u) {
                                           $this->send($u, json_encode(array("type"=>"mapPool", "mapPool"=> $this->mapPool)));
                                    }
								}
                            }
				
			}
			else
			{
                            /**
                             * forward message to clients
                             */
                            $this->stdout($message);
                            if (count($this->userList) > 0) {
                                    foreach ($this->userList as $u) {
                                            $this->send($u, $message);
                                    }
                            } else {
                                    $this->send($user, $message);
                            }
			}
		}
		else
		{
			$this->send($user, $message);
		}
    }
protected function isJson($string) {
 json_decode($string);
 return (json_last_error() == JSON_ERROR_NONE);
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
