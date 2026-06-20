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
                    @can('Hotel')
                        <li>
                            <a href='{{ route('hotels.index') }}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-domain'></i>
                                <span>Hotels</span>
                            </a>
                        </li>
                    @endcan
                    @can('Meeting Room')
                        <li>
                            <a href='{{ route('meeting-rooms.index') }}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-door'></i>
                                <span>Meeting Rooms</span>
                            </a>
                        </li>
                    @endcan
                    @can('Client')
                        <li>
                            <a href='{{ route('clients.index') }}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-account-multiple'></i>
                                <span>Clients</span>
                            </a>
                        </li>
                    @endcan

                    @can('Meeting Schedule')
                        <li>
                            <a href='{{ route('meetings.index') }}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-calendar-range'></i>
                                <span>Meetings</span>
                            </a>
                        </li>
                    @endcan
                    @can('Booking')
                        <li>
                            <a href='{{ route('bookings.index') }}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-book-open'></i>
                                <span>Bookings</span>
                            </a>
                        </li>
                    @endcan
                    @can('Meeting Package')
                        <li>
                            <a href='{{ route('packages.index') }}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-package-variant'></i>
                                <span>Packages</span>
                            </a>
                        </li>
                    @endcan
                @endcan

                @can('Transaction')
                    <li class='nav-small-cap'>Transaction</li>
                    @can('Participant')
                        <li>
                            <a href='{{ route('participants.index') }}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-account-check'></i>
                                <span>Participants</span>
                            </a>
                        </li>
                    @endcan
                    @can('Meeting Trans')
                        <li>
                            <a href='{{route('transaction.meeting-attendance')}}' class='sidebar-link spa_route'>
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
