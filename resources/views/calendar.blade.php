@extends('layout')

@section('content')
<div id="inbox" class="panel panel-default">
  <div class="panel-heading">
    <h1 class="panel-title">Calendar</h1>
  </div>
  <div class="panel-body">
    Here are the 10 oldest events in your calendar.
  </div>
  <div class="list-group">
    <?php if (isset($events)) {
      foreach($events as $event) { ?>
    <div class="list-group-item">
      <h3 class="list-group-item-heading"><?php echo $event->getSubject() ?></h3>
      <p class="list-group-item-heading text-muted">Start: <?php echo (new DateTime($event->getStart()->getDateTime()))->format(DATE_RFC2822) ?></p>
      <p class="list-group-item-heading text-muted">End: <?php echo (new DateTime($event->getEnd()->getDateTime()))->format(DATE_RFC822) ?></p>
    </div>
    <?php  }
    } ?>
  </div>
</div>
@endsection