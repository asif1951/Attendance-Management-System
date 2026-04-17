<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />
    <AuthenticatedLayout>
        <!-- Background with Gradient -->
        <div class="fixed inset-0 bg-gradient-to-br from-blue-50 via-white to-purple-50 -z-10"></div>
        
        <!-- Animated Background Elements -->
        <div class="fixed inset-0 overflow-hidden -z-5">
            <div class="absolute top-10 left-10 w-80 h-80 bg-blue-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
            <div class="absolute top-40 right-20 w-80 h-80 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-20 left-1/3 w-80 h-80 bg-pink-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
        </div>

        <!-- Main Container -->
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="w-full max-w-lg transform transition-all duration-500">
                <!-- Main Card -->
                <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl p-8 border border-white/30 relative overflow-hidden">
                    <!-- Card Glow Effect -->
                    <div class="absolute -inset-4 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-3xl blur-3xl opacity-10 -z-10"></div>
                    
                    <!-- Header Section -->
                    <div class="text-center mb-8">
                        <!-- Animated Logo Container -->
                        <div class="relative inline-block mb-6">
                            <div class="absolute -inset-3 bg-gradient-to-r from-blue-500 to-purple-500 rounded-2xl blur-lg opacity-30 animate-pulse"></div>
                            <div class="relative w-20 h-20 bg-gradient-to-br from-blue-600 via-purple-600 to-pink-500 rounded-2xl flex items-center justify-center shadow-xl transform hover:rotate-3 transition-transform duration-500">
                                <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </div>
                            <!-- Floating Elements -->
                            <div class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-bounce"></div>
                            <div class="absolute -bottom-2 -left-2 w-3 h-3 bg-cyan-400 rounded-full animate-bounce animation-delay-300"></div>
                        </div>
                        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mb-3">
                            Create Account
                        </h1>
                        <p class="text-gray-600 font-medium">Join our AMS community today</p>
                    </div>

                    <!-- Register Form -->
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name Field -->
                        <div class="space-y-2 group">
                            <InputLabel for="name" value="Full Name" class="text-gray-700 font-semibold" />
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl blur-sm group-hover:blur transition-all duration-300 opacity-0 group-hover:opacity-100"></div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400 group-hover:text-blue-500 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <TextInput
                                        id="name"
                                        type="text"
                                        class="mt-1 block w-full pl-12 pr-4 py-3.5 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-0 transition-all duration-300 bg-white/80"
                                        v-model="form.name"
                                        required
                                        autofocus
                                        autocomplete="name"
                                        placeholder="Enter your full name"
                                    />
                                </div>
                            </div>
                            <InputError class="mt-2 text-sm text-red-600 font-medium" :message="form.errors.name" />
                        </div>

                        <!-- Email Field -->
                        <div class="space-y-2 group">
                            <InputLabel for="email" value="Email Address" class="text-gray-700 font-semibold" />
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-r from-cyan-50 to-purple-50 rounded-xl blur-sm group-hover:blur transition-all duration-300 opacity-0 group-hover:opacity-100"></div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400 group-hover:text-purple-500 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <TextInput
                                        id="email"
                                        type="email"
                                        class="mt-1 block w-full pl-12 pr-4 py-3.5 rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-0 transition-all duration-300 bg-white/80"
                                        v-model="form.email"
                                        required
                                        autocomplete="username"
                                        placeholder="you@example.com"
                                    />
                                </div>
                            </div>
                            <InputError class="mt-2 text-sm text-red-600 font-medium" :message="form.errors.email" />
                        </div>

                        <!-- Password Field -->
                        <div class="space-y-2 group">
                            <InputLabel for="password" value="Password" class="text-gray-700 font-semibold" />
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl blur-sm group-hover:blur transition-all duration-300 opacity-0 group-hover:opacity-100"></div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400 group-hover:text-pink-500 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                    <TextInput
                                        id="password"
                                        type="password"
                                        class="mt-1 block w-full pl-12 pr-4 py-3.5 rounded-xl border-2 border-gray-200 focus:border-pink-500 focus:ring-0 transition-all duration-300 bg-white/80"
                                        v-model="form.password"
                                        required
                                        autocomplete="new-password"
                                        placeholder="Create a strong password"
                                    />
                                </div>
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                Minimum 8 characters with letters and numbers
                            </div>
                            <InputError class="mt-2 text-sm text-red-600 font-medium" :message="form.errors.password" />
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="space-y-2 group">
                            <InputLabel for="password_confirmation" value="Confirm Password" class="text-gray-700 font-semibold" />
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-r from-pink-50 to-blue-50 rounded-xl blur-sm group-hover:blur transition-all duration-300 opacity-0 group-hover:opacity-100"></div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400 group-hover:text-blue-500 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <TextInput
                                        id="password_confirmation"
                                        type="password"
                                        class="mt-1 block w-full pl-12 pr-4 py-3.5 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-0 transition-all duration-300 bg-white/80"
                                        v-model="form.password_confirmation"
                                        required
                                        autocomplete="new-password"
                                        placeholder="Re-enter your password"
                                    />
                                </div>
                            </div>
                            <InputError class="mt-2 text-sm text-red-600 font-medium" :message="form.errors.password_confirmation" />
                        </div>

                        <!-- Submit Button -->
                        <div class="relative group">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-500 rounded-2xl blur-lg group-hover:blur-xl transition-all duration-500 opacity-70"></div>
                            <PrimaryButton
                                class="relative w-full py-4 px-6 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-500 text-white font-bold rounded-2xl shadow-xl group-hover:shadow-2xl transform group-hover:scale-[1.02] transition-all duration-500"
                                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                                :disabled="form.processing"
                            >
                                <span class="flex items-center justify-center space-x-3">
                                    <span v-if="form.processing">
                                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Creating Account...
                                    </span>
                                    <span v-else class="flex items-center space-x-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                        </svg>
                                        <span>Create Account</span>
                                        <span class="opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-300">✨</span>
                                    </span>
                                </span>
                            </PrimaryButton>
                        </div>
                    </form>

                    <!-- Login Link -->
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <div class="text-center">
                            <p class="text-gray-600 mb-4">Already have an account?</p>
                            <Link
                                :href="route('login')"
                                class="inline-flex items-center justify-center w-full py-3 px-6 bg-white hover:bg-gray-50 text-gray-800 font-medium rounded-xl border-2 border-gray-300/50 hover:border-blue-300/50 shadow-sm hover:shadow-md transition-all duration-300 group"
                            >
                                <svg class="w-5 h-5 mr-2 text-gray-400 group-hover:text-blue-500 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                Sign in to your account
                            </Link>
                        </div>
                    </div>

                    <!-- Security Info -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex items-center justify-center space-x-6 text-xs text-gray-500">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Secure Registration</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>Data Protected</span>
                            </div>
                        </div>
                        <p class="mt-4 text-center text-xs text-gray-500">
                            © 2024 Attendance Management System. Your privacy matters.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Custom Animations */
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

@keyframes pulse {
    0%, 100% {
        opacity: 0.3;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-300 {
    animation-delay: 0.3s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

.animate-pulse {
    animation: pulse 2s infinite;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #4f46e5, #7c3aed);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #4338ca, #6d28d9);
}

/* Smooth transitions */
* {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Focus styles */
input:focus {
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

/* Text gradient utility */
.text-gradient {
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
</style>