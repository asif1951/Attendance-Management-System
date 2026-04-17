<template>
    <AuthenticatedLayout>
        <!-- Toast Notification -->
        <div v-if="showToast" class="fixed top-4 right-4 z-50 animate-slide-in">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center space-x-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="font-medium">{{ toastMessage }}</span>
            </div>
        </div>

        <!-- Loading Overlay -->
        <div v-if="isLoading" class="fixed inset-0 bg-black bg-opacity-30 z-50 flex items-center justify-center backdrop-blur-sm">
            <div class="bg-white rounded-2xl p-6 flex items-center space-x-3 shadow-2xl">
                <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-700 font-semibold text-lg">Loading...</span>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Hero Header Section -->
                <div class="mb-8">
                    <div class="bg-gradient-to-br from-blue-600 via-cyan-500 to-teal-500 rounded-2xl shadow-2xl overflow-hidden">
                        <div class="relative px-8 py-12">
                            <!-- Background Pattern -->
                            <div class="absolute inset-0 opacity-10">
                                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" patternUnits="userSpaceOnUse" width="40" height="40">
                                    <rect width="40" height="40" fill="none"/>
                                    <circle cx="20" cy="20" r="1" fill="white"/>
                                </svg>
                            </div>
                            
                            <div class="relative z-10">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="flex items-center space-x-3 mb-3">
                                            <div class="bg-white/20 backdrop-blur-lg rounded-xl p-2">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h1 class="text-4xl font-bold text-white tracking-tight">Academic Streams</h1>
                                                <p class="text-white/90 mt-1 text-lg">Manage academic streams for departments</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Create Button -->
                                    <button
                                        @click="openModal"
                                        class="group relative inline-flex items-center px-6 py-3 bg-white text-blue-600 font-semibold rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200"
                                    >
                                        <svg class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Create Academic Stream
                                    </button>
                                </div>
                                
                                <!-- Stats -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
                                    <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4">
                                        <p class="text-white/80 text-sm">Total Streams</p>
                                        <p class="text-white text-2xl font-bold">{{ academicStreams.total || 0 }}</p>
                                    </div>
                                    <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4">
                                        <p class="text-white/80 text-sm">Departments</p>
                                        <p class="text-white text-2xl font-bold">{{ departments.length || 0 }}</p>
                                    </div>
                                    <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4">
                                        <p class="text-white/80 text-sm">Active Streams</p>
                                        <p class="text-white text-2xl font-bold">{{ academicStreams.data?.length || 0 }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search & Filter Section -->
                <div class="mb-6">
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <!-- Search Input -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">🔍 Search Streams</label>
                                    <div class="relative group">
                                        <input
                                            type="text"
                                            v-model="filters.search"
                                            placeholder="Search by stream name or department..."
                                            class="w-full px-4 py-3 pl-12 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200"
                                        />
                                        <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400 group-focus-within:text-blue-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Department Filter -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">🏛 Department</label>
                                    <select
                                        v-model="filters.department_id"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200"
                                    >
                                        <option value="">All Departments</option>
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                            {{ dept.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">📊 Status</label>
                                    <select
                                        v-model="filters.status"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200"
                                    >
                                        <option value="">All Status</option>
                                        <option value="Assigned">✅ Assigned</option>
                                        <option value="UnAssigned">⏳ UnAssigned</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Reset Filters Button -->
                            <div class="mt-4 flex justify-end">
                                <button
                                    @click="resetFilters"
                                    class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 text-white font-semibold rounded-xl hover:from-gray-700 hover:to-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 transform hover:-translate-y-0.5"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Academic Streams Table Section -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">📚 All Academic Streams</h2>
                                <p class="text-sm text-gray-600 mt-1">List of all academic streams by department</p>
                            </div>
                            <div class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold">
                                {{ academicStreams.total || 0 }} Total
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        SL
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Department Name
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Academic Stream Name
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Created Date
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="academicStreams.data?.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="bg-gradient-to-br from-blue-100 to-cyan-100 rounded-full p-6 mb-4">
                                                <svg class="w-16 h-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            </div>
                                            <h3 class="text-xl font-bold text-gray-800 mb-2">No Academic Streams Yet</h3>
                                            <p class="text-gray-600 mb-6">Get started by creating your first academic stream.</p>
                                            <button
                                                @click="openModal"
                                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200"
                                            >
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                Create Your First Stream
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="(stream, index) in academicStreams.data" :key="stream.id" class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 transition-all duration-200 group">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        <span class="bg-gradient-to-r from-blue-600 to-cyan-600 text-white w-8 h-8 rounded-lg inline-flex items-center justify-center text-sm">
                                            {{ (academicStreams.current_page - 1) * academicStreams.per_page + index + 1 }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl p-2">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-semibold text-gray-900">{{ stream.department_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span class="text-sm font-medium text-gray-800">{{ stream.stream_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            :class="{
                                                'bg-gradient-to-r from-green-100 to-emerald-100 text-green-800': stream.status === 'Assigned',
                                                'bg-gradient-to-r from-yellow-100 to-orange-100 text-yellow-800': stream.status === 'UnAssigned'
                                            }"
                                            class="px-3 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{ stream.status === 'Assigned' ? '✅ Assigned' : '⏳ UnAssigned' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span>{{ formatDate(stream.created_at) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <!-- Edit Button -->
                                            <button
                                                @click="openEditModal(stream)"
                                                class="group relative inline-flex items-center px-3 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-xs font-semibold rounded-lg hover:from-blue-600 hover:to-blue-700 transform hover:-translate-y-0.5 transition-all duration-200 shadow-md"
                                            >
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                            
                                            <!-- Delete Button -->
                                            <button
                                                @click="deleteStream(stream)"
                                                class="group relative inline-flex items-center px-3 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs font-semibold rounded-lg hover:from-red-600 hover:to-red-700 transform hover:-translate-y-0.5 transition-all duration-200 shadow-md"
                                            >
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div v-if="academicStreams.last_page > 1" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div class="text-sm text-gray-700">
                                Showing <span class="font-semibold">{{ academicStreams.from }}</span> to <span class="font-semibold">{{ academicStreams.to }}</span> of <span class="font-semibold">{{ academicStreams.total }}</span> results
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    @click="goToPage(academicStreams.current_page - 1)"
                                    :disabled="academicStreams.current_page === 1"
                                    class="px-4 py-2 border-2 border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                                >
                                    ← Previous
                                </button>
                                <template v-for="page in academicStreams.last_page" :key="page">
                                    <button
                                        v-if="Math.abs(page - academicStreams.current_page) <= 2 || page === 1 || page === academicStreams.last_page"
                                        @click="goToPage(page)"
                                        :class="[
                                            'px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200',
                                            page === academicStreams.current_page
                                                ? 'bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-md transform scale-105'
                                                : 'border-2 border-gray-300 text-gray-700 hover:bg-gray-100'
                                        ]"
                                    >
                                        {{ page }}
                                    </button>
                                    <span
                                        v-else-if="page === academicStreams.current_page - 3 || page === academicStreams.current_page + 3"
                                        class="px-2 py-2 text-gray-500"
                                    >
                                        ...
                                    </span>
                                </template>
                                <button
                                    @click="goToPage(academicStreams.current_page + 1)"
                                    :disabled="academicStreams.current_page === academicStreams.last_page"
                                    class="px-4 py-2 border-2 border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                                >
                                    Next →
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Table Footer -->
                    <div v-if="academicStreams.data?.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                Showing <span class="font-semibold text-blue-600">{{ academicStreams.data?.length || 0 }}</span> academic streams
                            </div>
                            <div class="text-sm text-gray-600">
                                Total: <span class="font-semibold text-blue-600">{{ academicStreams.total || 0 }}</span> streams
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Creating Academic Stream -->
        <div v-if="showModal" class="fixed inset-0 overflow-y-auto z-50">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

            <!-- Modal Container -->
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Modal Content -->
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="bg-white/20 backdrop-blur-lg rounded-xl p-2">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-white">Create Academic Stream</h3>
                            </div>
                            <button
                                @click="closeModal"
                                class="text-white hover:text-gray-200 focus:outline-none transition-colors duration-200"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="bg-white px-6 py-6">
                        <form @submit.prevent="submitForm">
                            <div class="space-y-6">
                                <!-- Select Department -->
                                <div>
                                    <label for="department_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                        🏛 Select Department <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="department_id"
                                        v-model="form.department_id"
                                        required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200"
                                        :class="{ 'border-red-500': form.errors.department_id }"
                                    >
                                        <option value="" disabled>Choose a department</option>
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                            {{ dept.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.department_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.department_id }}
                                    </p>
                                </div>

                                <!-- Academic Stream Name -->
                                <div>
                                    <label for="stream_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                        📖 Academic Stream Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="stream_name"
                                        v-model="form.stream_name"
                                        required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200"
                                        placeholder="e.g., Day Shift, Evening Shift, Regular Program"
                                        :class="{ 'border-red-500': form.errors.stream_name }"
                                    />
                                    <p v-if="form.errors.stream_name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.stream_name }}
                                    </p>
                                    <p class="mt-2 text-sm text-gray-500 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Enter the name of the academic stream
                                    </p>
                                </div>

                                <!-- Example Stream Names -->
                                <div class="bg-gradient-to-r from-blue-50 to-cyan-50 p-4 rounded-xl">
                                    <p class="text-sm font-semibold text-gray-700 mb-3">💡 Examples:</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <span class="text-xs bg-white px-3 py-2 rounded-lg border border-blue-200 text-gray-700 hover:shadow-md transition-shadow duration-200">🌅 Day Shift</span>
                                        <span class="text-xs bg-white px-3 py-2 rounded-lg border border-blue-200 text-gray-700 hover:shadow-md transition-shadow duration-200">🌙 Evening Shift</span>
                                        <span class="text-xs bg-white px-3 py-2 rounded-lg border border-blue-200 text-gray-700 hover:shadow-md transition-shadow duration-200">📚 Regular Program</span>
                                        <span class="text-xs bg-white px-3 py-2 rounded-lg border border-blue-200 text-gray-700 hover:shadow-md transition-shadow duration-200">🎓 Weekend Program</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="mt-8 flex justify-end space-x-3">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="px-5 py-2.5 border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-cyan-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
                                >
                                    <span v-if="form.processing" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Creating...
                                    </span>
                                    <span v-else class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Create Stream
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Editing Academic Stream -->
        <div v-if="showEditModal" class="fixed inset-0 overflow-y-auto z-50">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm transition-opacity" @click="closeEditModal"></div>

            <!-- Modal Container -->
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Modal Content -->
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="bg-white/20 backdrop-blur-lg rounded-xl p-2">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-white">Edit Academic Stream</h3>
                            </div>
                            <button
                                @click="closeEditModal"
                                class="text-white hover:text-gray-200 focus:outline-none transition-colors duration-200"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="bg-white px-6 py-6">
                        <form @submit.prevent="submitEditForm">
                            <div class="space-y-6">
                                <!-- Current Department Info -->
                                <div v-if="selectedStream" class="bg-gradient-to-r from-blue-50 to-cyan-50 p-4 rounded-xl">
                                    <p class="text-sm font-semibold text-gray-700 mb-2">📋 Department Information</p>
                                    <p class="text-lg font-bold text-gray-900">{{ selectedStream.department_name }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Department cannot be changed</p>
                                </div>

                                <!-- Hidden Department ID -->
                                <input type="hidden" v-model="editForm.department_id" />

                                <!-- Academic Stream Name -->
                                <div>
                                    <label for="edit_stream_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                        📖 Academic Stream Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="edit_stream_name"
                                        v-model="editForm.stream_name"
                                        required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none transition-all duration-200"
                                        placeholder="e.g., Day Shift, Evening Shift, Regular Program"
                                        :class="{ 'border-red-500': editForm.errors.stream_name }"
                                    />
                                    <p v-if="editForm.errors.stream_name" class="mt-1 text-sm text-red-600">
                                        {{ editForm.errors.stream_name }}
                                    </p>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="edit_status" class="block text-sm font-semibold text-gray-700 mb-2">
                                        📊 Status <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="edit_status"
                                        v-model="editForm.status"
                                        required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none transition-all duration-200"
                                        :class="{ 'border-red-500': editForm.errors.status }"
                                    >
                                        <option value="UnAssigned">⏳ UnAssigned</option>
                                        <option value="Assigned">✅ Assigned</option>
                                    </select>
                                    <p v-if="editForm.errors.status" class="mt-1 text-sm text-red-600">
                                        {{ editForm.errors.status }}
                                    </p>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="mt-8 flex justify-end space-x-3">
                                <button
                                    type="button"
                                    @click="closeEditModal"
                                    class="px-5 py-2.5 border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="editForm.processing"
                                    class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold rounded-xl hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
                                >
                                    <span v-if="editForm.processing" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Updating...
                                    </span>
                                    <span v-else class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Update Stream
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

// Define props
const props = defineProps({
    departments: {
        type: Array,
        default: () => []
    },
    academicStreams: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

// Loading state
const isLoading = ref(false);

// Filter state
const filters = ref({
    search: props.filters.search || '',
    department_id: props.filters.department_id || '',
    status: props.filters.status || ''
});

// Modal states
const showModal = ref(false);
const showEditModal = ref(false);
const showToast = ref(false);
const toastMessage = ref('');
const selectedStream = ref(null);

// Debounce function
let debounceTimeout = null;
const debounce = (fn, delay) => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(fn, delay);
};

// Apply filters
const applyFilters = () => {
    debounce(() => {
        isLoading.value = true;
        router.get(route('class-arms.create'), filters.value, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                isLoading.value = false;
            }
        });
    }, 300);
};

// Reset filters
const resetFilters = () => {
    filters.value = {
        search: '',
        department_id: '',
        status: ''
    };
    applyFilters();
};

// Watch filters
watch(() => filters.value.search, () => applyFilters());
watch(() => filters.value.department_id, () => applyFilters());
watch(() => filters.value.status, () => applyFilters());

// Pagination
const goToPage = (page) => {
    if (props.academicStreams.current_page === page) return;
    isLoading.value = true;
    router.get(route('class-arms.create'), { ...filters.value, page: page }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

// Form handling
const form = useForm({
    department_id: '',
    stream_name: ''
});

// Edit Form handling
const editForm = useForm({
    department_id: '',
    stream_name: '',
    status: ''
});

// Open modal
const openModal = () => {
    showModal.value = true;
};

// Close modal
const closeModal = () => {
    showModal.value = false;
    form.reset();
};

// Open edit modal
const openEditModal = (stream) => {
    selectedStream.value = stream;
    
    // Find department id from departments array
    const department = props.departments.find(dept => dept.name === stream.department_name);
    
    editForm.department_id = department ? department.id : '';
    editForm.stream_name = stream.stream_name;
    editForm.status = stream.status;
    showEditModal.value = true;
};

// Close edit modal
const closeEditModal = () => {
    showEditModal.value = false;
    selectedStream.value = null;
    editForm.reset();
};

// Show toast function
const showSuccessToast = (message) => {
    toastMessage.value = message;
    showToast.value = true;
    
    // Auto hide after 3 seconds
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};

// Submit form - Create new academic stream
const submitForm = () => {
    form.post(route('class-arms.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessToast('Academic stream created successfully!');
            closeModal();
        },
        onError: () => {
            // Handle errors if needed
        }
    });
};

// Submit edit form
const submitEditForm = () => {
    if (!selectedStream.value) return;
    
    editForm.put(route('class-arms.update', selectedStream.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessToast('Academic stream updated successfully!');
            closeEditModal();
        },
        onError: () => {
            // Handle errors if needed
        }
    });
};

// Delete stream
const deleteStream = (stream) => {
    if (confirm('Are you sure you want to delete this academic stream?')) {
        router.delete(route('class-arms.destroy', stream.id), {
            preserveScroll: true,
            onSuccess: () => {
                showSuccessToast('Academic stream deleted successfully!');
            },
            onError: () => {
                // Handle errors if needed
            }
        });
    }
};

// Format date
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        return 'Invalid Date';
    }
};
</script>

<style scoped>
/* Smooth modal transition */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

/* Smooth backdrop blur transition */
.backdrop-blur-sm {
    transition: backdrop-filter 0.3s ease;
}

/* Toast animation */
@keyframes slide-in {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.animate-slide-in {
    animation: slide-in 0.3s ease-out;
}

/* Table row hover effect */
tr {
    transition: all 0.2s ease;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #3b82f6, #06b6d4);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #2563eb, #0891b2);
}
</style>