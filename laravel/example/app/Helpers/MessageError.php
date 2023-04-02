<?php
  namespace Helper;
  // if(!function_exists('messageError')) {

  //   function messageError($messages) {

  //     if (is_array($messages)) {
       
  //       $responseError = '';
  //       foreach($messages as $key => $value) {

  //         $responseError = $key.  ": " .$value[0].", ";

  //       }
  //       return response()->json($responseError, 422);
  //     }

  //     throw new Exception("Messeges not array type");
      
  //   }
  // }

  class messageError {
    public static function message($messages) {
      if (is_array($messages)) {
        $res = '';
        foreach($messages as $key => $value) {
          $res .= $value[0];
        }
      }

      return response()->json([
        'status'=> 'error',
        'message' => $res
      ], 422);
    }
  }
?>