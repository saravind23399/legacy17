<?php

namespace App\Http\Middleware;

use Closure;
use App\Event;
use App\Team;
use Session;
use Auth;

class CheckRegistration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $type, $registered)
    {
        // Check if user has confirmed his registrations
        if(Auth::user()->hasConfirmed()){
            Session::flash('success', 'Sorry! you have already confirmed your events');         
            return redirect()->route('pages.dashboard');                                            
        }
        $event_id = $request->route('event_id');
        $event = Event::find($event_id);
        // Check if team exists
        if($type == 'team' && $registered == 'yes'){
            $team_id = $request->route('id');
            $team = Team::find($team_id);
            if(!$team){
                return redirect()->route('pages.events');                                                       
            }
        }
        // Check if event exists        
        if($event){
            $user = Auth::user();                    
            // Check if the event being registered is single and the actual event type is single
            if($type == 'single' && $event->isGroupEvent()){
                Session::flash('success', 'Sorry! its not a single participating event');
                return redirect()->route('pages.events');                
            }
            else if($type == 'team' && !$event->isGroupEvent()){
                Session::flash('success', 'Sorry! its not a team participating event');     
                return redirect()->route('pages.events');                                                           
            }
            if($registered == 'no'){
                if(!$user->hasRegisteredEvent($event->id)){
                    if($registered_event = $this->userHasParallelEvent($event->id)){
                        if($request->ajax()){
                            $response['error'] = true;
                            $response['message'] = "Sorry! you have registered a parallel event $registered_event->title";
                            return response()->json($response); 
                        }
                        else{
                             Session::flash('success', "Sorry! you have registered a parallel event $registered_event->title");
                             return redirect()->route('pages.events');  
                        }
                    }
                    else{
                        return $next($request);                        
                    }
                }
                else{
                    Session::flash('success', 'Sorry! you have already registered for this event');                
                }
            }
            else if($registered == 'yes'){
                if($user->hasRegisteredEvent($event->id)){
                    if($event->isGroupEvent()){
                        if($user->isTeamLeader($event->id)){
                            return $next($request);
                        }
                        else{
                            Session::flash('success', 'Sorry! only team leaders can unregister teams');
                            return redirect()->route('pages.dashboard');   
                        }                   
                    }
                    else{
                        return $next($request);                                           
                    }
                }
                else{
                    Session::flash('success', 'Sorry! you have not registered for this event');                
                    return redirect()->route('pages.dashboard'); 
                }
            }
            return redirect()->route('pages.events');                                            
        }
        else{
            return redirect()->route('pages.events');                                
        }
    }
    private function userHasParallelEvent($event_id){
        $user = Auth::user();
        $event = Event::find($event_id);
        $registered_events = collect($user->events);
        $registered_events->merge($user->teamEvents());
        foreach($registered_events as $registered_event){
            // Date of the event to be registered
            $event_date = date_create($event->event_date);
            // Date of the registered event
            $registered_event_date = date_create($registered_event->event_date);
            // Start and end time of event being registered
            $start_time = strtotime($event->start_time);
            $end_time = strtotime($event->end_time);       
            //  Start and end time of event already registered
            $registered_start_time = strtotime($registered_event->start_time);
            $registered_end_time = strtotime($registered_event->end_time);                
            // Check whether they occur in parallel
            if($event_date == $registered_event_date){
                if(($registered_start_time <= $start_time && $start_time < $registered_end_time) || ($end_time > $registered_start_time && $end_time <= $registered_end_time)){
                    return $registered_event;                 
                }                    
            }
        }
        return false;
    }
}
