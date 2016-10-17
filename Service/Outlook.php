<?php
namespace Microsoft\Service;

class Outlook {
    private static $outlookApiUrl = "https://outlook.office.com/api/v2.0";

    public static function getUser($access_token)
    {
        $getUserParameters = [
            // Only return the user's display name and email address
            "\$select" => "DisplayName,EmailAddress"
        ];

        $getUserUrl = self::$outlookApiUrl."/Me?".http_build_query($getUserParameters);

        return self::makeApiCall($access_token, "", "GET", $getUserUrl);
    }

    public static function getMessages($access_token, $user_email)
    {
        $getMessagesParameters = [
            // Only return Subject, ReceivedDateTime, and From fields
            "\$select" => "Subject,ReceivedDateTime,From",
            // Sort by ReceivedDateTime, newest first
            "\$orderby" => "ReceivedDateTime DESC",
            // Return at most 10 results
            "\$top" => "10"
        ];

        $getMessagesUrl = self::$outlookApiUrl."/Me/MailFolders/Inbox/Messages?".http_build_query($getMessagesParameters);

        return self::makeApiCall($access_token, $user_email, "GET", $getMessagesUrl);
    }
    
    public static function getEvents($access_token, $user_email)
    {
        $getEventsParameters = [
            // Only return Subject, Start, and End fields
            "\$select" => "Subject,Start,End",
            // Sort by Start, oldest first
            "\$orderby" => "Start/DateTime",
            // Return at most 10 results
            "\$top" => "10"
        ];

        $getEventsUrl = self::$outlookApiUrl."/Me/Events?".http_build_query($getEventsParameters);

        return self::makeApiCall($access_token, $user_email, "GET", $getEventsUrl);
    }
    
    public static function getContacts($access_token, $user_email)
    {
        $getContactsParameters = [
            // Only return GivenName, Surname, and EmailAddresses fields
            "\$select" => "GivenName,Surname,EmailAddresses",
            // Sort by GivenName, A-Z
            "\$orderby" => "GivenName",
            // Return at most 10 results
            "\$top" => "10"
        ];

        $getContactsUrl = self::$outlookApiUrl."/Me/Contacts?".http_build_query($getContactsParameters);

        return self::makeApiCall($access_token, $user_email, "GET", $getContactsUrl);
    }
    
    public static function makeApiCall($access_token, $user_email, $method, $url, $payload = NULL)
    {
        # Normalize $method.
        $method = strtoupper($method);

        # Generate the list of headers to always send.
        $headers = [
            "User-Agent: php-tutorial/1.0",         // Sending a User-Agent header is a best practice.
            "Authorization: Bearer ".$access_token, // Always need our auth token!
            "Accept: application/json",             // Always accept JSON response.
            "client-request-id: ".self::makeGuid(), // Stamp each new request with a new GUID.
            "return-client-request-id: true",       // Tell the server to include our request-id GUID in the response.
            "X-AnchorMailbox: ".$user_email         // Provider user's email to optimize routing of API call
        ];

        # And the context to send the data in.
        $context = [
            'ignore_errors' => true,
            'method' => $method,
        ];

        switch($method) {
            case "GET":
                # Nothing to do, GET is the default and needs no extra headers.
                break;
            case "POST":
            case "PATCH":
                $headers[] = "Content-Type: application/json";
            case "DELETE":
                $context['content'] = $payload;
                break;
            default:
                error_log("INVALID METHOD: ".$method);
                exit;
        }
        $context['header'] = implode("\r\n", $headers);

        $response = file_get_contents(
            $url,
            false,
            stream_context_create(['http' => $context])
        );
        error_log("file_get_contents done.");

        # http_response_header is a predefined variable created after the
        # use of an HTTP wrapper such as file_get_contents above.
        $httpCode = (function() use ($http_response_header) {
            foreach ($http_response_header as $header) {
                preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $header, $httpCode);
                return $httpCode[0];
            }
        })();
        error_log("Request returned status ".$httpCode);

        if ($httpCode >= 400) {
            return [
                'errorNumber' => $httpCode,
                'error' => 'Token request returned HTTP error '.$httpCode
            ];
        }

        return json_decode($response, true);
    }

    // This function generates a random GUID.
    public static function makeGuid()
    {
        if (function_exists('com_create_guid')) {
            error_log("Using 'com_create_guid'.");
            return strtolower(trim(com_create_guid(), '{}'));
        } elseif (function_exists('random_bytes')) {
            error_log("Using 'random_bytes'.");
            return implode(chr(45), unpack('h8a/h4b/h4c/h4d/h12e', random_bytes(32)));
        } else {
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
