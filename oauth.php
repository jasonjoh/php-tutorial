<?php

namespace Microsoft;

class oAuthService
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

        // Calling http_build_query is important to get the data
        // formatted as expected.
        $token_request_body = http_build_query($token_request_data);
        error_log("Request body: ".$token_request_body);

        $curl = curl_init(self::$authority.self::$tokenUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $token_request_body);

        $response = curl_exec($curl);
        error_log("curl_exec done.");

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        error_log("Request returned status ".$httpCode);
        if ($httpCode >= 400) {
            return ['errorNumber' => $httpCode, 'error' => 'Token request returned HTTP error '.$httpCode];
        }

        // Check error
        $curl_errno = curl_errno($curl);
        $curl_err = curl_error($curl);
        if ($curl_errno) {
            $msg = $curl_errno.": ".$curl_err;
            error_log("CURL returned an error: ".$msg);
            return ['errorNumber' => $curl_errno, 'error' => $msg];
        }

        curl_close($curl);

        // The response is a JSON payload, so decode it into
        // an array.
        $json_vals = json_decode($response, true);
        error_log("TOKEN RESPONSE:");
        foreach ($json_vals as $key=>$value) {
            error_log("  ".$key.": ".$value);
        }

        return $json_vals;
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
