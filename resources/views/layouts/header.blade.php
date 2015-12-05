@section('header')
<nav class="navbar navbar-light bg-faded row">
  <button class="navbar-toggler hidden-sm-up" type="button" data-toggle="collapse" data-target="#exCollapsingNavbar2">
    &#9776;
  </button>
  <div class="collapse navbar-toggleable-xs" id="exCollapsingNavbar2">
    <span class="navbar-brand" href="#">True Analytics</span>
    <ul class="nav navbar-nav">
      <li class="nav-item @if($page == 'job') active @endif">
        <a class="nav-link" href="{{ url('job') }}">Job</a>
      </li>
      <li class="nav-item @if($page == 'workstream') active @endif">
        <a class="nav-link" href="{{ url('workstream') }}">Worstream</a>
      </li>
      <li class="nav-item @if($page == 'revenue') active @endif">
        <a class="nav-link" href="{{ url('revenue') }}">Revenue</a>
      </li>
      <li class="nav-item @if($page == 'social') active @endif">
        <a class="nav-link" href="{{ url('social') }}">Social</a>
      </li>
    </ul>
  </div>
</nav>
@stop