<?php
namespace Microsoft;

require_once('bootstrap.php');

?>
<html>
    <head>
        <title>PHP Calendar API Tutorial</title>
    </head>
    <body>
<?php   if (!Service\OAuth::loggedIn()): ?>
        <!-- User not logged in, prompt for login -->
        <p>Please <a href="<?=Service\OAuth::getLoginUrl(Service\OAuth::getRedirectUri())?>">sign in</a> with your Office 365 or Outlook.com account.</p>
<?php   else: ?>
<?php       $events = Service\Outlook::getEvents($_SESSION['access_token'], $_SESSION['user_email']); ?>
        <!-- User is logged in, do something here -->
        <h2>Your events</h2>
        <table>
            <tr>
                <th>Subject</th>
                <th>Start</th>
                <th>End</th>
            </tr>
<?php       foreach($events['value'] as $event): ?>
            <tr>
                <td><?=$event['Subject']; ?></td>
                <td><?=$event['Start']['DateTime']; ?></td>
                <td><?=$event['End']['DateTime']; ?></td>
            </tr>
<?php       endforeach; ?>
        </table>
<?php   endif; ?>
    </body>
</html>
