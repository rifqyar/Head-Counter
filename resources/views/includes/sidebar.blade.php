<aside class="left-sidebar">
    <div class="scroll-sidebar">
        <nav class="sidebar-nav">
            <ul id="sidebarnav">
                @can('Dashboard')
                    <li class='nav-small-cap'>Main Menu</li>
                    <li>
                        <a href='{{route('dashboard')}}' class='sidebar-link spa_route'>
                            <i class='mdi mdi-view-dashboard'></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                @endcan

                @can('Master Data')
                    <li class='nav-small-cap'>Master Data</li>
                    @can('Client')
                        <li>
                            <a href='{{route('masterdata.client')}}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-account-multiple'></i>
                                <span>Client</span>
                            </a>
                        </li>
                    @endcan

                    @can('Meeting Schedule')
                        <li>
                            <a href='{{route('masterdata.meeting-schedule')}}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-calendar-range'></i>
                                <span>Meeting Schedule</span>
                            </a>
                        </li>
                    @endcan
                @endcan

                @can('Transaction')
                    <li class='nav-small-cap'>Transaction</li>
                    @can('Meeting Trans')
                        <li>
                            <a href='#' class='sidebar-link spa_route'>
                                <i class='mdi mdi-account-circle'></i>
                                <span>Meeting Attendace</span>
                            </a>
                        </li>
                    @endcan

                    {{-- @can('Additional Slot')
                        <li>
                            <a href='#' class='sidebar-link spa_route'>
                                <i class='bi bi-person-add'></i>
                                <span>Additional Slot</span>
                            </a>
                        </li>
                    @endcan --}}
                @endcan

                @can('Report')
                    <li class='nav-small-cap'>Report</li>
                    @can('Meeting Report')
                        <li>
                            <a href='#' class='sidebar-link spa_route'>
                                <i class='mdi mdi-file-chart'></i>
                                <span>Meeting Report</span>
                            </a>
                        </li>
                    @endcan
                @endcan

                @can('Setting')
                    <li class='nav-small-cap'>Setting</li>
                    @can('Manage User')
                        <li class='sidebar-item'>
                            <a href='#' class='sidebar-link spa_route'>
                                <i class='mdi mdi-account-settings-variant'></i>
                                <span>Manage User</span>
                            </a>
                        </li>
                    @endcan

                    @can('Manage Role')
                        <li class='sidebar-item'>
                            <a href='{{route('setting.role')}}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-settings'></i>
                                <span>Manage Role</span>
                            </a>
                        </li>
                    @endcan

                    @can('Manage Permission')
                        <li class='sidebar-item'>
                            <a href='{{route('setting.permission')}}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-settings'></i>
                                <span>Manage Permission</span>
                            </a>
                        </li>
                    @endcan
                @endcan
            </ul>
        </nav>
    </div>
</aside>
