<section class="user-dashboard  padding-bottom">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="dashboard-sidebar">
                    <div class="close-dashboard d-lg-none">
                        <i class="las la-times"></i>
                    </div>
                    <div class="dashboard-user">
                        <div class="user-thumb">
                            <img  id="output" src="{{ getImage('assets/images/user/profile/'. auth()->user()->image, '350x300',true)}}" alt="dashboard">
                        </div>
                        <div class="user-content">
                            <span>@lang('Welcome')</span>
                            <h5 class="name">{{ auth()->user()->fullname }}</h5>
                        </div>
                    </div>
                    <ul class="user-dashboard-tab">
                        <li>
                            <a class="{{menuActive('user.home')}}" href="{{route('user.home')}}">@lang('Food Distribution')</a>
                        </li>
                        <li>
                            <a class="{{menuActive('user.land')}}" href="{{route('user.land')}}">@lang('Food Production')</a>
                        </li>
                        
                       
                        <li>
                            <a href="{{ route('user.logout') }}" class="">@lang('Sign Out')</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">


                @yield('content')
            </div>
        </div>
    </div>
</section>