<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class OutlookController extends Controller
{
  public function mail() 
  {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    $tokenCache = new \App\TokenStore\TokenCache;

    $graph = new Graph();
    $graph->setAccessToken($tokenCache->getAccessToken());

    $user = $graph->createRequest('GET', '/me')
                  ->setReturnType(Model\User::class)
                  ->execute();

    $messageQueryParams = array (
      // Only return Subject, ReceivedDateTime, and From fields
      "\$select" => "subject,receivedDateTime,from",
      // Sort by ReceivedDateTime, newest first
      "\$orderby" => "receivedDateTime DESC",
      // Return at most 10 results
      "\$top" => "10"
    );

    $getMessagesUrl = '/me/mailfolders/inbox/messages?'.http_build_query($messageQueryParams);
    $messages = $graph->createRequest('GET', $getMessagesUrl)
                      ->setReturnType(Model\Message::class)
                      ->execute();

    return view('mail', array(
      'username' => $user->getDisplayName(),
      'messages' => $messages
    ));
  }

  public function calendar() 
  {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    $tokenCache = new \App\TokenStore\TokenCache;

    $graph = new Graph();
    $graph->setAccessToken($tokenCache->getAccessToken());

    $user = $graph->createRequest('GET', '/me')
                  ->setReturnType(Model\User::class)
                  ->execute();

    $eventsQueryParams = array (
      // // Only return Subject, Start, and End fields
      "\$select" => "subject,start,end",
      // Sort by Start, oldest first
      "\$orderby" => "Start/DateTime",
      // Return at most 10 results
      "\$top" => "10"
    );

    $getEventsUrl = '/me/events?'.http_build_query($eventsQueryParams);
    $events = $graph->createRequest('GET', $getEventsUrl)
                    ->setReturnType(Model\Event::class)
                    ->execute();

    return view('calendar', array(
      'username' => $user->getDisplayName(),
      'events' => $events
    ));
  }

  public function contacts() 
  {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    $tokenCache = new \App\TokenStore\TokenCache;

    $graph = new Graph();
    $graph->setAccessToken($tokenCache->getAccessToken());

    $user = $graph->createRequest('GET', '/me')
                  ->setReturnType(Model\User::class)
                  ->execute();

    $contactsQueryParams = array (
      // // Only return givenName, surname, and emailAddresses fields
      "\$select" => "givenName,surname,emailAddresses",
      // Sort by given name
      "\$orderby" => "givenName ASC",
      // Return at most 10 results
      "\$top" => "10"
    );

    $getContactsUrl = '/me/contacts?'.http_build_query($contactsQueryParams);
    $contacts = $graph->createRequest('GET', $getContactsUrl)
                      ->setReturnType(Model\Contact::class)
                      ->execute();

    return view('contacts', array(
      'username' => $user->getDisplayName(),
      'contacts' => $contacts
    ));
  }
}
