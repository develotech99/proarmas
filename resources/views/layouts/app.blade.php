<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ProArmas y Municiones') }} - Sistema de Inventario</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <script>
            function toggleMobileMenu() {
                const sidebar = document.getElementById('mobile-sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
            
            function closeMobileMenu() {
                const sidebar = document.getElementById('mobile-sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        </script>
    </head>
    <body class="font-sans antialiased bg-slate-50">
        <div class="min-h-screen flex">
            <!-- Sidebar Overlay (Mobile) -->
            <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden" onclick="closeMobileMenu()"></div>
            
            <!-- Sidebar -->
            <div id="mobile-sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-800 transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:flex-shrink-0">
                @include('layouts.navigation')
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Top Header -->
                <header class="bg-white shadow-sm border-b border-slate-200 lg:hidden">
                    <div class="px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between items-center py-4">
                            <!-- Mobile Menu Button -->
                            <button onclick="toggleMobileMenu()" class="p-2 rounded-md text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-slate-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>
                            
                            <!-- Mobile Logo -->
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-slate-800 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 4h.01M9 12h.01M9 16h.01M13 12h.01M13 16h.01M13 8h.01M17 12h.01M17 16h.01"></path>
                                    </svg>
                                </div>
                                <span class="text-lg font-semibold text-slate-800">ProArmas</span>
                            </div>
                            
                            <div class="w-10"></div> <!-- Spacer for centering -->
                        </div>
                    </div>
                </header>

                <!-- Page Heading (Desktop) -->
                @isset($header)
                    <header class="bg-white shadow-sm border-b border-slate-200 hidden lg:block">
                        <div class="px-6 py-4">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1 p-2">
                @yield('content')  
                </main>
            </div>
        </div>

        @yield('scripts')
    </body>
</html>