@extends('layout')

@section('content')
<div id="inbox" class="panel panel-default">
  <div class="panel-heading">
    <h1 class="panel-title">Contacts</h1>
  </div>
  <div class="panel-body">
    Here are your first 10 contacts.
  </div>
  <div class="list-group">
    <?php if (isset($contacts)) {
      foreach($contacts as $contact) { ?>
    <div class="list-group-item">
      <h3 class="list-group-item-heading"><?php echo $contact->getGivenName().' '.$contact->getSurname() ?></h3>
      <p class="list-group-item-heading"><?php echo $contact->getEmailAddresses()[0]['address']?></p>
    </div>
    <?php  }
    } ?>
  </div>
</div>
@endsection