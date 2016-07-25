<?php
/**
 * Created by PhpStorm.
 * User: mareike
 * Date: 29.04.2015
 * Time: 14:29
 */

class Nl2go_ResponseHelper {

    /**
     * err-number, that should be pulled, whenever credentials are missing
     */
    const ERRNO_PLUGIN_CREDENTIALS_MISSING = 'int-1-404';
    /**
     *err-number, that should be pulled, whenever credentials are wrong
     */
    const ERRNO_PLUGIN_CREDENTIALS_WRONG = 'int-1-403';
    /**
     * err-number for all other (intern) errors. More Details to the failure should be added to error-message
     */
    const ERRNO_PLUGIN_OTHER = 'int-1-600';



    static function generateErrorResponse($message, $errorCode, $context =null ){
        $res =  array(
            'success' => false,
            'message' =>$message,
        'errorcode' => $errorCode
        );
        if($context != null){
            $res['context'] = $context;
        }
        return json_encode($res);
    }

    static function generateSuccessResponse($data= array()){
        $res =  array('success' =>true, 'message' => 'OK');
        $res = array_merge($res, $data);
        $json = json_encode($res);
        if ($json === false){
            if(json_last_error() == JSON_ERROR_UTF8){
                $res = self::utf8ize($res);
                $json = json_encode($res);
                return $json;
            }else{
                return self::generateErrorResponse('problem on json-encoding: '. json_last_error_msg(), self::ERRNO_PLUGIN_OTHER);
            }
        }
       return $json;
    }

    static function utf8ize($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = utf8ize($v);
            }
        } else if (is_string ($d)) {
            return utf8_encode($d);
        }
        return $d;
    }

}