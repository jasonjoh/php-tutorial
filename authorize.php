<?php
namespace Microsoft;

require_once('bootstrap.php');

$auth_code = $_GET['code'];

$tokens = Service\OAuth::getTokenFromAuthCode($auth_code, Service\OAuth::getRedirectUri());

if (isset($tokens['access_token'])) {
    $_SESSION['access_token'] = $tokens['access_token'];
    $_SESSION['refresh_token'] = $tokens['refresh_token'];

    // expires_in is in seconds
    // Get current timestamp (seconds since Unix Epoch) and
    // add expires_in to get expiration time
    // Subtract 5 minutes to allow for clock differences
    $expiration = time() + $tokens['expires_in'] - 300;
    $_SESSION['token_expires'] = $expiration;

    // Get the user's email
    $user = Service\Outlook::getUser($tokens['access_token']);
    $_SESSION['user_email'] = $user['EmailAddress'];

    // Redirect back to index page
    header("Location: /index.php");
} else {
    echo "<p>ERROR: ".$tokens['error']."</p>";
}
