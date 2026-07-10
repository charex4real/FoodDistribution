<section class="user-dashboard padding-bottom">
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
                    
                       @if(getUserDetails(auth()->id())->section == 1)
                        <li>
                            <a class="{{menuActive('user.home')}}" href="{{route('user.home')}}">
                            <svg class="bi pe-none me-2" width="20" height="20"><use xlink:href="#home"/></svg>
                               <strong> @lang('Food Distribution')</strong>
                            </a>
                        </li>
                        @endif

                        <li>
                            <a class="{{menuActive('user.land')}}" href="{{route('user.land')}}">
                                <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
                            @lang('Dasboard')
                        </a>
                        </li>
                        <li>
                            <a class="{{menuActive('user.my.ref1')}}" href="{{ route('user.my.ref1') }}"> 
                               <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#people-circle"/></svg>
                            @lang('My Referrals')</a>
                        </li>
                       
                    </ul>
                    <ul class="user-dashboard-tab">
                            <li>
                                <a class="collapsed" data-bs-toggle="collapse" data-bs-target="#account-collapse2" aria-expanded="false">
                                    <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#table"/></svg>

                                          <strong>Investment</strong>
                                </a>
                                <div class="collapse user-dashboard-tab" id="account-collapse2">
                                <ul class="user-dashboard-tab btn-toggle-nav">
                                    <li>
                                        <a class="{{menuActive('user.plan.reservation')}}" href="{{ route('user.plan.reservation') }}"> 
                                            <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#grid"/></svg>
                                        @lang('My Reservation')</a>
                                    </li>
                                    <li>
                                      

                                        <a class="{{menuActive('user.plan.investment')}}" href="{{route('user.plan.investment')}}">

                                        <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 212.1 209.7" style="enable-background:new 0 0 212.1 209.7" xml:space="preserve"><style>.st6{fill:#000}.st11{fill:none;stroke:#000;stroke-width:6.8376}</style><g id="Layer_4"><path class="st6" d="M39.9 209.7v-23.4H2.4v23.4"/><path style="fill:#000" d="M96.2 209.7v-51.6H58.7v51.6"/><path class="st6" d="M152.4 209.7V30.4h-37.5v179.3"/><path style="fill:#000" d="M208.7 209.7V78.4h-37.5v131.3"/><path class="st11" d="m2.4 172.2 56.3-56.3 18.7 18.8L208.7 3.4"/><path class="st11" d="M208.7 40.9V3.4h-37.5"/></g></svg> 
                                        Current Offering
                                    </a>
                                    </li>

                                    <li> 
                                        

                                        <a class="{{menuActive('user.plan.index')}}" href="{{route('user.plan.index')}}">

                                        <svg class="bi pe-none me-2" width="20" height="20"><use xlink:href="#speedometer2"/></svg>
                                         @lang('Farm Projects') </a>
                                    </li>

                                    
                                  </ul>
                                </div>
                            </li>
                        </ul>
                    <ul class="user-dashboard-tab">
                        <li>
                            <a class="collapsed" data-bs-toggle="collapse" data-bs-target="#account-collapse" aria-expanded="false">
                                <strong>
                                 <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#grid"/></svg>
                                    Shop
                                </strong>
                            </a>
                            <div class="collapse user-dashboard-tab" id="account-collapse">
                            <ul class="user-dashboard-tab btn-toggle-nav">
                                
                               <li>
                                    <a class="
                                    @if(Route::is('products1') )
                                    {{menuActive('products1')}}
                                    @else
                                    {{menuActive('product1*')}}
                                    @endif
                                    " href="{{ route('products1') }}">@lang('Product')</a>
                                </li>
                                
                                <li>
                                    <a href="{{ route('user.orders1') }}" class="{{menuActive('user.orders1')}}">
                                        @lang('Orders')
                                    </a>
                                </li>
                                
                                
                            </ul>
                            </div>
                        </li>
                    </ul>
                    <ul class="user-dashboard-tab">
                        <li>
                            <a class="collapsed" data-bs-toggle="collapse" data-bs-target="#account-collapse2" aria-expanded="false">
                                <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#table"/></svg>

                                      <strong>Finance</strong>
                            </a>
                            <div class="collapse user-dashboard-tab" id="account-collapse2">
                            <ul class="user-dashboard-tab btn-toggle-nav">
                                <li>
                                    <a href="{{ route('user.transactions1') }}" class="{{menuActive('user.transactions1')}}">
                                        @lang('Transactions ')
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('user.withdraw1.history1') }}" class="{{menuActive('user.withdraw1*')}}">
                                        @lang('Withdraws ')
                                    </a>
                                </li>
                                
                                <li>
                                    <a href="{{ route('user.deposit.index1') }}" class="{{menuActive(['user.deposit.index1'])}}">
                                        @lang('Deposits ')
                                    </a>
                                </li>
                                
                                <li>
                                    <a href="{{ route('user.deposit.history1') }}" class="{{menuActive(['user.deposit*'])}}">
                                        @lang('Deposits history ')
                                    </a>
                                </li>
                               

                                
                              </ul>
                            </div>
                        </li>
                    </ul>


                    <ul class="user-dashboard-tab">
                        <li>
                            <a class="collapsed" data-bs-toggle="collapse" data-bs-target="#account-collapse3" aria-expanded="false">
                                    <strong><svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#people-circle"/></svg>
                                    Settings</strong>  
                            </a>
                            <div class="collapse user-dashboard-tab" id="account-collapse3">
                            <ul class="user-dashboard-tab btn-toggle-nav">
                                
                                <li>
                                    <a class="{{menuActive('user.profile.setting1')}}" href="{{route('user.profile.setting1')}}" class="">@lang('Profile Setting')</a>
                                </li>
                                <li>
                                    <a href="{{ route('user.twofactor1') }}" class="{{menuActive('user.twofactor1')}}">
                                        @lang('2FA Security')
                                    </a>
                                </li>
                               
                                <li>
                                    <a class="{{menuActive('user.change.password1')}}" href="{{route('user.change.password1')}}" class="">@lang('Change Password')</a>
                                </li>
                                <li>
                                    <a href="{{ route('user.logout') }}" class="">@lang('Sign Out')</a>
                                </li>
                                
                              </ul>
                            </div>
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