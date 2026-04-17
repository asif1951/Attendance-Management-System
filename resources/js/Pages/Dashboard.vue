<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

const page = usePage();

// Props
const props = defineProps({
    auth: Object,
    stats: {
        type: Object,
        default: () => ({})
    },
    recent_students: {
        type: Array,
        default: () => []
    },
    recent_courses: {
        type: Array,
        default: () => []
    },
    department_stats: {
        type: Array,
        default: () => []
    },
    level_stats: {
        type: Array,
        default: () => []
    },
    session_stats: {
        type: Array,
        default: () => []
    }
});

// Check if user is admin
const isAdmin = computed(() => {
    return page.props.auth?.user?.role === 'admin';
});

// If user is not admin, redirect to home page
onMounted(() => {
    if (!isAdmin.value && page.props.auth?.user) {
        router.visit('/');
    }
});

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

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <div v-if="isAdmin" class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Welcome Banner -->
                <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl mb-8 overflow-hidden">
                    <div class="relative p-8">
                        <div class="absolute top-0 right-0 opacity-10">
                            <svg class="w-64 h-64" fill="white" viewBox="0 0 24 24">
                                <path d="M12 2L15 8.5L22 9.5L17 14L18.5 21L12 17.5L5.5 21L7 14L2 9.5L9 8.5L12 2z"/>
                            </svg>
                        </div>
                        <div class="relative z-10">
                            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">
                                Welcome back, {{ auth?.user?.name }}!
                            </h1>
                            <p class="text-white/80 text-lg">
                                Here's what's happening with your academic management system today.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Total Students</p>
                                <p class="text-3xl font-bold text-gray-800 mt-2">{{ stats.total_students || 0 }}</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Total Courses</p>
                                <p class="text-3xl font-bold text-gray-800 mt-2">{{ stats.total_courses || 0 }}</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-purple-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Departments</p>
                                <p class="text-3xl font-bold text-gray-800 mt-2">{{ stats.total_departments || 0 }}</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-orange-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Academic Streams</p>
                                <p class="text-3xl font-bold text-gray-800 mt-2">{{ stats.total_academic_streams || 0 }}</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Second Row Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-r from-red-500 to-pink-500 rounded-2xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white/80 text-sm font-medium">Course Teachers</p>
                                <p class="text-3xl font-bold mt-2">{{ stats.total_course_teachers || 0 }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-cyan-500 to-blue-500 rounded-2xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white/80 text-sm font-medium">Total Users</p>
                                <p class="text-3xl font-bold mt-2">{{ stats.total_users || 0 }}</p>
                                <p class="text-xs mt-1">👑 {{ stats.total_admins || 0 }} Admins | 👤 {{ stats.total_regular_users || 0 }} Users</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white/80 text-sm font-medium">Attendance Rate</p>
                                <p class="text-3xl font-bold mt-2">{{ stats.attendance_percentage || 0 }}%</p>
                                <p class="text-xs mt-1">✅ {{ stats.present_count || 0 }} Present</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-purple-500 to-indigo-500 rounded-2xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white/80 text-sm font-medium">Total Attendances</p>
                                <p class="text-3xl font-bold mt-2">{{ stats.total_attendances || 0 }}</p>
                                <p class="text-xs mt-1">❌ {{ stats.absent_count || 0 }} Absent | ⏰ {{ stats.late_count || 0 }} Late</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section - Department Statistics -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Department-wise Student Distribution -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <span class="w-2 h-6 bg-blue-500 rounded-full mr-3"></span>
                            Department-wise Students
                        </h3>
                        <div class="space-y-4">
                            <div v-for="dept in department_stats" :key="dept.name" class="flex items-center">
                                <div class="w-32 text-sm font-medium text-gray-600">{{ dept.name }}</div>
                                <div class="flex-1 ml-4">
                                    <div class="relative">
                                        <div class="overflow-hidden h-8 text-xs flex rounded-lg bg-gray-200">
                                            <div :style="{ width: (dept.total_students / (stats.total_students || 1)) * 100 + '%' }" 
                                                 class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-blue-500 to-purple-600">
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4 text-sm font-bold text-gray-700">{{ dept.total_students }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Level-wise Student Distribution -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <span class="w-2 h-6 bg-green-500 rounded-full mr-3"></span>
                            Level-wise Students
                        </h3>
                        <div class="space-y-4">
                            <div v-for="level in level_stats" :key="level.level" class="flex items-center">
                                <div class="w-20 text-sm font-medium text-gray-600">{{ level.level || 'Not Set' }}</div>
                                <div class="flex-1 ml-4">
                                    <div class="relative">
                                        <div class="overflow-hidden h-8 text-xs flex rounded-lg bg-gray-200">
                                            <div :style="{ width: (level.total / (stats.total_students || 1)) * 100 + '%' }" 
                                                 class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-green-500 to-emerald-600">
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4 text-sm font-bold text-gray-700">{{ level.total }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Session Statistics -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Session-wise Student Distribution -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <span class="w-2 h-6 bg-orange-500 rounded-full mr-3"></span>
                            Session-wise Students
                        </h3>
                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            <div v-for="session in session_stats" :key="session.session" class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition">
                                <span class="text-sm font-medium text-gray-600">{{ session.session }}</span>
                                <span class="text-sm font-bold text-gray-800">{{ session.total }} students</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats Summary -->
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white">
                        <h3 class="text-lg font-bold mb-4 flex items-center">
                            <span class="w-2 h-6 bg-white rounded-full mr-3"></span>
                            System Overview
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white/10 rounded-xl p-3 backdrop-blur-sm">
                                <div class="text-2xl font-bold">{{ stats.total_students || 0 }}</div>
                                <div class="text-xs mt-1 opacity-80">Total Students</div>
                            </div>
                            <div class="bg-white/10 rounded-xl p-3 backdrop-blur-sm">
                                <div class="text-2xl font-bold">{{ stats.total_courses || 0 }}</div>
                                <div class="text-xs mt-1 opacity-80">Total Courses</div>
                            </div>
                            <div class="bg-white/10 rounded-xl p-3 backdrop-blur-sm">
                                <div class="text-2xl font-bold">{{ stats.total_departments || 0 }}</div>
                                <div class="text-xs mt-1 opacity-80">Departments</div>
                            </div>
                            <div class="bg-white/10 rounded-xl p-3 backdrop-blur-sm">
                                <div class="text-2xl font-bold">{{ stats.total_academic_streams || 0 }}</div>
                                <div class="text-xs mt-1 opacity-80">Academic Streams</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Students Table -->
                <div class="bg-white rounded-2xl shadow-lg mb-8 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <span class="w-2 h-6 bg-purple-500 rounded-full mr-3"></span>
                            Recent Students
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Session</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Added</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="student in recent_students" :key="student.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ student.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ student.student_id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ student.department_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ student.level || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ student.session || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(student.created_at) }}</td>
                                </tr>
                                <tr v-if="recent_students.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">No students found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Courses Table -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <span class="w-2 h-6 bg-green-500 rounded-full mr-3"></span>
                            Recent Courses
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Added</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="course in recent_courses" :key="course.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ course.course_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ course.course_code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ course.department_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ course.level || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(course.created_at) }}</td>
                                </tr>
                                <tr v-if="recent_courses.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No courses found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Access Denied for Non-Admin -->
        <div v-else class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-900">
                        <div class="text-6xl mb-4">🚫</div>
                        <h3 class="text-2xl font-bold mb-2">Access Denied</h3>
                        <p class="text-gray-600 mb-6">You do not have permission to access the admin dashboard.</p>
                        <a href="/" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700 transition">
                            Go to Home Page
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>