@extends('layout')

@section('content')
<div id="inbox" class="panel panel-default">
  <div class="panel-heading">
    <h1 class="panel-title">Inbox</h1>
  </div>
  <div class="panel-body">
    Here are the 10 most recent messages in your inbox.
  </div>
  <div class="list-group">
    <?php if (isset($messages)) {
      foreach($messages as $message) { ?>
    <div class="list-group-item">
      <h3 class="list-group-item-heading"><?php echo $message->getSubject() ?></h3>
      <h4 class="list-group-item-heading"><?php echo $message->getFrom()->getEmailAddress()->getName() ?></h4>
      <p class="list-group-item-heading text-muted"><em>Received: <?php echo $message->getReceivedDateTime()->format(DATE_RFC2822) ?></em></p>
    </div>
    <?php  }
    } ?>
  </div>
</div>
@endsection