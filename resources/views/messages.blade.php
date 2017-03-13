@if ($message = Session::get('success'))
    <div class="ui success visible message">
        {!! $message !!}
    </div>
@endif
@if ($message = Session::get('warning'))
    <div class="ui warning visible message">
        {!! $message !!}
    </div>
@endif
@if ($message = Session::get('error'))
    <div class="ui error visible message">
        {!! $message !!}
    </div>
@endif