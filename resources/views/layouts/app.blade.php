<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>@yield('title', 'Dashboard')</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="{{ asset('public/assets/style.css') }}">
        <style>
            body{
            overflow-x:hidden;
            background:#f1f5f9;
            }
            /* SIDEBAR */
            .sidebar{
            min-height:100vh;
            background:linear-gradient(180deg,#0f172a,#020617);
            padding-top:10px;
            transition:.3s;
            }
            /* MOBILE */
            @media(max-width:768px){
            .sidebar{
            position:fixed;
            top:0;
            left:-260px;
            width:260px;
            z-index:999;
            height:100%;
            }
            .sidebar.active{
            left:0;
            }
            }
            /* LOGO */
            .logo{
            font-size:18px;
            color:#fff;
            text-align:center;
            margin-bottom:20px;
            }
            /* LINKS */
            .sidebar .nav-link{
            color:#cbd5f5;
            padding:10px 14px;
            border-radius:10px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            }
            .sidebar .nav-link:hover{
            background:#1e293b;
            color:#fff;
            }
            .sidebar .nav-link.active{
            background:#2563eb;
            color:#fff;
            }
            /* SUB MENU */
            .sub-link{
            margin-left:30px;
            font-size:13px;
            color:#94a3b8 !important;
            padding:6px 10px;
            display:block;
            text-decoration: none !important;
            }
            .sub-link:hover{
            color:#fff !important;
            background:#1e293b;
            border-radius:8px;
            }
            .sub-link.active{
            color:#fff !important;
            background:#1e293b;
            border-left:3px solid #3b82f6;
            }
            /* HEADER */
            .page-header{
            background:#fff;
            padding:15px 20px;
            border-radius:12px;
            box-shadow:0 4px 12px rgba(0,0,0,.05);
            }
            /* OVERLAY */
            #overlay{
            position:fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background:rgba(0,0,0,.4);
            display:none;
            z-index:998;
            }
            #overlay.active{
            display:block;
            }
        </style>
    </head>
    <body>
        <div id="overlay"></div>
        <div class="container-fluid">
            <div class="row">
                <!-- SIDEBAR -->
                <nav class="col-md-2 sidebar py-3">
                    <div class="px-3">
                        <div class="logo">
                            <i class="bi bi-building"></i> Shree Groups
                        </div>
                        <ul class="nav flex-column gap-1">
                            @auth
                            @if(auth()->user()->role === 'admin')
                            <!-- Dashboard -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}"
                                    href="{{ url('/admin/dashboard') }}">
                                <span><i class="bi bi-speedometer2"></i> Dashboard</span>
                                </a>
                            </li>
                            <!-- STAFF -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/staff*') ? 'active' : '' }}"
                                    data-bs-toggle="collapse"
                                    href="#staffMenu">
                                <span><i class="bi bi-people"></i> Staff</span>
                                <i class="bi bi-chevron-down"></i>
                                </a>
                                <div class="collapse {{ request()->is('admin/staff*') ? 'show' : '' }}" id="staffMenu">
                                    <a class="sub-link {{ request()->is('admin/staff') ? 'active' : '' }}"
                                        href="{{ url('/admin/staff') }}">
                                    Staff List
                                    </a>
                                    <a class="sub-link {{ request()->is('admin/staff-attendance*') ? 'active' : '' }}"
                                        href="{{ route('staff.attendance') }}">
                                    Attendance
                                    </a>
                                    <a class="sub-link {{ request()->is('admin/staff-monthly-report*') ? 'active' : '' }}"
                                        href="{{ route('staff.monthly') }}">
                                    Monthly Report
                                    </a>
                                </div>
                            </li>
                            <!-- ORDERS -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/orders*') ? 'active' : '' }}"
                                    href="{{ url('/admin/orders') }}">
                                <span><i class="bi bi-box-seam"></i> Orders</span>
                                </a>
                            </li>
                            <!-- ✅ CUSTOMERS (ADDED) -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/customers*') ? 'active' : '' }}"
                                    data-bs-toggle="collapse"
                                    href="#customerMenu">
                                <span><i class="bi bi-person-lines-fill"></i> Customers</span>
                                <i class="bi bi-chevron-down"></i>
                                </a>
                                <div class="collapse {{ request()->is('admin/customers*') ? 'show' : '' }}" id="customerMenu">
                                    <a class="sub-link {{ request()->is('admin/customers') ? 'active' : '' }}"
                                        href="{{ url('/admin/customers') }}">
                                    Customer List
                                    </a>
                                    <!-- <a class="sub-link {{ request()->is('admin/customers/create') ? 'active' : '' }}"
                                        href="{{ url('/admin/customers/create') }}">
                                        Add Customer
                                        </a> -->
                                    <a class="sub-link {{ request()->is('admin/customers/due*') ? 'active' : '' }}"
                                        href="{{ url('/admin/customers/due') }}">
                                    Due Customers
                                    </a>
                                </div>
                            </li>
                            <!-- EXPENSES -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('admin/expenses*') || request()->is('admin/categories*') ? 'active' : '' }}"
                                    data-bs-toggle="collapse"
                                    href="#expenseMenu">
                                    
                                    <span><i class="bi bi-cash-stack"></i> Expenses</span>
                                    <i class="bi bi-chevron-down"></i>
                                </a>

                                <div class="collapse {{ request()->is('admin/expenses*') || request()->is('admin/categories*') ? 'show' : '' }}" id="expenseMenu">

                                    <!-- All Expenses -->
                                    <a class="sub-link {{ request()->is('admin/expenses') ? 'active' : '' }}"
                                        href="{{ url('/admin/expenses') }}">
                                        All Expenses
                                    </a>

                                    <!-- Add Expense -->
                                    <a class="sub-link {{ request()->is('admin/expenses/create') ? 'active' : '' }}"
                                        href="{{ route('expenses.create') }}">
                                        Add Expense
                                    </a>

                                    <!-- Categories -->
                                    <a class="sub-link {{ request()->is('admin/categories*') ? 'active' : '' }}"
                                        href="{{ route('categories') }}">
                                        Categories
                                    </a>

                                </div>
                            </li>
                            @endif
                            {{-- ================= STAFF MENU ================= --}}
                            @if(auth()->user()->role === 'staff')
                            <!-- Dashboard -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('staff/dashboard') ? 'active' : '' }}"
                                    href="{{ url('/staff/dashboard') }}">
                                <span><i class="bi bi-speedometer2"></i> Dashboard</span>
                                </a>
                            </li>
                            <!-- Orders -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('staff/orders*') ? 'active' : '' }}"
                                    href="{{ url('/staff/orders') }}">
                                <span><i class="bi bi-box-seam"></i> Orders</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('staff/customers*') ? 'active' : '' }}"
                                    data-bs-toggle="collapse"
                                    href="#customerMenu">
                                <span><i class="bi bi-person-lines-fill"></i> Customers</span>
                                <i class="bi bi-chevron-down"></i>
                                </a>
                                <div class="collapse {{ request()->is('staff/customers*') ? 'show' : '' }}" id="customerMenu">
                                    <a class="sub-link {{ request()->is('staff/customers') ? 'active' : '' }}"
                                        href="{{ url('/staff/customers') }}">
                                    Customer List
                                    </a>
                                    <!-- <a class="sub-link {{ request()->is('staff/customers/create') ? 'active' : '' }}"
                                        href="{{ url('/staff/customers/create') }}">
                                        Add Customer
                                        </a> -->
                                    <a class="sub-link {{ request()->is('staff/customers/due*') ? 'active' : '' }}"
                                        href="{{ url('/staff/customers/due') }}">
                                    Due Customers
                                    </a>
                                </div>
                            </li>
                            @endif
                            @endauth
                            <!-- LOGOUT -->
                            <li class="nav-item mt-3">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="nav-link w-100 text-start border-0 bg-transparent">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </nav>
                <!-- MAIN -->
                <main class="col-md-10 ms-sm-auto px-md-4 py-4">
                    <div class="page-header d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn d-md-none" id="menuToggle">
                            <i class="fas fa-bars"></i>
                            </button>
                            <h2 class="m-0">@yield('title')</h2>
                        </div>
                        <!-- <div class="text-muted">
                            {{ date('d M Y') }}
                        </div> -->
                        <div class="text-muted">
                            {{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})
                        </div>
                    </div>
                    @yield('content')
                </main>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            const toggle = document.getElementById('menuToggle');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('overlay');
            
            toggle.onclick = () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            }
            
            overlay.onclick = () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            }
        </script>
    </body>
</html>