<?php
namespace Microsoft;

require_once('bootstrap.php');

?>
<html>
	<head>
		<title>PHP Mail API Tutorial</title>
	</head>
    <body>
<?php   if (!Service\OAuth::loggedIn()): ?>
        <!-- User not logged in, prompt for login -->
        <p>Please <a href="<?=Service\OAuth::getLoginUrl(Service\OAuth::getRedirectUri())?>">sign in</a> with your Office 365 or Outlook.com account.</p>
<?php   else: ?>
<?php      $messages = Service\Outlook::getMessages(Service\OAuth::getAccessToken(Service\OAuth::getRedirectUri()), $_SESSION['user_email']); ?>
        <!-- User is logged in, do something here -->
        <h2>Your messages</h2>
        <table>
            <tr>
                <th>From</th>
                <th>Subject</th>
                <th>Received</th>
            </tr>
<?php      foreach($messages['value'] as $message): ?>
            <tr>
                <td><?=$message['From']['EmailAddress']['Name']; ?></td>
                <td><?=$message['Subject']; ?></td>
                <td><?=$message['ReceivedDateTime']; ?></td>
            </tr>
<?php      endforeach; ?>
      </table>
<?php  endif; ?>
    </body>
</html>
