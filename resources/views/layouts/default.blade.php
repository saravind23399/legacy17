<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Legacy17</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {{ HTML::Style("css/materialize.min.css") }}
        {{ HTML::Style("css/font-awesome.min.css") }}  
        {{ HTML::Style("css/app.css") }}            
        {{ HTML::Style("css/materialize-stepper.min.css") }}                                                     
        {{ HTML::Script("js/jquery.min.js") }}        
        {{ HTML::Script("js/materialize.min.js") }} 
        {{ HTML::Script("js/materialize-stepper.min.js") }} 
        {{ HTML::Script("js/particles.min.js") }}         
        {{ HTML::Script("js/app.js") }}         
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">            
    </head>
    <body>
        @include('layouts.partials.default_nav')    
        <div class="container">
            <div class="row">
                <div class="col s12">
                    @include('layouts.partials.flash')
                </div>
            </div>
            @yield('content')
        </div>
        <div class="footer">    
            <span class="white-text">
                &copy; MEPCO 2017
            </span>
            <span class="right white-text">
                Designed with <i class="fa pulsing fa-heart"></i> by Ameer Jhan
            </span>
        </div>
    </body>
    <script>
        $('.stepper').activateStepper();
    </script>
</html>