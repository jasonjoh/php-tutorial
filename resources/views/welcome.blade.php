@extends('layout')

@section('content')
<div class="jumbotron">
  <h1>PHP Outlook Sample</h1>
  <p>This example shows how to get an OAuth token from Azure using the <a href="https://docs.microsoft.com/en-us/azure/active-directory/develop/active-directory-v2-protocols-oauth-code" target="_blank">authorization code grant flow</a> and to use that token to make calls to the Outlook APIs in the <a href="https://developer.microsoft.com/en-us/graph/" target="_blank">Microsoft Graph</a>.</p>
  <p>
    <a class="btn btn-lg btn-primary" href="/signin" role="button" id="connect-button">Connect to Outlook</a>
  </p>
</div>
@endsection