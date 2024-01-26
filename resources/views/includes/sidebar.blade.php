<div id="app">
    <div id="sidebar" class="active">
        <div class="sidebar-wrapper active">
            <div class="sidebar-header position-relative">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="logo">
                        <a class="navbar-brand m-0" href="#" target="_blank">
                            <img src="{{ asset('assets/images/logo/logo-sm.png') }}" class="navbar-brand-img" alt="main_logo" style="height:60px;border-radius:10px" >
                        </a>
                    </div>
                    <div class="theme-toggle d-flex gap-1  align-items-center mt-2">
                        <div class="form-check form-switch fs-6">
                            <span class="nav-link-text xs-1">Dark Mode</span>
                        </div>
                        <div class="form-check form-switch fs-6">
                            <input class="form-check-input  me-0" type="checkbox" id="toggle-dark">
                            <label class="form-check-label"></label>
                        </div>
                    </div>
                    <div class="sidebar-toggler  x">
                        <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                    </div>
                </div>
            </div>
            <div class="sidebar-menu">
                <ul class="menu">
                    @can('Dashboard')
                        <li class='sidebar-title'>Main Menu</li>
                        <li class='sidebar-item'>
                            <a href='{{route('dashboard')}}' class='sidebar-link spa_route'>
                                <i class='bi bi-grid-fill'></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                    @endcan

                    @can('Master Data')
                        <li class='sidebar-title'>Master Data</li>
                        @can('Client')
                            <li class='sidebar-item'>
                                <a href='{{route('masterdata.client')}}' class='sidebar-link spa_route'>
                                    <i class='fa-solid fa-user-tie'></i>
                                    <span>Client</span>
                                </a>
                            </li>
                        @endcan

                        @can('Meeting Schedule')
                            <li class='sidebar-item'>
                                <a href='{{route('masterdata.meeting-schedule')}}' class='sidebar-link spa_route'>
                                    <i class='bi bi-calendar-date'></i>
                                    <span>Meeting Schedule</span>
                                </a>
                            </li>
                        @endcan
                    @endcan

                    @can('Transaction')
                        <li class='sidebar-title'>Transaction</li>
                        @can('Meeting Trans')
                            <li class='sidebar-item'>
                                <a href='#' class='sidebar-link spa_route'>
                                    <i class='bi bi-people-fill'></i>
                                    <span>Meeting Attendace</span>
                                </a>
                            </li>
                        @endcan

                        @can('Additional Slot')
                            <li class='sidebar-item'>
                                <a href='#' class='sidebar-link spa_route'>
                                    <i class='bi bi-person-add'></i>
                                    <span>Additional Slot</span>
                                </a>
                            </li>
                        @endcan
                    @endcan

                    @can('Report')
                        <li class='sidebar-title'>Report</li>
                        @can('Meeting Report')
                            <li class='sidebar-item'>
                                <a href='#' class='sidebar-link spa_route'>
                                    <i class='bi bi-graph-up'></i>
                                    <span>Meeting Report</span>
                                </a>
                            </li>
                        @endcan
                    @endcan

                    @can('Setting')
                        <li class='sidebar-title'>Setting</li>
                        @can('Manage User')
                            <li class='sidebar-item'>
                                <a href='#' class='sidebar-link spa_route'>
                                    <i class='bi bi-person-gear'></i>
                                    <span>Manage User</span>
                                </a>
                            </li>
                        @endcan

                        @can('Manage Role')
                            <li class='sidebar-item'>
                                <a href='{{route('setting.role')}}' class='sidebar-link spa_route'>
                                    <i class='bi bi-gear'></i>
                                    <span>Manage Role</span>
                                </a>
                            </li>
                        @endcan

                        @can('Manage Permission')
                            <li class='sidebar-item'>
                                <a href='{{route('setting.permission')}}' class='sidebar-link spa_route'>
                                    <i class='bi bi-gear'></i>
                                    <span>Manage Permission</span>
                                </a>
                            </li>
                        @endcan
                    @endcan
                </ul>
            </div>
        </div>
    </div>
