<script setup>
import { ref, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

// usePage() hook ব্যবহার করে page props access করুন
const page = usePage();

// Safe access to auth
const auth = computed(() => page.props.auth || {});
const authUser = computed(() => auth.value?.user || null);

// Check if user is admin
const isAdmin = computed(() => {
    return authUser.value?.role === 'admin';
});

// Check if user is authenticated
const isAuthenticated = computed(() => {
    return !!authUser.value;
});

// Get user initial for avatar
const userInitial = computed(() => {
    if (!authUser.value?.name) return 'U';
    return authUser.value.name.charAt(0).toUpperCase();
});

// Check current route
const isCurrentRoute = (routeName) => {
    return page.url === routeName || page.url.startsWith(routeName + '/');
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 pt-16">
            <!-- Beautiful Gradient Navbar -->
            <nav class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 shadow-2xl fixed top-0 left-0 right-0 z-50 border-b border-white/20">
                <!-- Primary Navigation Menu -->
                <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-20">
                        <div class="flex items-center">
                            <!-- Enhanced AMS Logo -->
                            <div class="ml-4 flex items-center space-x-3 group">
                                <div class="relative">
                                    <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center shadow-lg ring-2 ring-white/30 group-hover:ring-white/50 transition-all duration-300">
                                        <span class="text-white font-bold text-lg">A</span>
                                    </div>
                                    <div class="absolute -inset-1 bg-gradient-to-r from-yellow-400 to-pink-500 rounded-xl opacity-0 group-hover:opacity-20 blur transition-all duration-500"></div>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-2xl font-extrabold text-white tracking-tight">
                                        AMS
                                    </span>
                                    <span class="text-[10px] text-white/80 font-medium tracking-wider uppercase">
                                        ACADEMIC MANAGEMENT
                                    </span>
                                </div>
                            </div>

                            <!-- Navigation Links with enhanced styling -->
                            <div class="hidden space-x-2 sm:-my-px sm:ms-12 sm:flex">
                                <!-- Dashboard link for admin -->
                                <NavLink 
                                    v-if="isAdmin"
                                    :href="route('dashboard')" 
                                    :active="isCurrentRoute('/dashboard')"
                                    class="relative group px-5 py-2.5 rounded-lg font-medium transition-all duration-300"
                                >
                                    <span class="relative z-10 text-white/90 hover:text-white">Dashboard</span>
                                    <span 
                                        class="absolute inset-0 bg-white/10 backdrop-blur-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                    ></span>
                                    <span 
                                        v-if="isCurrentRoute('/dashboard')"
                                        class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-8 h-1 bg-gradient-to-r from-yellow-300 to-pink-400 rounded-t-full"
                                    ></span>
                                    <span 
                                        class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-0.5 bg-gradient-to-r from-yellow-300 to-pink-400 rounded-full group-hover:w-8 transition-all duration-300"
                                    ></span>
                                </NavLink>
                                
                                <!-- Home link for regular users -->
                                <NavLink 
                                    v-else
                                    href="/" 
                                    :active="page.url === '/'"
                                    class="relative group px-5 py-2.5 rounded-lg font-medium transition-all duration-300"
                                >
                                    <span class="relative z-10 text-white/90 hover:text-white">Home</span>
                                    <span 
                                        class="absolute inset-0 bg-white/10 backdrop-blur-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                    ></span>
                                    <span 
                                        v-if="page.url === '/'"
                                        class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-8 h-1 bg-gradient-to-r from-yellow-300 to-pink-400 rounded-t-full"
                                    ></span>
                                    <span 
                                        class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-0.5 bg-gradient-to-r from-yellow-300 to-pink-400 rounded-full group-hover:w-8 transition-all duration-300"
                                    ></span>
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <!-- Settings Dropdown - Only show when user is authenticated -->
                            <div v-if="isAuthenticated" class="ms-3 relative">
                                <Dropdown align="right" width="52">
                                    <template #trigger>
                                        <div class="flex items-center space-x-3 group cursor-pointer">
                                            <div class="flex flex-col items-end">
                                                <span class="text-sm font-semibold text-white group-hover:text-yellow-200 transition-colors duration-200">
                                                    {{ authUser?.name || 'User' }}
                                                </span>
                                                <span class="text-xs text-white/70">
                                                    {{ isAdmin ? 'Administrator' : 'Student' }}
                                                </span>
                                            </div>
                                            <div class="relative">
                                                <div class="w-10 h-10 bg-gradient-to-br from-white/30 to-white/10 backdrop-blur-sm rounded-full flex items-center justify-center group-hover:from-white/40 group-hover:to-white/20 transition-all duration-300 shadow-sm ring-2 ring-white/40 group-hover:ring-white/60">
                                                    <span class="text-white font-semibold text-sm">
                                                        {{ userInitial }}
                                                    </span>
                                                </div>
                                                <div class="absolute -inset-1 bg-gradient-to-r from-yellow-400/30 to-pink-500/30 rounded-full opacity-0 group-hover:opacity-100 blur-sm transition-all duration-500"></div>
                                            </div>
                                            <svg
                                                class="w-4 h-4 text-white/70 group-hover:text-white transition-colors duration-200"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </div>
                                    </template>

                                    <template #content>
                                        <div class="py-2 px-4 border-b border-gray-100">
                                            <div class="text-xs text-gray-500 font-medium">ACCOUNT</div>
                                        </div>
                                        <DropdownLink 
                                            :href="route('profile.edit')" 
                                            class="flex items-center space-x-2 px-4 py-3 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 transition-colors duration-200"
                                        >
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span>Profile Settings</span>
                                        </DropdownLink>
                                        <div class="border-t border-gray-100 my-1"></div>
                                        <DropdownLink 
                                            :href="route('logout')" 
                                            method="post" 
                                            as="button"
                                            class="flex items-center space-x-2 px-4 py-3 text-red-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-pink-50 transition-colors duration-200"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            <span>Log Out</span>
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                            
                            <!-- Enhanced Login/Register links when NOT authenticated -->
                            <div v-else class="flex space-x-4 items-center">
                                <NavLink 
                                    :href="route('login')" 
                                    :active="isCurrentRoute('/login')"
                                    class="px-5 py-2.5 rounded-lg bg-white/20 backdrop-blur-sm text-white font-medium hover:bg-white/30 border border-white/30 hover:border-white/50 transition-all duration-300 hover:shadow-lg hover:shadow-white/20"
                                >
                                    Sign In
                                </NavLink>
                                <NavLink 
                                    :href="route('register')" 
                                    :active="isCurrentRoute('/register')"
                                    class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-yellow-400 to-pink-500 text-white font-medium hover:from-yellow-500 hover:to-pink-600 transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-pink-500/30"
                                >
                                    Get Started
                                </NavLink>
                            </div>
                        </div>

                        <!-- Enhanced Hamburger Menu -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-3 rounded-xl text-white hover:text-yellow-200 hover:bg-white/10 focus:outline-none transition-all duration-300 group bg-white/5 backdrop-blur-sm border border-white/20 hover:border-white/40"
                            >
                                <div class="relative w-6 h-6">
                                    <span 
                                        :class="{
                                            'rotate-45 translate-y-[5px] w-6': showingNavigationDropdown,
                                            '-translate-y-[5px] w-4': !showingNavigationDropdown
                                        }"
                                        class="absolute h-0.5 bg-current rounded-full transform transition-all duration-300 group-hover:w-6 group-hover:bg-gradient-to-r group-hover:from-yellow-300 group-hover:to-pink-400"
                                    ></span>
                                    <span 
                                        :class="{
                                            'opacity-0': showingNavigationDropdown,
                                            'opacity-100 w-6': !showingNavigationDropdown
                                        }"
                                        class="absolute h-0.5 bg-current rounded-full transform transition-all duration-300 group-hover:w-6 group-hover:bg-gradient-to-r group-hover:from-yellow-300 group-hover:to-pink-400"
                                    ></span>
                                    <span 
                                        :class="{
                                            '-rotate-45 -translate-y-[5px] w-6': showingNavigationDropdown,
                                            'translate-y-[5px] w-4': !showingNavigationDropdown
                                        }"
                                        class="absolute h-0.5 bg-current rounded-full transform transition-all duration-300 group-hover:w-6 group-hover:bg-gradient-to-r group-hover:from-yellow-300 group-hover:to-pink-400"
                                    ></span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Enhanced Responsive Navigation Menu -->
                <div 
                    :class="{ 
                        'max-h-96 opacity-100 visible': showingNavigationDropdown, 
                        'max-h-0 opacity-0 invisible': !showingNavigationDropdown 
                    }" 
                    class="sm:hidden overflow-hidden transition-all duration-500 bg-gradient-to-b from-purple-700/90 via-indigo-700/90 to-pink-700/90 backdrop-blur-md"
                >
                    <div class="pt-4 pb-3 space-y-1 px-4">
                        <!-- Dashboard link for admin -->
                        <ResponsiveNavLink 
                            v-if="isAdmin"
                            :href="route('dashboard')" 
                            :active="isCurrentRoute('/dashboard')"
                            class="rounded-xl px-4 py-3 bg-white/10 backdrop-blur-sm border border-white/20"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gradient-to-r from-yellow-400/20 to-pink-500/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                </div>
                                <span class="text-white font-medium">Dashboard</span>
                            </div>
                        </ResponsiveNavLink>
                        
                        <!-- Home link for regular users -->
                        <ResponsiveNavLink 
                            v-else
                            href="/" 
                            :active="page.url === '/'"
                            class="rounded-xl px-4 py-3 bg-white/10 backdrop-blur-sm border border-white/20"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gradient-to-r from-yellow-400/20 to-pink-500/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                </div>
                                <span class="text-white font-medium">Home</span>
                            </div>
                        </ResponsiveNavLink>
                    </div>
                    
                    <!-- Enhanced Responsive Settings Options -->
                    <div v-if="isAuthenticated" class="pt-4 pb-1 border-t border-white/20">
                        <div class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-white/30 to-white/10 backdrop-blur-sm rounded-full flex items-center justify-center ring-2 ring-white/40">
                                    <span class="text-white font-semibold text-lg">
                                        {{ userInitial }}
                                    </span>
                                </div>
                                <div>
                                    <div class="font-bold text-white text-sm">
                                        {{ authUser?.name || 'User' }}
                                    </div>
                                    <div v-if="authUser?.email" class="text-sm text-white/70">
                                        {{ authUser.email }}
                                    </div>
                                    <div class="text-xs text-white/50 mt-1">
                                        {{ isAdmin ? 'Administrator' : 'Student' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-2 space-y-1 px-4 pb-3">
                            <ResponsiveNavLink :href="route('profile.edit')" class="rounded-xl px-4 py-3 bg-white/10 backdrop-blur-sm border border-white/20">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-gradient-to-r from-blue-400/20 to-purple-500/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-white font-medium">Profile Settings</span>
                                </div>
                            </ResponsiveNavLink>
                            <ResponsiveNavLink 
                                :href="route('logout')" 
                                method="post" 
                                as="button"
                                class="rounded-xl px-4 py-3 bg-gradient-to-r from-red-500/20 to-pink-500/20 border border-red-400/30"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-gradient-to-r from-red-400/30 to-pink-500/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                    </div>
                                    <span class="text-white font-medium">Log Out</span>
                                </div>
                            </ResponsiveNavLink>
                        </div>
                    </div>
                    
                    <!-- Enhanced Login/Register in mobile when NOT authenticated -->
                    <div v-else class="pt-4 pb-1 border-t border-white/20">
                        <div class="px-6 py-4">
                            <div class="text-sm text-white/90 font-medium mb-3">ACCOUNT ACCESS</div>
                            <div class="space-y-2">
                                <ResponsiveNavLink 
                                    :href="route('login')" 
                                    :active="isCurrentRoute('/login')"
                                    class="rounded-xl px-4 py-3 bg-white/10 backdrop-blur-sm border border-white/20"
                                >
                                    Sign In
                                </ResponsiveNavLink>
                                <ResponsiveNavLink 
                                    :href="route('register')" 
                                    :active="isCurrentRoute('/register')"
                                    class="rounded-xl px-4 py-3 bg-gradient-to-r from-yellow-400 to-pink-500 text-white font-medium"
                                >
                                    Create Account
                                </ResponsiveNavLink>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main :class="{ 'flex': isAdmin && isAuthenticated }">
                <!-- SIDEBAR - Only for Admin with Beautiful Design -->
                <aside v-if="isAdmin && isAuthenticated" class="w-72 bg-gradient-to-b from-gray-900 via-gray-800 to-gray-900 min-h-screen text-white fixed left-0 top-20 bottom-0 shadow-2xl z-40 backdrop-blur-sm">
                    <!-- Sidebar Header with Gradient -->
                    <div class="relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-600/20 to-pink-600/20"></div>
                        <div class="relative p-6 border-b border-white/10">
                            <div class="flex items-center space-x-3">
                                <div class="relative">
                                    <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg ring-2 ring-white/30">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                        </svg>
                                    </div>
                                    <div class="absolute -inset-1 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl opacity-50 blur group-hover:opacity-100 transition-all duration-500"></div>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
                                        Admin Panel
                                    </h2>
                                    <p class="text-xs text-gray-400 mt-0.5">Management Dashboard</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Navigation with Beautiful Icons -->
                    <nav class="mt-6 overflow-y-auto h-[calc(100vh-9rem)] custom-scrollbar">
                        <div class="px-5 mb-4">
                            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center">
                                <div class="w-1 h-4 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full mr-2"></div>
                                MAIN NAVIGATION
                            </div>
                        </div>
                        <ul class="space-y-1.5 px-3 pb-6">
                            <li>
                                <Link 
                                    :href="route('departments.create')" 
                                    :active="isCurrentRoute('/departments/create')"
                                    class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group relative overflow-hidden"
                                    :class="isCurrentRoute('/departments/create') ? 'bg-gradient-to-r from-purple-600/40 to-pink-600/40 border border-purple-500/30' : 'hover:bg-white/10'"
                                >
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/20 to-pink-500/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative z-10 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-300 group-hover:scale-110" :class="isCurrentRoute('/departments/create') ? 'bg-gradient-to-r from-purple-500 to-pink-500' : 'bg-gradient-to-r from-purple-500/20 to-pink-500/20 group-hover:from-purple-500 group-hover:to-pink-500'">
                                        <svg class="w-4 h-4 transition-all duration-300" :class="isCurrentRoute('/departments/create') ? 'text-white' : 'text-gray-400 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <span class="relative z-10 font-medium transition-colors duration-300" :class="isCurrentRoute('/departments/create') ? 'text-white' : 'text-gray-300 group-hover:text-white'">Create Department</span>
                                </Link>
                            </li>

                            <!-- Create Class Arms -->
                            <li>
                                <Link 
                                    href="/class-arms/create" 
                                    :active="isCurrentRoute('/class-arms/create')"
                                    class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group relative overflow-hidden"
                                    :class="isCurrentRoute('/class-arms/create') ? 'bg-gradient-to-r from-blue-600/40 to-cyan-600/40 border border-blue-500/30' : 'hover:bg-white/10'"
                                >
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-cyan-500/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative z-10 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-300 group-hover:scale-110" :class="isCurrentRoute('/class-arms/create') ? 'bg-gradient-to-r from-blue-500 to-cyan-500' : 'bg-gradient-to-r from-blue-500/20 to-cyan-500/20 group-hover:from-blue-500 group-hover:to-cyan-500'">
                                        <svg class="w-4 h-4 transition-all duration-300" :class="isCurrentRoute('/class-arms/create') ? 'text-white' : 'text-gray-400 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                        </svg>
                                    </div>
                                    <span class="relative z-10 font-medium transition-colors duration-300" :class="isCurrentRoute('/class-arms/create') ? 'text-white' : 'text-gray-300 group-hover:text-white'">Create Class Arms</span>
                                </Link>
                            </li>

                            <!-- Create Course Teacher -->
                            <li>
                                <Link 
                                    href="/course-teachers/create" 
                                    :active="isCurrentRoute('/course-teachers/create')"
                                    class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group relative overflow-hidden"
                                    :class="isCurrentRoute('/course-teachers/create') ? 'bg-gradient-to-r from-green-600/40 to-emerald-600/40 border border-green-500/30' : 'hover:bg-white/10'"
                                >
                                    <div class="absolute inset-0 bg-gradient-to-r from-green-500/20 to-emerald-500/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative z-10 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-300 group-hover:scale-110" :class="isCurrentRoute('/course-teachers/create') ? 'bg-gradient-to-r from-green-500 to-emerald-500' : 'bg-gradient-to-r from-green-500/20 to-emerald-500/20 group-hover:from-green-500 group-hover:to-emerald-500'">
                                        <svg class="w-4 h-4 transition-all duration-300" :class="isCurrentRoute('/course-teachers/create') ? 'text-white' : 'text-gray-400 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <span class="relative z-10 font-medium transition-colors duration-300" :class="isCurrentRoute('/course-teachers/create') ? 'text-white' : 'text-gray-300 group-hover:text-white'">Create Course Teacher</span>
                                </Link>
                            </li>

                            <li>
                                <Link 
                                    href="/courses/create" 
                                    :active="isCurrentRoute('/courses/create')"
                                    class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group relative overflow-hidden"
                                    :class="isCurrentRoute('/courses/create') ? 'bg-gradient-to-r from-yellow-600/40 to-amber-600/40 border border-yellow-500/30' : 'hover:bg-white/10'"
                                >
                                    <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/20 to-amber-500/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative z-10 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-300 group-hover:scale-110" :class="isCurrentRoute('/courses/create') ? 'bg-gradient-to-r from-yellow-500 to-amber-500' : 'bg-gradient-to-r from-yellow-500/20 to-amber-500/20 group-hover:from-yellow-500 group-hover:to-amber-500'">
                                        <svg class="w-4 h-4 transition-all duration-300" :class="isCurrentRoute('/courses/create') ? 'text-white' : 'text-gray-400 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <span class="relative z-10 font-medium transition-colors duration-300" :class="isCurrentRoute('/courses/create') ? 'text-white' : 'text-gray-300 group-hover:text-white'">Create Course</span>
                                </Link>
                            </li>

                            <li>
                                <Link 
                                    href="/students/create" 
                                    :active="isCurrentRoute('/students/create')"
                                    class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group relative overflow-hidden"
                                    :class="isCurrentRoute('/students/create') ? 'bg-gradient-to-r from-red-600/40 to-orange-600/40 border border-red-500/30' : 'hover:bg-white/10'"
                                >
                                    <div class="absolute inset-0 bg-gradient-to-r from-red-500/20 to-orange-500/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative z-10 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-300 group-hover:scale-110" :class="isCurrentRoute('/students/create') ? 'bg-gradient-to-r from-red-500 to-orange-500' : 'bg-gradient-to-r from-red-500/20 to-orange-500/20 group-hover:from-red-500 group-hover:to-orange-500'">
                                        <svg class="w-4 h-4 transition-all duration-300" :class="isCurrentRoute('/students/create') ? 'text-white' : 'text-gray-400 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                        </svg>
                                    </div>
                                    <span class="relative z-10 font-medium transition-colors duration-300" :class="isCurrentRoute('/students/create') ? 'text-white' : 'text-gray-300 group-hover:text-white'">Add Student</span>
                                </Link>
                            </li>

                            <!-- User Management Divider -->
                            <li class="pt-3 mt-2">
                                <div class="px-4 py-2">
                                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center">
                                        <div class="w-1 h-3 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full mr-2"></div>
                                        USER MANAGEMENT
                                    </div>
                                </div>
                            </li>

                            <li>
                                <Link 
                                    href="/user" 
                                    :active="isCurrentRoute('/user')"
                                    class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 group relative overflow-hidden"
                                    :class="isCurrentRoute('/user') ? 'bg-gradient-to-r from-indigo-600/40 to-purple-600/40 border border-indigo-500/30' : 'hover:bg-white/10'"
                                >
                                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/20 to-purple-500/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative z-10 w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-300 group-hover:scale-110" :class="isCurrentRoute('/user') ? 'bg-gradient-to-r from-indigo-500 to-purple-500' : 'bg-gradient-to-r from-indigo-500/20 to-purple-500/20 group-hover:from-indigo-500 group-hover:to-purple-500'">
                                        <svg class="w-4 h-4 transition-all duration-300" :class="isCurrentRoute('/user') ? 'text-white' : 'text-gray-400 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <span class="relative z-10 font-medium transition-colors duration-300" :class="isCurrentRoute('/user') ? 'text-white' : 'text-gray-300 group-hover:text-white'">User Management</span>
                                </Link>
                            </li>
                        </ul>
                    </nav>
                </aside>
                
                <!-- CONTENT - With conditional margin -->
                <div :class="{
                    'flex-1 p-6 ml-72': isAdmin && isAuthenticated,
                    'w-full py-12': !isAdmin || !isAuthenticated
                }">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style>
/* Smooth animations */
* {
    transition: background-color 0.3s ease, border-color 0.3s ease, transform 0.3s ease, opacity 0.3s ease, width 0.3s ease;
}

/* Custom scrollbar for sidebar */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #7c3aed #1f2937;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #1f2937;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #8b5cf6, #ec4899);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #a78bfa, #f472b6);
}

/* Smooth dropdown transitions */
.max-h-0 {
    max-height: 0;
}

.max-h-96 {
    max-height: 24rem;
}

/* Glass morphism effect */
.backdrop-blur-md {
    backdrop-filter: blur(12px);
}

/* Ensure content doesn't get hidden behind fixed navbar */
@media (max-width: 640px) {
    .min-h-screen {
        padding-top: 0 !important;
    }
    
    /* Hide sidebar on mobile for admin */
    aside.fixed {
        position: relative !important;
        width: 100% !important;
        height: auto !important;
        top: 0 !important;
    }
    
    /* Remove margin on mobile */
    .ml-72 {
        margin-left: 0 !important;
        width: 100% !important;
    }
}
</style>