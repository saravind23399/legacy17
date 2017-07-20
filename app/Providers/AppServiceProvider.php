<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Event;
use Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Validator for checking team members have registered
        Validator::extend('teamMembersExist', function($attribute, $value, $parameters, $validator){
            $team_members_emails = explode(',', $value);
            foreach($team_members_emails as $team_member_email){
                if(User::where('email', $team_member_email)->count() == 0){
                    return false;
                }
            }
            return true;
        });
        Validator::replacer('teamMembersExist', function($message, $attribute, $rule, $parameters, $validator){
            $value = array_get($validator->getData(), $attribute);
            $team_members_emails = explode(',', $value);
            $invalid_emails = [];
            foreach($team_members_emails as $team_member_email){
                if(User::where('email', $team_member_email)->count() == 0){
                    array_push($invalid_emails, $team_member_email);
                }
            }
            $invalid_emails = implode(',', $invalid_emails);
            return str_replace(':invalid_emails', $invalid_emails, ':invalid_emails has/have not registered yet');
        });
        // Validator for checking team members are from same college
        Validator::extend('isCollegeMate', function($attribute, $value, $parameters, $validator){
            $team_members_emails = explode(',', $value);
            foreach($team_members_emails as $team_member_email){
                $team_member = User::where('email', $team_member_email)->first();
                if($team_member){
                    $user_college = Auth::user()->college;
                    $team_member_college = $team_member->college;
                    if($team_member_college->id != $user_college->id){
                        return false;
                    }
                }
            }
            return true;
        });
        Validator::replacer('isCollegeMate', function($message, $attribute, $rule, $parameters, $validator){
            $value = array_get($validator->getData(), $attribute);
            $team_members_emails = explode(',', $value);
            $invalid_emails = [];
            foreach($team_members_emails as $team_member_email){
                $team_member = User::where('email', $team_member_email)->first();                
                if($team_member){
                    $user_college = Auth::user()->college;
                    $team_member_college = $team_member->college;
                    if($team_member_college->id != $user_college->id){
                        array_push($invalid_emails, $team_member_email);                        
                    }
                }
            }
            $invalid_emails = implode(',', $invalid_emails);
            return str_replace(':invalid_emails', $invalid_emails, ':invalid_emails is/are not from your college');
        });
        // Validator for checking team members have not confirmed registrations        
        Validator::extend('isNotConfirmed', function($attribute, $value, $parameters, $validator){
            $team_members_emails = explode(',', $value);
            foreach($team_members_emails as $team_member_email){
                $team_member = User::where('email', $team_member_email)->first();
                if($team_member){
                    if($team_member->hasConfirmed()){
                        return false;
                    }
                }
            }
            return true;
        });
        Validator::replacer('isNotConfirmed', function($message, $attribute, $rule, $parameters, $validator){
            $value = array_get($validator->getData(), $attribute);
            $team_members_emails = explode(',', $value);
            $invalid_emails = [];
            foreach($team_members_emails as $team_member_email){
                $team_member = User::where('email', $team_member_email)->first();
                if($team_member){
                    if($team_member->hasConfirmed()){
                        array_push($invalid_emails, $team_member->email);
                    }
                }
            }
            $invalid_emails = implode(',', $invalid_emails);
            return str_replace(':invalid_emails', $invalid_emails, ':invalid_emails has/have already confirmed registration');
        });
        // Validator for checking team members have no parallel events        
        Validator::extend('hasNoParallelEvent', function($attribute, $value, $parameters, $validator){
            $team_members_emails = explode(',', $value);
            foreach($team_members_emails as $team_member_email){
                $team_member = User::where('email', $team_member_email)->first();
                if($team_member){
                    if($this->userHasParallelEvent($team_member->id, $parameters[0])){
                        return false;
                    }
                }
            }
            return true;
        });
        Validator::replacer('hasNoParallelEvent', function($message, $attribute, $rule, $parameters, $validator){
            $value = array_get($validator->getData(), $attribute);
            $team_members_emails = explode(',', $value);
            $invalid_emails = [];
            foreach($team_members_emails as $team_member_email){
                $team_member = User::where('email', $team_member_email)->first();
                if($team_member){
                    if($this->userHasParallelEvent($team_member->id, $parameters[0])){
                        array_push($invalid_emails, $team_member->email);
                    }
                }
            }
            $invalid_emails = implode(',', $invalid_emails);
            return str_replace(':invalid_emails', $invalid_emails, ':invalid_emails has/have registered parallel events');
        });
    }
    
    private function userHasParallelEvent($user_id, $event_id){
        $user = User::find($user_id);
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

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
