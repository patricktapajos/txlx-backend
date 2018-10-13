<?php

class helpers {

  static function jsonResponse($response, $data) {

    $return = array('success'=>0);
    
    if($data != null){
    	$return = $data;
    	$return['success'] = 1;
    }
    return $response->withJson( $return );
  }
}