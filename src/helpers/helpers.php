<?php

class helpers {

  static function jsonResponse($response, $data) {

    $return = array('success'=>0);
    
    if($data != null){
    	$return = $data;
    	$return['success'] = 1;
    }
    return $response->withJson( $return, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  }
}