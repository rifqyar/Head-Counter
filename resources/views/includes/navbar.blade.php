<header class="topbar">
    <nav class="navbar top-navbar navbar-expand-md navbar-light">
        <!-- ============================================================== -->
        <!-- Logo -->
        <!-- ============================================================== -->
        <div class="navbar-header">
            <a class="navbar-brand" href="{{route('dashboard')}}">
                <!-- Logo icon --><b>
                    <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                    <!-- Dark Logo icon -->
                    <img src="{{ app(\App\Support\Branding\HotelLogo::class)->currentAsset() }}" width="50%" alt="Hotel logo" class="dark-logo" />
                    <!-- Light Logo icon -->
                    <img src="{{ app(\App\Support\Branding\HotelLogo::class)->currentAsset() }}" width="50%" alt="Hotel logo" class="light-logo" />
                </b>
                <span></span>
            </a>
        </div>
        <!-- ============================================================== -->
        <!-- End Logo -->
        <!-- ============================================================== -->
        <div class="navbar-collapse">
            <!-- ============================================================== -->
            <!-- toggle and nav items -->
            <!-- ============================================================== -->
            <ul class="navbar-nav mr-auto mt-md-0">
                <!-- This is  -->
                <li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="mdi mdi-menu"></i></a> </li>
                <li class="nav-item"> <a class="nav-link sidebartoggler hidden-sm-down text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="ti-menu"></i></a> </li>
            </ul>
            <ul class="navbar-nav my-lg-0">
                @if (Auth::user()?->isSuperAdmin())
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="hc-tenant-pill">
                                <i class="mdi mdi-domain"></i>
                                {{ app(\App\Support\Tenancy\TenantContext::class)->hotel()?->code ?? 'All Hotels' }}
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right scale-up p-3" style="min-width: 320px;">
                            <strong>Tenant Context</strong>
                            <p class="text-muted mb-2">Switch the current hotel context for hotel-scoped screens.</p>
                            <form method="POST" action="{{ route('tenant-switch.switch') }}">
                                @csrf
                                <select name="hotel_id" class="form-control mb-2" required>
                                    <option value="">Choose hotel</option>
                                    @foreach (\App\Domain\Hotel\Hotel::where('status', 'ACTIVE')->orderBy('name')->get() as $hotel)
                                        <option value="{{ $hotel->id }}" @selected((int) session('tenant_hotel_id') === $hotel->id)>{{ $hotel->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-primary btn-block">Switch Hotel</button>
                            </form>
                            <form method="POST" action="{{ route('tenant-switch.reset') }}" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-secondary btn-block">View All Hotels</button>
                            </form>
                            <a href="{{ route('tenant-switch.index') }}" class="dropdown-item mt-2 px-0 spa_route">Open switcher page</a>
                        </div>
                    </li>
                @else
                    <li class="nav-item">
                        <span class="nav-link text-muted">
                            <span class="hc-tenant-pill">
                                <i class="mdi mdi-domain"></i>
                                {{ app(\App\Support\Tenancy\TenantContext::class)->hotel()?->code ?? Auth::user()?->hotel?->code ?? 'No Hotel' }}
                            </span>
                        </span>
                    </li>
                @endif
                <!-- ============================================================== -->
                <!-- Profile -->
                <!-- ============================================================== -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark hc-user-trigger" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="hc-user-name">{{ Auth::user()->name }}</span>
                        <img src="../assets/images/users/1.jpg" alt="user" class="profile-pic" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-right scale-up">
                        <ul class="dropdown-user">
                            <li>
                                <div class="dw-user-box">
                                    <div class="u-img"><img src="../assets/images/users/1.jpg" alt="user"></div>
                                    <div class="u-text">
                                        <h4>Hello, {{ Auth::user()->name }}!</h4>
                                        <a href="#" class="btn btn-rounded btn-danger btn-sm">View Profile</a></div>
                                </div>
                            </li>
                            <li>
                                <a href="{{ route('logout') }}" class='dropdown-item'
                                onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                    <i class="fa fa-power-off"></i>
                                    <span>Log Out</span>
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</header>
