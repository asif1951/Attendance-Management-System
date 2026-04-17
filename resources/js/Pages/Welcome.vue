<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const page = usePage();
const props = defineProps({
    auth: Object,
    assignedCourses: {
        type: Array,
        default: () => []
    },
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
    attendanceStats: {
        type: Object,
        default: () => ({})
    },
    recentAttendances: {
        type: Array,
        default: () => []
    },
    systemStats: {
        type: Object,
        default: () => ({})
    },
    todayAttendanceRate: {
        type: Number,
        default: 0
    },
    departments: {
        type: Array,
        default: () => []
    }
});

const stats = ref([
    { label: 'Total Students', value: props.systemStats?.total_students || '0', icon: '👨‍🎓', change: '+12%', color: 'from-blue-500 to-cyan-500' },
    { label: 'Active Courses', value: props.systemStats?.total_courses || '0', icon: '📖', change: '+5%', color: 'from-green-500 to-emerald-500' },
    { label: 'Faculty Members', value: props.systemStats?.total_teachers || '0', icon: '👨‍🏫', change: '+8%', color: 'from-purple-500 to-pink-500' },
    { label: 'Attendance Today', value: `${props.todayAttendanceRate || 0}%`, icon: '📊', change: '+2%', color: 'from-orange-500 to-red-500' }
]);

const animatedValue = ref(0);
const showMyCourses = ref(false);
const showAttendanceStats = ref(true);

// Check if user is admin
const isAdmin = computed(() => {
    return page.props.auth?.user?.role === 'admin';
});

// Check if user is logged in
const isLoggedIn = computed(() => {
    return !!page.props.auth?.user;
});

// Redirect admin to dashboard if they somehow land here
onMounted(() => {
    if (isAdmin.value && page.props.auth?.user) {
        router.visit('/dashboard');
    }
    
    // Counter animation
    const interval = setInterval(() => {
        if (animatedValue.value < (props.attendanceStats?.present_percentage || 0)) {
            animatedValue.value += 1;
        } else {
            clearInterval(interval);
        }
    }, 30);
});

// Use assigned courses from database
const userCourses = ref(props.assignedCourses || []);

// Get current user from auth
const currentUser = computed(() => {
    if (page.props.auth?.user) {
        return {
            name: page.props.auth.user.name,
            id: `TCH-${page.props.auth.user.id}`,
            department: page.props.auth.user.department || 'Computer Science & Engineering',
            semester: page.props.auth.user.level || '5th Semester',
            avatar: page.props.auth.user.name.charAt(0) + (page.props.auth.user.name.split(' ')[1]?.charAt(0) || ''),
            role: page.props.auth.user.role,
            email: page.props.auth.user.email
        };
    }
    
    return {
        name: 'Guest User',
        id: 'STD-0000',
        department: 'Not Logged In',
        semester: 'N/A',
        avatar: 'GU',
        role: 'guest',
        email: ''
    };
});

// Function to calculate progress color
const getProgressColor = (progress) => {
    if (progress >= 80) return 'bg-gradient-to-r from-green-400 to-green-600';
    if (progress >= 60) return 'bg-gradient-to-r from-blue-400 to-blue-600';
    if (progress >= 40) return 'bg-gradient-to-r from-yellow-400 to-yellow-600';
    return 'bg-gradient-to-r from-red-400 to-red-600';
};

// Function to get course color based on course id
const getCourseColor = (courseId) => {
    const colors = ['blue', 'green', 'purple', 'orange', 'pink', 'indigo', 'red', 'teal'];
    return colors[courseId % colors.length];
};

