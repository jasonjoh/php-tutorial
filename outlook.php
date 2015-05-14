<!-- Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license. See full license at the bottom of this file. -->
<?php
  class OutlookService {
    private static $outlookApiUrl = "https://outlook.office365.com/api/v1.0";
    
    public static function getMessages($access_token) {
      $getMessagesUrl = self::$outlookApiUrl."/Me/Messages?"
                        ."\$select=Subject,DateTimeReceived,From"
                        ."&\$orderby=DateTimeReceived"
                        ."&\$top=10";
                        
      return self::makeApiCall($access_token, "GET", $getMessagesUrl);
    }
    
    public static function makeApiCall($access_token, $method, $url, $payload = NULL) {
      // Generate the list of headers to always send.
      $headers = array(
        "User-Agent: php-tutorial/1.0",         // Sending a User-Agent header is a best practice.
        "Authorization: Bearer ".$access_token, // Always need our auth token!
        "Accept: application/json",             // Always accept JSON response.
        "client-request-id: ".self::makeGuid(), // Stamp each new request with a new GUID.
        "return-client-request-id: true"        // Tell the server to include our request-id GUID in the response.
      );
      
      $curl = curl_init($url);
      
      switch(strtoupper($method)) {
        case "GET":
          // Nothing to do, GET is the default and needs no
          // extra headers.
          error_log("Doing GET");
          break;
        case "POST":
          error_log("Doing POST");
          // Add a Content-Type header (IMPORTANT!)
          $headers[] = "Content-Type: application/json";
          curl_setopt($curl, CURLOPT_POST, true);
          curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
          break;
        case "PATCH":
          error_log("Doing PATCH");
          // Add a Content-Type header (IMPORTANT!)
          $headers[] = "Content-Type: application/json";
          curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
          curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
          break;
        case "DELETE":
          error_log("Doing DELETE");
          curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
          break;
        default:
          error_log("INVALID METHOD: ".$method);
          exit;
      }
      
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      $response = curl_exec($curl);
      error_log("curl_exec done.");
      
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      error_log("Request returned status ".$httpCode);
      
      if ($httpCode >= 400) {
        return array('errorNumber' => $httpCode,
                     'error' => 'Request returned HTTP error '.$httpCode);
      }
      
      $curl_errno = curl_errno($curl);
      $curl_err = curl_error($curl);
      
      if ($curl_errno) {
        $msg = $curl_errno.": ".$curl_err;
        error_log("CURL returned an error: ".$msg);
        curl_close($curl);
        return array('errorNumber' => $curl_errno,
                     'error' => $msg);
      }
      else {
        error_log("Response: ".$response);
        curl_close($curl);
        return json_decode($response, true);
      }
    }
    
    // This function generates a random GUID.
    public static function makeGuid(){
        if (function_exists('com_create_guid')) {
          error_log("Using 'com_create_guid'.");
          return strtolower(trim(com_create_guid(), '{}'));
        }
        else {
          error_log("Using custom GUID code.");
          $charid = strtolower(md5(uniqid(rand(), true)));
          $hyphen = chr(45);
          $uuid = substr($charid, 0, 8).$hyphen
                 .substr($charid, 8, 4).$hyphen
                 .substr($charid, 12, 4).$hyphen
                 .substr($charid, 16, 4).$hyphen
                 .substr($charid, 20, 12);
                 
          return $uuid;
        }
    }
  }
?>

<!--
 MIT License: 
 
 Permission is hereby granted, free of charge, to any person obtaining 
 a copy of this software and associated documentation files (the 
 ""Software""), to deal in the Software without restriction, including 
 without limitation the rights to use, copy, modify, merge, publish, 
 distribute, sublicense, and/or sell copies of the Software, and to 
 permit persons to whom the Software is furnished to do so, subject to 
 the following conditions: 
 
 The above copyright notice and this permission notice shall be 
 included in all copies or substantial portions of the Software. 
 
 THE SOFTWARE IS PROVIDED ""AS IS"", WITHOUT WARRANTY OF ANY KIND, 
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF 
 MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND 
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE 
 LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION 
 OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION 
 WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
-->