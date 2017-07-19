<div class="card hoverable">
     <div class="progress hide" id="event-{{ $event->id }}-progress">
        <div class="indeterminate"></div>
    </div>
    <div class="card-image waves-effect waves-light waves-block">
        <img src="{{ url($event->getImageUrl()) }}" alt="{{ $event->title }} image" class="activator">
    </div>
    <div class="card-content">
        <span class="card-title activator">
            {{ $event->title }}
            <i class="material-icons right activator">more_vert</i>            
        </span>
        <div class="event-details">
            <p><i class="fa fa-calendar"></i> {{ $event->getDate() }}</p>
            <p><i class="fa fa-clock-o"></i> {{ $event->getStartTime() }} to {{ $event->getEndTime() }}</p>
            <p><i class="fa fa-child"> 2 Registration / 5 Slots</i></p>
            <p>
                @if(Auth::check() && Auth::user()->type == 'student')
                    @if(Auth::user()->hasRegisteredEvent($event->id))
                        @if($event->isGroupEvent())
                            <ul class="collapsible" data-collapsible="accordion">
                                <li>
                                    <div class="collapsible-header">
                                        <strong>{{ Auth::user()->teamForEvent($event->id)->name }} Details</strong>
                                    </div>
                                    <div class="collapsible-body">
                                        <ul class="collection">
                                            @foreach(Auth::user()->teamForEvent($event->id)->teamMembers as $teamMember)
                                                <li class="collection-item">
                                                    {{ $teamMember->user->full_name }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                            {{ link_to_route('pages.unregisterteam', 'Remove', ['event_id' => $event->id, 'id' => Auth::user()->teamForEvent($event->id)->id], ['class' => 'btn red btn-waves-effect waves-light']) }}
                        @else
                            {{ link_to_route('pages.unregister', 'Remove', ['event_id' => $event->id], ['class' => 'btn red btn-waves-effect waves-light']) }} 
                        @endif 
                    @endif
                @endif
            </p>
        </div>
        @if(Auth::check() && Auth::user()->type == 'admin')
            <a href="{{ route('admin::events.edit', ['id' => $event->id]) }}" class="btn blue waves-effect waves-light">Edit</a>
            {!! Form::open(['url' => route('admin::events.destroy', ['id' => $event->id]), 'method' => 'delete', 'style' => 'display:inline']) !!}
                {!! Form::submit('Delete', ['class' => 'btn red waves-effect waves-light btn-delete-event']) !!}
            {!! Form::close() !!}
        @endif
    </div>
    <div class="card-reveal">   
        <span class="card-title">
            <i class="material-icons right">close</i>                    
            {{ $event->title }} Rules
        </span>
        <ul class="browser-default">
            @foreach($event->getRulesList() as $rule)
                <li>{!! $rule !!}</li>
            @endforeach  
        </ul>      
        @if(Auth::check())
            @if(Auth::user()->type == 'student')
                @if(!Auth::user()->hasRegisteredEvent($event->id))
                    @if($event->max_members == 1)
                        {{ link_to('#', 'Register', ['class' => 'btn waves-effect waves-light green btn-register-event', 'data-event' => $event->id]) }}
                    @else
                        {{ link_to_route('pages.registerteam', 'Register Team', ['event_id' => $event->id], ['class' => 'btn waves-effect waves-light green btn-registerteam-event', 'data-event' => $event->id]) }}
                    @endif
                @endif
            @endif
        @else
            {{ link_to_route('auth.login', 'Login to Register', null,  ['class' => 'btn waves-effect waves-light red']) }}
        @endif
    </div>
</div>