// Function to get course icon based on course code
const getCourseIcon = (courseCode) => {
    if (!courseCode) return '📘';
    
    if (courseCode.includes('CSE') || courseCode.includes('CS')) return '💻';
    if (courseCode.includes('MAT') || courseCode.includes('MATH')) return '📐';
    if (courseCode.includes('ENG') || courseCode.includes('ENGLISH')) return '📚';
    if (courseCode.includes('PHY') || courseCode.includes('PHYSICS')) return '⚛️';
    if (courseCode.includes('CHEM')) return '🧪';
    if (courseCode.includes('EEE') || courseCode.includes('ELECTRICAL')) return '⚡';
    if (courseCode.includes('BBA') || courseCode.includes('BUSINESS')) return '📊';
    
    return '📘';
};

// Get status badge color
const getStatusColor = (status) => {
    switch(status) {
        case 'present': return 'bg-green-100 text-green-700';
        case 'absent': return 'bg-red-100 text-red-700';
        case 'late': return 'bg-yellow-100 text-yellow-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};

// Get status icon
const getStatusIcon = (status) => {
    switch(status) {
        case 'present': return '✅';
        case 'absent': return '❌';
        case 'late': return '⏰';
        default: return '📝';
    }
};

// Format date
const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
};
</script>

<template>
    <Head title="Welcome to AMS" />

    <AuthenticatedLayout>
        <!-- Animated Background -->
        <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100"></div>
            <div class="absolute top-0 -left-40 w-80 h-80 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
            <div class="absolute top-0 -right-40 w-80 h-80 bg-yellow-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-40 left-20 w-80 h-80 bg-pink-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
        </div>

        <!-- Hero Section with 3D Effect -->
        <div class="relative overflow-hidden rounded-3xl shadow-2xl mb-8 transform hover:scale-[1.01] transition-all duration-500">
            <!-- Background Image with Overlay -->
            <div 
                class="absolute inset-0 bg-cover bg-center"
                style="background-image: url('https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');"
            >
                <div class="absolute inset-0 bg-gradient-to-r from-blue-900/90 via-purple-900/85 to-pink-900/90"></div>
            </div>
            
            <!-- Animated Particles -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute top-20 left-10 w-2 h-2 bg-white rounded-full animate-ping"></div>
                <div class="absolute top-40 right-20 w-3 h-3 bg-white rounded-full animate-ping animation-delay-1000"></div>
                <div class="absolute bottom-20 left-1/4 w-2 h-2 bg-white rounded-full animate-ping animation-delay-2000"></div>
                <div class="absolute top-1/2 right-1/3 w-4 h-4 bg-white rounded-full animate-pulse-slow"></div>
            </div>
            
            <!-- Hero Content -->
            <div class="relative z-10 px-8 py-20 md:px-12 lg:px-20">
                <!-- University Badge -->
                <div class="inline-flex items-center bg-white/20 backdrop-blur-md px-6 py-3 rounded-full mb-8 animate-pulse border border-white/30">
                    <span class="text-white text-sm font-medium">🏛️ RTM Al-Kabir Technical University</span>
                    <span class="ml-3 w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                </div>
                
                <!-- Main Hero Text -->
                <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 leading-tight animate-fade-in">
                    Welcome to 
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-300 via-purple-300 to-pink-300 animate-gradient">
                        AMS
                    </span>
                </h1>
                
                <p class="text-xl text-gray-200 mb-8 max-w-2xl animate-slide-up leading-relaxed">
                    Empowering Excellence in Education through Smart Attendance Management System
                </p>
                
                <!-- User Welcome Message -->
                <div v-if="isLoggedIn" class="mb-8 animate-slide-up">
                    <div class="inline-flex items-center bg-white/20 backdrop-blur-md px-6 py-3 rounded-full border border-white/30">
                        <span class="text-white text-sm font-medium">👋 Welcome back, {{ currentUser.name }}!</span>
                        <span class="ml-3 text-xs bg-gradient-to-r from-green-500 to-emerald-600 text-white px-3 py-1 rounded-full">
                            {{ currentUser.role === 'admin' ? 'Administrator' : 'Teacher' }}
                        </span>
                    </div>
                </div>
                
                <!-- Hero Stats -->
                <div class="flex flex-wrap gap-4 mb-8">
                    <div class="bg-white/10 backdrop-blur-md rounded-xl px-5 py-3 border border-white/20 transform hover:scale-105 transition-all duration-300">
                        <div class="text-2xl font-bold text-white">{{ departments.length }}</div>
                        <div class="text-sm text-gray-300">Departments</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-xl px-5 py-3 border border-white/20 transform hover:scale-105 transition-all duration-300">
                        <div class="text-2xl font-bold text-white">{{ systemStats.total_students || 0 }}+</div>
                        <div class="text-sm text-gray-300">Students</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-xl px-5 py-3 border border-white/20 transform hover:scale-105 transition-all duration-300">
                        <div class="text-2xl font-bold text-white">{{ systemStats.total_teachers || 0 }}+</div>
                        <div class="text-sm text-gray-300">Faculty</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-xl px-5 py-3 border border-white/20 transform hover:scale-105 transition-all duration-300">
                        <div class="text-2xl font-bold text-white">{{ systemStats.total_courses || 0 }}+</div>
                        <div class="text-sm text-gray-300">Courses</div>
                    </div>
                </div>
                
                <!-- CTA Button -->
                <button v-if="!isLoggedIn" class="group bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 text-white px-10 py-4 rounded-xl font-semibold hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 animate-bounce-slow">
                    🚀 Get Started
                    <span class="inline-block transition-transform group-hover:translate-x-1">→</span>
                </button>
                <button 
                    v-else 
                    @click="showMyCourses = !showMyCourses"
                    class="group bg-gradient-to-r from-green-500 via-blue-500 to-purple-500 text-white px-10 py-4 rounded-xl font-semibold hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 animate-bounce-slow"
                >
                    📚 {{ showMyCourses ? 'Hide' : 'View' }} My Courses
                    <span class="inline-block transition-transform group-hover:translate-x-1">→</span>
                </button>
            </div>
            
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl animate-pulse-slow"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl animate-pulse-slow animation-delay-2000"></div>
        </div>

        <!-- Main Content -->
        <div class="space-y-8">
            <!-- My Courses Section (Only for logged in users) -->
            <div v-if="isLoggedIn && userCourses.length > 0" class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl p-8 border border-white/50">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                            <div class="relative">
                                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                                    <span class="text-white text-2xl font-bold">📚</span>
                                </div>
                                <div class="absolute -top-2 -right-2 w-7 h-7 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center shadow-md">
                                    <span class="text-white text-xs font-bold">{{ userCourses.length }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center gap-3">
                                    <span>My Teaching Portfolio</span>
                                    <span class="text-sm font-normal bg-gradient-to-r from-blue-100 to-purple-100 text-blue-700 px-4 py-1.5 rounded-full">
                                        {{ userCourses.length }} Courses Assigned
                                    </span>
                                </div>
                                <p class="text-gray-600 mt-2">Manage your courses and track student attendance</p>
                            </div>
                        </h2>
                    </div>
                    <button 
                        @click="showMyCourses = !showMyCourses"
                        class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-2"
                    >
                        <span>{{ showMyCourses ? 'Hide' : 'View' }} Courses</span>
                        <span class="text-lg">{{ showMyCourses ? '⬆️' : '📖' }}</span>
                    </button>
                </div>

                <!-- Courses Display -->
                <div v-if="showMyCourses" class="animate-fade-in-up">
                    <!-- Teacher Info Card -->
                    <div class="bg-gradient-to-r from-blue-50 via-purple-50 to-pink-50 rounded-2xl p-6 mb-8 border border-gray-200 shadow-lg">
                        <div class="flex items-center gap-5">
                            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg transform rotate-3">
                                <span class="text-white text-3xl font-bold">{{ currentUser.avatar }}</span>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-2xl font-bold text-gray-800">{{ currentUser.name }}</h3>
                                <div class="flex flex-wrap gap-4 mt-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-600">📧 Email:</span>
                                        <span class="font-semibold text-blue-700">{{ currentUser.email }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-600">🏛️ Department:</span>
                                        <span class="font-semibold text-purple-700">{{ currentUser.department || 'Not Assigned' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-600">👔 Role:</span>
                                        <span class="font-semibold text-orange-700">
                                            {{ currentUser.role === 'admin' ? 'Administrator' : 'Teacher' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-5xl animate-float">🍎</div>
                        </div>
                    </div>

                    <!-- Courses Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div 
                            v-for="course in userCourses" 
                            :key="course.id"
                            class="rounded-2xl p-6 transform hover:scale-105 transition-all duration-300 hover:shadow-2xl cursor-pointer group"
                            :class="{
                                'bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-200': getCourseColor(course.id) === 'blue',
                                'bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-200': getCourseColor(course.id) === 'green',
                                'bg-gradient-to-br from-purple-50 to-purple-100 border-2 border-purple-200': getCourseColor(course.id) === 'purple',
                                'bg-gradient-to-br from-orange-50 to-orange-100 border-2 border-orange-200': getCourseColor(course.id) === 'orange',
                                'bg-gradient-to-br from-pink-50 to-pink-100 border-2 border-pink-200': getCourseColor(course.id) === 'pink',
                                'bg-gradient-to-br from-indigo-50 to-indigo-100 border-2 border-indigo-200': getCourseColor(course.id) === 'indigo',
                                'bg-gradient-to-br from-red-50 to-red-100 border-2 border-red-200': getCourseColor(course.id) === 'red',
                                'bg-gradient-to-br from-teal-50 to-teal-100 border-2 border-teal-200': getCourseColor(course.id) === 'teal'
                            }"
                        >
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <div class="text-3xl mb-2 group-hover:scale-110 transition-transform duration-300">{{ getCourseIcon(course.course_code) }}</div>
                                    <div class="text-xs font-semibold px-3 py-1 rounded-full"
                                        :class="{
                                            'bg-blue-200 text-blue-800': getCourseColor(course.id) === 'blue',
                                            'bg-green-200 text-green-800': getCourseColor(course.id) === 'green',
                                            'bg-purple-200 text-purple-800': getCourseColor(course.id) === 'purple',
                                            'bg-orange-200 text-orange-800': getCourseColor(course.id) === 'orange',
                                            'bg-pink-200 text-pink-800': getCourseColor(course.id) === 'pink',
                                            'bg-indigo-200 text-indigo-800': getCourseColor(course.id) === 'indigo',
                                            'bg-red-200 text-red-800': getCourseColor(course.id) === 'red',
                                            'bg-teal-200 text-teal-800': getCourseColor(course.id) === 'teal'
                                        }">
                                        {{ course.course_code || 'N/A' }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-gray-800">{{ course.progress || '0' }}%</div>
                                    <div class="text-xs text-gray-600">Completion</div>
                                </div>
                            </div>
                            
                            <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-2">{{ course.course_name || 'Unnamed Course' }}</h3>
                            
                            <!-- Course Details -->
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center gap-2" v-if="course.level">
                                    <span class="text-gray-500">📊</span>
                                    <span class="text-sm text-gray-700">Level: {{ course.level }}</span>
                                </div>
                                <div class="flex items-center gap-2" v-if="course.department_name">
                                    <span class="text-gray-500">🏛️</span>
                                    <span class="text-sm text-gray-700">{{ course.department_name }}</span>
                                </div>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="mb-3">
                                <div class="flex justify-between text-xs text-gray-600 mb-1">
                                    <span>Course Progress</span>
                                    <span>{{ course.progress || '0' }}%</span>
                                </div>
                                <div class="w-full bg-white/60 rounded-full h-2.5 overflow-hidden">
                                    <div class="h-2.5 rounded-full transition-all duration-1000 ease-out"
                                        :class="getProgressColor(course.progress || 0)"
                                        :style="`width: ${course.progress || 0}%`"></div>
                                </div>
                            </div>
                            
                            <!-- Action Button -->
                            <Link 
                                :href="route('course.details', course.id)"
                                class="w-full mt-4 py-2.5 rounded-xl font-medium text-sm transition-all duration-300 flex items-center justify-center gap-2 group-hover:shadow-md"
                                :class="{
                                    'bg-blue-100 text-blue-700 hover:bg-blue-200': getCourseColor(course.id) === 'blue',
                                    'bg-green-100 text-green-700 hover:bg-green-200': getCourseColor(course.id) === 'green',
                                    'bg-purple-100 text-purple-700 hover:bg-purple-200': getCourseColor(course.id) === 'purple',
                                    'bg-orange-100 text-orange-700 hover:bg-orange-200': getCourseColor(course.id) === 'orange',
                                    'bg-pink-100 text-pink-700 hover:bg-pink-200': getCourseColor(course.id) === 'pink',
                                    'bg-indigo-100 text-indigo-700 hover:bg-indigo-200': getCourseColor(course.id) === 'indigo',
                                    'bg-red-100 text-red-700 hover:bg-red-200': getCourseColor(course.id) === 'red',
                                    'bg-teal-100 text-teal-700 hover:bg-teal-200': getCourseColor(course.id) === 'teal'
                                }"
                            >
                                Manage Course →
                            </Link>
                        </div>
                    </div>

                    <!-- Teaching Summary -->
                    <div class="mt-8 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-2xl p-6 text-white shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold mb-2">📊 Teaching Summary</h3>
                                <p class="text-blue-100">Your semester overview and teaching load</p>
                            </div>
                            <div class="text-4xl animate-pulse">🎯</div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                            <div class="text-center bg-white/10 backdrop-blur-sm rounded-xl p-3">
                                <div class="text-3xl font-bold">{{ userCourses.length }}</div>
                                <div class="text-sm text-blue-100">Total Courses</div>
                            </div>
                            <div class="text-center bg-white/10 backdrop-blur-sm rounded-xl p-3">
                                <div class="text-3xl font-bold">
                                    {{ Math.round(userCourses.reduce((acc, course) => acc + (course.progress || 0), 0) / userCourses.length) || 0 }}%
                                </div>
                                <div class="text-sm text-blue-100">Avg Progress</div>
                            </div>
                            <div class="text-center bg-white/10 backdrop-blur-sm rounded-xl p-3">
                                <div class="text-3xl font-bold">{{ userCourses.length * 4 }}h</div>
                                <div class="text-sm text-blue-100">Weekly Hours</div>
                            </div>
                            <div class="text-center bg-white/10 backdrop-blur-sm rounded-xl p-3">
                                <div class="text-3xl font-bold">{{ attendanceStats.total_records || 0 }}</div>
                                <div class="text-sm text-blue-100">Attendance Records</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Statistics Section -->
            <div v-if="isLoggedIn && attendanceStats.total_records > 0" class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl p-8 border border-white/50">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                            <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-lg">
                                <span class="text-white text-2xl">📊</span>
                            </div>
                            <div>
                                <span>Attendance Analytics</span>
                                <p class="text-gray-600 text-sm mt-1">Real-time attendance tracking and insights</p>
                            </div>
                        </h2>
                    </div>
                    <button 
                        @click="showAttendanceStats = !showAttendanceStats"
                        class="text-gray-600 hover:text-gray-800 transition-colors"
                    >
                        <span class="text-2xl">{{ showAttendanceStats ? '▲' : '▼' }}</span>
                    </button>
                </div>

                <div v-if="showAttendanceStats" class="animate-fade-in-up">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-5 border border-green-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-3xl">✅</div>
                                <div class="text-2xl font-bold text-green-600">{{ attendanceStats.present_percentage || 0 }}%</div>
                            </div>
                            <div class="text-lg font-semibold text-gray-800">Present</div>
                            <div class="text-2xl font-bold text-green-700">{{ attendanceStats.present || 0 }}</div>
                            <div class="text-sm text-gray-500 mt-2">Total present records</div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-red-50 to-rose-50 rounded-2xl p-5 border border-red-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-3xl">❌</div>
                                <div class="text-2xl font-bold text-red-600">{{ attendanceStats.absent_percentage || 0 }}%</div>
                            </div>
                            <div class="text-lg font-semibold text-gray-800">Absent</div>
                            <div class="text-2xl font-bold text-red-700">{{ attendanceStats.absent || 0 }}</div>
                            <div class="text-sm text-gray-500 mt-2">Total absent records</div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl p-5 border border-yellow-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-3xl">⏰</div>
                                <div class="text-2xl font-bold text-yellow-600">{{ attendanceStats.late_percentage || 0 }}%</div>
                            </div>
                            <div class="text-lg font-semibold text-gray-800">Late</div>
                            <div class="text-2xl font-bold text-yellow-700">{{ attendanceStats.late || 0 }}</div>
                            <div class="text-sm text-gray-500 mt-2">Total late records</div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-5 border border-blue-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-3xl">📈</div>
                                <div class="text-2xl font-bold text-blue-600">{{ attendanceStats.present_percentage || 0 }}%</div>
                            </div>
                            <div class="text-lg font-semibold text-gray-800">Overall Rate</div>
                            <div class="text-2xl font-bold text-blue-700">{{ attendanceStats.total_records || 0 }}</div>
                            <div class="text-sm text-gray-500 mt-2">Total records</div>
                        </div>
                    </div>

                    <!-- Overall Attendance Progress -->
                    <div class="mb-8">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700 font-semibold">Overall Attendance Rate</span>
                            <span class="font-bold text-blue-600">{{ attendanceStats.present_percentage || 0 }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                            <div class="bg-gradient-to-r from-green-400 to-blue-500 h-4 rounded-full transition-all duration-1000 ease-out" 
                                 :style="`width: ${animatedValue}%`"></div>
                        </div>
                    </div>

                    <!-- Recent Attendance Records -->
                    <div v-if="recentAttendances.length > 0">
                        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <span>🕒 Recent Attendance Records</span>
                            <span class="text-sm font-normal text-gray-500">(Last 10 entries)</span>
                        </h3>
                        <div class="overflow-x-auto rounded-xl">
                            <table class="min-w-full bg-white rounded-xl overflow-hidden">
                                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Student</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Course</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Percentage</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="record in recentAttendances" :key="record.id" class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ formatDate(record.date) }}</td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-800">{{ record.first_name }} {{ record.last_name }}</div>
                                            <div class="text-xs text-gray-500">{{ record.student_roll }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm text-gray-800">{{ record.course_name }}</div>
                                            <div class="text-xs text-gray-500">{{ record.course_code }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold" :class="getStatusColor(record.status)">
                                                {{ getStatusIcon(record.status) }} {{ record.status.toUpperCase() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-semibold text-gray-700">{{ record.percentage || 0 }}%</span>
                                                <div class="flex-1 max-w-20 bg-gray-200 rounded-full h-1.5">
                                                    <div class="bg-blue-500 h-1.5 rounded-full" :style="`width: ${record.percentage || 0}%`"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Courses Message -->
            <div v-if="isLoggedIn && userCourses.length === 0" class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl p-8 border border-white/50">
                <div class="text-center py-12">
                    <div class="text-7xl mb-4 animate-bounce">📚</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">No Courses Assigned</h3>
                    <p class="text-gray-600 mb-6">You don't have any courses assigned to you yet. Please contact the administrator.</p>
                    <button 
                        @click="showMyCourses = !showMyCourses"
                        class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-8 py-3 rounded-xl font-semibold hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300"
                    >
                        {{ showMyCourses ? 'Hide' : 'View' }} My Courses
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div 
                    v-for="(stat, index) in stats" 
                    :key="index"
                    class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 transform hover:-translate-y-2 transition-all duration-300 hover:shadow-2xl animate-fade-in border border-white/50 group cursor-pointer"
                    :style="`animation-delay: ${index * 100}ms`"
                >
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-300"
                             :class="`bg-gradient-to-r ${stat.color} bg-opacity-10`">
                            {{ stat.icon }}
                        </div>
                        <div class="text-sm font-semibold text-green-600 bg-green-50 px-3 py-1 rounded-full">
                            {{ stat.change }}
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-800 mb-1">{{ stat.value }}</div>
                    <div class="text-sm text-gray-600">{{ stat.label }}</div>
                </div>
            </div>

            <!-- Departments Section -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl p-8 border border-white/50">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
                            🏛️ Academic Departments
                            <span class="text-sm font-normal text-gray-500">({{ departments.length }} Departments)</span>
                        </h2>
                        <p class="text-gray-600 mt-2">RTM Al-Kabir Technical University offers excellence across all disciplines</p>
                    </div>
                    <div class="text-5xl animate-float">🎓</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="(dept, index) in departments" 
                        :key="dept.id"
                        class="rounded-2xl p-6 transform hover:scale-105 transition-all duration-300 hover:shadow-2xl cursor-pointer group"
                        :class="{
                            'bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-200': index % 5 === 0,
                            'bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-200': index % 5 === 1,
                            'bg-gradient-to-br from-purple-50 to-purple-100 border-2 border-purple-200': index % 5 === 2,
                            'bg-gradient-to-br from-yellow-50 to-yellow-100 border-2 border-yellow-200': index % 5 === 3,
                            'bg-gradient-to-br from-pink-50 to-pink-100 border-2 border-pink-200': index % 5 === 4
                        }"
                        :style="`animation-delay: ${index * 200}ms`"
                    >
                        <div class="flex items-start justify-between mb-4">
                            <div class="text-4xl group-hover:scale-110 transition-transform duration-300">
                                {{ index % 5 === 0 ? '💻' : index % 5 === 1 ? '⚡' : index % 5 === 2 ? '📊' : index % 5 === 3 ? '📚' : '👗' }}
                            </div>
                            <span class="text-sm font-semibold px-3 py-1 rounded-full"
                                :class="{
                                    'bg-blue-100 text-blue-800': index % 5 === 0,
                                    'bg-green-100 text-green-800': index % 5 === 1,
                                    'bg-purple-100 text-purple-800': index % 5 === 2,
                                    'bg-yellow-100 text-yellow-800': index % 5 === 3,
                                    'bg-pink-100 text-pink-800': index % 5 === 4
                                }">
                                {{ dept.student_count || 0 }} Students
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">{{ dept.name }}</h3>
                        <p class="text-sm text-gray-600 mb-4">State-of-the-art facilities with experienced faculty members</p>
                        <div class="w-full bg-white/60 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full transition-all duration-1000 ease-out"
                                :class="{
                                    'bg-blue-500': index % 5 === 0,
                                    'bg-green-500': index % 5 === 1,
                                    'bg-purple-500': index % 5 === 2,
                                    'bg-yellow-500': index % 5 === 3,
                                    'bg-pink-500': index % 5 === 4
                                }"
                                :style="`width: ${((dept.student_count || 0) / 500) * 100}%`"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Card -->
                <div class="bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 rounded-3xl shadow-2xl p-8 text-white transform hover:-rotate-1 transition-all duration-300 group">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="text-5xl group-hover:scale-110 transition-transform duration-300">📱</div>
                        <div>
                            <h3 class="text-2xl font-bold">Digital Transformation</h3>
                            <p class="text-blue-100">Modern Education Management</p>
                        </div>
                    </div>
                    <p class="text-blue-100 mb-6 leading-relaxed">
                        Our smart attendance system revolutionizes traditional methods with real-time tracking, 
                        automated reports, and instant notifications for faculty and students.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2 text-sm">Real-time Tracking</div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2 text-sm">Automated Reports</div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2 text-sm">Instant Alerts</div>
                    </div>
                </div>

                <!-- Right Card -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl p-8 transform hover:rotate-1 transition-all duration-300 border border-white/50 group">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="text-5xl group-hover:scale-110 transition-transform duration-300">🎯</div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800">Academic Excellence</h3>
                            <p class="text-gray-600">Our Mission & Vision</p>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        RTM Al-Kabir Technical University is committed to providing world-class education, 
                        fostering innovation, and preparing students for global challenges.
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-3">
                            <div class="text-3xl font-bold text-blue-600">98%</div>
                            <div class="text-sm text-gray-600">Student Satisfaction</div>
                        </div>
                        <div class="text-center bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-3">
                            <div class="text-3xl font-bold text-green-600">92%</div>
                            <div class="text-sm text-gray-600">Placement Rate</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Performance -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl p-8 border border-white/50">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <span>📈 System Performance</span>
                        <span class="text-sm font-normal text-gray-500">(Real-time metrics)</span>
                    </h3>
                    <div class="text-4xl animate-spin-slow">🔄</div>
                </div>
                <div class="space-y-5">
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700 font-semibold">Attendance Tracking Accuracy</span>
                            <span class="font-bold text-blue-600">{{ attendanceStats.present_percentage || animatedValue }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-green-400 via-blue-500 to-purple-500 h-3 rounded-full transition-all duration-1000 ease-out" 
                                 :style="`width: ${attendanceStats.present_percentage || animatedValue}%`"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700 font-semibold">System Uptime</span>
                            <span class="font-bold text-green-600">99.9%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-400 to-cyan-500 h-3 rounded-full transition-all duration-1000" style="width: 99.9%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700 font-semibold">Database Performance</span>
                            <span class="font-bold text-purple-600">98.5%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-400 to-pink-500 h-3 rounded-full transition-all duration-1000" style="width: 98.5%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Section -->
            <div class="text-center py-12">
                <div class="text-7xl mb-4 animate-bounce">🎓</div>
                <h4 class="text-3xl font-bold text-gray-800 mb-3">
                    RTM Al-Kabir Technical University
                </h4>
                <p class="text-gray-600 mb-6 text-lg">
                    Where Excellence Meets Innovation in Technical Education
                </p>
                <div class="flex justify-center gap-8 text-sm text-gray-500">
                    <span class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse"></span>
                        Smart Campus
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-blue-500 rounded-full animate-pulse"></span>
                        Digital Learning
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-purple-500 rounded-full animate-pulse"></span>
                        Global Standards
                    </span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(50px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes pulseSlow {
    0%, 100% { opacity: 0.2; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(1.05); }
}

@keyframes bounceSlow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-12px); }
}

@keyframes spinSlow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes blob {
    0%, 100% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

@keyframes gradient {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.animate-fade-in {
    animation: fadeIn 0.8s ease-out forwards;
}

.animate-slide-up {
    animation: slideUp 0.8s ease-out 0.2s both;
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out both;
}

.animate-pulse-slow {
    animation: pulseSlow 4s ease-in-out infinite;
}

.animate-bounce-slow {
    animation: bounceSlow 2.5s ease-in-out infinite;
}

.animate-spin-slow {
    animation: spinSlow 10s linear infinite;
}

.animate-blob {
    animation: blob 7s infinite;
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}

.animate-gradient {
    background-size: 200% 200%;
    animation: gradient 3s ease infinite;
}

.animation-delay-1000 {
    animation-delay: 1s;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

/* Line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46a0 100%);
}

/* Glass effect enhancements */
.backdrop-blur-sm {
    backdrop-filter: blur(12px);
}

.backdrop-blur-md {
    backdrop-filter: blur(16px);
}

/* Smooth transitions */
* {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>