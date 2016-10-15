<?php
namespace Microsoft;

require_once('bootstrap.php');

?>
<html>
    <head>
        <title>PHP Contacts API Tutorial</title>
    </head>
    <body>
<?php   if (!Service\OAuth::loggedIn()): ?>
        <!-- User not logged in, prompt for login -->
        <p>Please <a href="<?=Service\OAuth::getLoginUrl(Service\OAuth::getRedirectUri())?>">sign in</a> with your Office 365 or Outlook.com account.</p>
<?php   else: ?>
<?php      $contacts = Service\Outlook::getContacts($_SESSION['access_token'], $_SESSION['user_email']); ?>
        <!-- User is logged in, do something here -->
        <h2>Your contacts</h2>
        <table>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email Address</th>
            </tr>
<?php       foreach($contacts['value'] as $contact): ?>
            <tr>
                <td><?=$contact['GivenName']?></td>
                <td><?=$contact['Surname']?></td>
                <td><?=$contact['EmailAddresses'][0]['Address']?></td>
            </tr>
<?php       endforeach; ?>
        </table>
<?php   endif; ?>
    </body>
</html>
