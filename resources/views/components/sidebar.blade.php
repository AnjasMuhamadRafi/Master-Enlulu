<!-- Sidebar Component -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('images/public/ENLULU.png') }}" class="sidebar-brand-logo" alt="Enlulu Logo">
        <span class="sidebar-brand-text">ENLULU</span>
    </div>
    
    <div class="sidebar-content">
        <ul class="sidebar-menu">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('dashboard') }}" class="menu-item" data-label="Dashboard">
                    <i class="bi bi-house-fill"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            
            <!-- Data Karyawan -->
            <li>
                <a href="#" class="menu-item menu-toggle" data-submenu="employee" data-label="Data Karyawan">
                    <i class="bi bi-people-fill"></i>
                    <span class="menu-text">Data Karyawan</span>
                    <i class="bi bi-chevron-down submenu-chevron"></i>
                </a>
                <ul class="submenu" id="employee-submenu">
                    <li><a href="{{ route('employee.index') }}"><i class="bi bi-list"></i><span class="menu-text">Daftar Karyawan</span></a></li>
                    <li><a href="{{ route('employee.create') }}"><i class="bi bi-plus-circle"></i><span class="menu-text">Tambah Karyawan</span></a></li>
                    <li><a href="{{ route('employee.import') }}"><i class="bi bi-file-earmark-excel"></i><span class="menu-text">Import Excel</span></a></li>
                </ul>
            </li>
            
            <!-- Kontrak -->
            <li>
                <a href="#" class="menu-item menu-toggle" data-submenu="contract" data-label="Kontrak">
                    <i class="bi bi-file-text-fill"></i>
                    <span class="menu-text">Kontrak</span>
                    <i class="bi bi-chevron-down submenu-chevron"></i>
                </a>
                <ul class="submenu" id="contract-submenu">
                    <li><a href="{{ route('contract.index') }}"><i class="bi bi-list"></i><span class="menu-text">Daftar Kontrak</span></a></li>
                    <li><a href="{{ route('contract.create') }}"><i class="bi bi-plus-circle"></i><span class="menu-text">Buat Kontrak</span></a></li>
                    <li><a href="{{ route('contract.template') }}"><i class="bi bi-file-earmark-text"></i><span class="menu-text">Template</span></a></li>
                </ul>
            </li>
            
            <!-- Laporan -->
            <li>
                <a href="#" class="menu-item menu-toggle" data-submenu="report" data-label="Laporan">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span class="menu-text">Laporan</span>
                    <i class="bi bi-chevron-down submenu-chevron"></i>
                </a>
                <ul class="submenu" id="report-submenu">
                    <li><a href="{{ route('report.aktif') }}"><i class="bi bi-bar-chart"></i><span class="menu-text">Report Karyawan Aktif</span></a></li>
                    <li><a href="{{ route('report.resign') }}"><i class="bi bi-bar-chart"></i><span class="menu-text">Report Karyawan Resign</span></a></li>
                </ul>
            </li>
            
            <!-- Divider untuk admin roles -->
            @if(auth()->check() && (auth()->user()->role === 'Super Admin' || auth()->user()->role === 'Admin'))
                <li class="menu-divider" style="margin: 10px 5px; border-top: 2px solid rgba(255,255,255,0.2); padding: 0;"></li>
                
                <!-- Pengaturan -->
                <li>
                    <a href="#" class="menu-item menu-toggle" data-submenu="settings" data-label="Pengaturan">
                        <i class="bi bi-gear-fill"></i>
                        <span class="menu-text">Pengaturan</span>
                        <i class="bi bi-chevron-down submenu-chevron"></i>
                    </a>
                    <ul class="submenu" id="settings-submenu">
                        @if(auth()->user()->role === 'Super Admin')
                            <li><a href="{{ route('settings.users') }}"><i class="bi bi-person-gear"></i><span class="menu-text">Kelola User</span></a></li>
                            <li><a href="{{ route('settings.roles') }}"><i class="bi bi-shield-check"></i><span class="menu-text">Kelola Role</span></a></li>
                        @endif
                        <li><a href="{{ route('settings.profile') }}"><i class="bi bi-person"></i><span class="menu-text">Profil Saya</span></a></li>
                        <li><a href="{{ route('settings.security') }}"><i class="bi bi-lock"></i><span class="menu-text">Keamanan</span></a></li>
                    </ul>
                </li>
                
                <!-- Activity Log - Super Admin only -->
                @if(auth()->user()->role === 'Super Admin')
                    <li>
                        <a href="{{ route('log.index') }}" class="menu-item" data-label="Activity Log">
                            <i class="bi bi-clock-history"></i>
                            <span class="menu-text">Activity Log</span>
                        </a>
                    </li>
                @endif
            @endif
        </ul>
    </div>
    
    <!-- User Menu at Bottom -->
    <div class="sidebar-footer">
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="logout-link" data-label="Keluar">
            <i class="bi bi-box-arrow-right"></i>
            <span class="menu-text">Keluar</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</div>

<script>
    // Sidebar menu toggle
    document.querySelectorAll('.menu-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const submenuId = this.getAttribute('data-submenu');
            const submenu = document.getElementById(submenuId + '-submenu');
            submenu.classList.toggle('show');
            
            // Rotate chevron
            const chevron = this.querySelector('.submenu-chevron');
            if (chevron) {
                chevron.style.transform = submenu.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        });
    });
    
    // Show submenu if route matches
    document.querySelectorAll('.submenu a').forEach(link => {
        if (link.href === window.location.href) {
            const submenu = link.closest('.submenu');
            if (submenu) {
                submenu.classList.add('show');
                // Rotate chevron
                const toggle = submenu.previousElementSibling;
                if (toggle) {
                    const chevron = toggle.querySelector('.submenu-chevron');
                    if (chevron) {
                        chevron.style.transform = 'rotate(180deg)';
                    }
                }
            }
        }
    });
</script>
