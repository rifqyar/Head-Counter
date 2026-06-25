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
                                <i class='fa fa-building'></i>
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

                    @can('meeting.view')
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

                @if (auth()->user()?->can('Transaction') || auth()->user()?->can('redemption.scan'))
                    <li class='nav-small-cap'>Transaction</li>
                    @can('Participant')
                        <li>
                            <a href='{{ route('participants.index') }}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-account-check'></i>
                                <span>Participants</span>
                            </a>
                        </li>
                    @endcan
                    @can('meeting.view')
                        <li>
                            <a href='{{ route('meetings.index') }}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-account-circle'></i>
                                <span>Meeting Attendance</span>
                            </a>
                        </li>
                    @endcan
                    @can('redemption.scan')
                        <li>
                            <a href='{{ route('scanner.index') }}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-qrcode-scan'></i>
                                <span>QR Scanner</span>
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
                @endif

                @can('Report')
                    <li class='nav-small-cap'>Report</li>
                    @can('Meeting Report')
                        <li>
                            <a href='{{ route('reports.show', 'meetings') }}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-file-chart'></i>
                                <span>Meeting Report</span>
                            </a>
                        </li>
                    @endcan
                    @can('report.view')
                        <li>
                            <a href='{{ route('reports.index') }}' class='sidebar-link spa_route'>
                                <i class='fa fa-bar-chart'></i>
                                <span>All Reports</span>
                            </a>
                        </li>
                    @endcan
                    @can('report.export')
                        <li>
                            <a href='{{ route('reports.exports.index') }}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-download'></i>
                                <span>Export Center</span>
                            </a>
                        </li>
                    @endcan
                @endcan

                @can('Setting')
                    <li class='nav-small-cap'>Setting</li>
                    @can('Manage User')
                        <li class='sidebar-item'>
                            <a href='{{ route('users.index') }}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-account-settings-variant'></i>
                                <span>Manage User</span>
                            </a>
                        </li>
                    @endcan
                    @can('settings.manage')
                        <li class='sidebar-item'>
                            <a href='{{ route('settings.index') }}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-tune'></i>
                                <span>Hotel Settings</span>
                            </a>
                        </li>
                    @endcan
                    @if (auth()->user()?->isSuperAdmin())
                        <li class='sidebar-item'>
                            <a href='{{ route('settings.subscriptions.index') }}' class='sidebar-link spa_route'>
                                <i class='fa fa-credit-card'></i>
                                <span>Subscriptions</span>
                            </a>
                        </li>
                        <li class='sidebar-item'>
                            <a href='{{ route('tenant-switch.index') }}' class='sidebar-link spa_route'>
                                <i class='mdi mdi-swap-horizontal'></i>
                                <span>Tenant Switch</span>
                            </a>
                        </li>
                    @endif

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
