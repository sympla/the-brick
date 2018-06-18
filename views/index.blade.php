<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Negotiate documentation">
    <title>Negotiate Documentation</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <style>
        .nounderline {
            text-decoration: none !important
        }
    </style>
</head>

<body>
<div class="container">
    <h1> Documentation </h1>
    <div class="panel panel-default">
        <div class="panel-body">
            @foreach($docArray as $key => $route)
            <div class="panel panel-default" style="margin-bottom: 5px;">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" class="nounderline" href="#collapse{{ $key }}">
                            <b>{{ $route->route }}</b>
                            @foreach($route->methods as $method)
                                @if($method == "POST")
                                    <span class="label label-success pull-right" style="margin-left: 3px;">
                                            {{ $method }}
                                    </span>
                                @elseif($method == "GET")
                                    <span class="label label-primary pull-right" style="margin-left: 3px;">
                                        {{ $method }}
                                    </span>
                                @elseif($method == "PUT")
                                    <span class="label label-warning pull-right" style="margin-left: 3px;">
                                        {{ $method }}
                                    </span>
                                @elseif($method == "DELETE")
                                <span class="label label-warning pull-right" style="margin-left: 3px;">
                                        {{ $method }}
                                    </span>
                                @elseif($method == "DELETE")
                                    <span class="label label-info pull-right" style="margin-left: 3px;">
                                        {{ $method }}
                                    </span>
                                @else
                                    <span class="label label-default pull-right" style="margin-left: 3px;">
                                        {{ $method }}
                                    </span>
                                @endif
                            @endforeach
                            &nbsp;
                        </a>
                    </h4>
                </div>
                <div id="collapse{{ $key }}" class="panel-collapse collapse">
                    <div class="panel-body">
                        {{ !empty($route->description) ? $route->description : 'Without description' }}
                    </div>
                    <div class="panel-body">
                        <p><strong>Filtros: </strong></p>
                        @foreach($route->filters as $filter)
                        <div class="panel panel-default" style="margin-bottom: 5px;">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" class="nounderline" href="#collapsec{{ $uniqid = uniqid() }}">
                                        {{ $filter->name }}
                                    </a>
                                </h4>
                            </div>
                            <div id="collapsec{{ $uniqid }}" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="panel-body">{{ !empty($filter->description) ? $filter->description : 'Without description' }}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    Last update: {{ $lastModified }}
</div> <!-- /container -->
</body>
<footer>
    <script>
        var coll = document.getElementsByClassName("collapsible");
        var i;

        for (i = 0; i < coll.length; i++) {
          coll[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var content = this.nextElementSibling;
            if (content.style.display === "block") {
              content.style.display = "none";
            } else {
              content.style.display = "block";
            }
          });
        }
    </script>
</footer>
</html>