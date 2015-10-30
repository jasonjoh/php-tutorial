<?php
  session_start();
  require('oauth.php');
  require('outlook.php');
  
  $loggedIn = !is_null($_SESSION['access_token']);
  $redirectUri = 'http://localhost/php-tutorial/authorize.php';
?>
<html>
  <head>
    <title>PHP Contacts API Tutorial</title>
  </head>
  <body>
    <?php 
      if (!$loggedIn) {
    ?>
      <!-- User not logged in, prompt for login -->
      <p>Please <a href="<?php echo oAuthService::getLoginUrl($redirectUri)?>">sign in</a> with your Office 365 or Outlook.com account.</p>
    <?php
      }
      else {
        $contacts = OutlookService::getContacts($_SESSION['access_token'], $_SESSION['user_email']);
    ?>
      <!-- User is logged in, do something here -->
      <h2>Your contacts</h2>
      
      <table>
        <tr>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Email Address</th>
        </tr>
        
        <?php foreach($contacts['value'] as $contact) { ?>
          <tr>
            <td><?php echo $contact['GivenName'] ?></td>
            <td><?php echo $contact['Surname'] ?></td>
            <td><?php echo $contact['EmailAddresses'][0]['Address'] ?></td>
          </tr>
        <?php } ?>
      </table>
    <?php    
      }
    ?>
  </body>
</html>