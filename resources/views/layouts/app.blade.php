<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', 'Default Title')</title>
        <!-- Add your CSS here -->
        <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    </head>
    <body>
        <div class="sidebar">
            @include('layouts.sidebar')
        </div>

        <section class="home-section">
            <nav>
                <div class="sidebar-button">
                    <i class="bx bx-menu sidebarBtn"></i>
                    <span class="dashboard">Dashboard</span>
                </div>
                <div class="search-box">
                    <input type="text" placeholder="Search..." />
                    <i class="bx bx-search"></i>
                </div>
                <div class="profile-details">
                    <img src="{{ asset('assets/img/profile.jpg') }}" alt="" />
                    <span class="admin_name">Prem Shahi</span>
                    <i class="bx bx-chevron-down"></i>
                </div>
            </nav>            
            @yield('content')
        </section>

        <footer>
            @include('layouts.footer')
        </footer>

        <script src="{{ asset('assets/js/app.js') }}"></script>
    </body>
</html>