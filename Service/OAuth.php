<?php
namespace Microsoft\Service;

class OAuth
{
    private static $clientId = "YOUR APP ID HERE";
    private static $clientSecret = "YOUR APP PASSWORD HERE";
    private static $authority = "https://login.microsoftonline.com";
    private static $authorizeUrl = '/common/oauth2/v2.0/authorize?client_id=%1$s&redirect_uri=%2$s&response_type=code&scope=%3$s';
    private static $tokenUrl = "/common/oauth2/v2.0/token";

    // The scopes the app requires
    private static $scopes = [
        "openid",
        "offline_access",
        "https://outlook.office.com/mail.read",
        "https://outlook.office.com/calendars.read",
        "https://outlook.office.com/contacts.read"
    ];

    public static function getLoginUrl($redirectUri)
    {
        // Build scope string. Multiple scopes are separated
        // by a space
        $scopestr = implode(" ", self::$scopes);

        $loginUrl = self::$authority.sprintf(self::$authorizeUrl, self::$clientId, urlencode($redirectUri), urlencode($scopestr));

        error_log("Generated login URL: ".$loginUrl);
        return $loginUrl;
    }

    public static function getTokenFromAuthCode($authCode, $redirectUri)
    {
        return self::getToken("authorization_code", $authCode, $redirectUri);
    }

    public static function getTokenFromRefreshToken($refreshToken, $redirectUri)
    {
        return self::getToken("refresh_token", $refreshToken, $redirectUri);
    }

    public static function getToken($grantType, $code, $redirectUri)
    {
        $parameter_name = $grantType;
        if (strcmp($parameter_name, 'authorization_code') == 0) {
            $parameter_name = 'code';
        }

        // Build the form data to post to the OAuth2 token endpoint
        $token_request_data = [
            "grant_type" => $grantType,
            $parameter_name => $code,
            "redirect_uri" => $redirectUri,
            "scope" => implode(" ", self::$scopes),
            "client_id" => self::$clientId,
            "client_secret" => self::$clientSecret
        ];

        // Calling http_build_query is important to get the data formatted as expected.
        $token_request_body = http_build_query($token_request_data);
        error_log("Request body: ".$token_request_body);

        $response = file_get_contents(
            self::$authority.self::$tokenUrl,
            false,
            stream_context_create([
                'http'=> [
                    'method' => 'POST',
                    'timeout' => 15.0,
                    'content' => $token_request_body,
                    'ignore_errors' => true,
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
                ]
            ])
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

        # The response is a JSON payload, so decode it into an array.
        $json = json_decode($response, true);
        error_log("TOKEN RESPONSE:");
        foreach ($json as $key => $value) {
            if (!is_array($value)) {
                error_log("  ".$key.": ".$value);
            } else {
                error_log("  ".$key.": ".implode(',', $value));
            }
        }

        if (isset($json['error'])) {
            return [
                'errorNumber' => $json['error_codes'],
                'error' => $json['error_description']
            ];
        }

        return $json;
    }

    public static function getAccessToken($redirectUri)
    {
        // Is there an access token in the session?
        $current_token = $_SESSION['access_token'];
        if (!is_null($current_token)) {
            // Check expiration
            $expiration = $_SESSION['token_expires'];
            if ($expiration < time()) {
                error_log('Token expired! Refreshing...');
                // Token expired, refresh
                $refresh_token = $_SESSION['refresh_token'];
                $new_tokens = self::getTokenFromRefreshToken($refresh_token, $redirectUri);

                // Update the stored tokens and expiration
                $_SESSION['access_token'] = $new_tokens['access_token'];
                $_SESSION['refresh_token'] = $new_tokens['refresh_token'];

                // expires_in is in seconds
                // Get current timestamp (seconds since Unix Epoch) and
                // add expires_in to get expiration time
                // Subtract 5 minutes to allow for clock differences
                $expiration = time() + $new_tokens['expires_in'] - 300;
                $_SESSION['token_expires'] = $expiration;

                // Return new token
                return $new_tokens['access_token'];
            } else {
                // Token is still valid, return it
                return $current_token;
            }
        } else {
            return null;
        }
    }

    public static function loggedIn()
    {
        return (isset($_SESSION['access_token'])) ? !is_null($_SESSION['access_token']) : false;
    }

    public static function getRedirectUri($path = '/authorize.php')
    {
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $path;
    }
}
