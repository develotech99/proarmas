<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-slate-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 mb-2">Verificar Correo Electrónico</h1>
                <p class="text-slate-600">Confirma tu cuenta para acceder al sistema</p>
            </div>

            <!-- Verification Info -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">
                <!-- Main Message -->
                <div class="text-center mb-6">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-slate-800 mb-3">Revisa tu correo electrónico</h2>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Te hemos enviado un enlace de verificación a tu correo electrónico. 
                        Haz clic en el enlace para activar tu cuenta y acceder al sistema de inventario.
                    </p>
                </div>

                <!-- Success Message -->
                @if (session('status') == 'verification-link-sent')
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm font-medium text-green-800">
                                Enlace de verificación reenviado correctamente
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="space-y-4">
                    <!-- Resend Button -->
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-slate-800 text-white py-3 px-4 rounded-lg font-medium hover:bg-slate-700 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors duration-200">
                            Reenviar Correo de Verificación
                        </button>
                    </form>

                    <!-- Logout Button -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-slate-100 text-slate-700 py-3 px-4 rounded-lg font-medium hover:bg-slate-200 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors duration-200">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>

            <!-- Help Text -->
            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                <div class="text-center">
                    <h3 class="text-sm font-medium text-slate-800 mb-2">¿No recibiste el correo?</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Revisa tu bandeja de entrada y carpeta de spam. Si el problema persiste, 
                        contacta al administrador del sistema.
                    </p>
                </div>
            </div>

            <!-- Back to Home -->
            <div class="text-center">
                <a href="{{ url('/') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-slate-700 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver al inicio
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>