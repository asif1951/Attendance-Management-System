<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch, computed, onMounted } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import axios from 'axios';

// Define props to receive data from backend
const props = defineProps({
    courses: {
        type: Object,
        default: () => ({})
    },
    departments: {
        type: Array,
        default: () => []
    },
    academicStreams: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

// Get page instance for flash messages
const page = usePage();

// Modal states
const showModal = ref(false);
const showEditModal = ref(false);
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');
const selectedCourse = ref(null);

// Filter states
const filters = ref({
    search: props.filters.search || '',
    department_id: props.filters.department_id || '',
    level: props.filters.level || '',
    stream_id: props.filters.stream_id || ''
});

// Reactive streams based on selected department for filter
const filterStreams = ref([]);

// Reactive streams based on selected department for create form
const filteredStreams = ref([]);
const filteredEditStreams = ref([]);

// Available levels for courses
const availableLevels = [
    { id: '1/1', name: 'Level 1/1 (Year 1 Semester 1)' },
    { id: '1/2', name: 'Level 1/2 (Year 1 Semester 2)' },
    { id: '2/1', name: 'Level 2/1 (Year 2 Semester 1)' },
    { id: '2/2', name: 'Level 2/2 (Year 2 Semester 2)' },
    { id: '3/1', name: 'Level 3/1 (Year 3 Semester 1)' },
    { id: '3/2', name: 'Level 3/2 (Year 3 Semester 2)' },
    { id: '4/1', name: 'Level 4/1 (Year 4 Semester 1)' },
    { id: '4/2', name: 'Level 4/2 (Year 4 Semester 2)' },
    { id: '5/1', name: 'Level 5/1 (Year 5 Semester 1)' },
    { id: '5/2', name: 'Level 5/2 (Year 5 Semester 2)' }
];

// Form handling
const form = useForm({
    course_name: '',
    course_code: '',
    level: '',
    department_id: '',
    academic_stream_id: '',
});

const editForm = useForm({
    course_name: '',
    course_code: '',
    level: '',
    department_id: '',
    academic_stream_id: '',
});

// Watch filter changes and apply them
watch(filters, () => {
    applyFilters();
}, { deep: true });

// Watch department selection in filter
watch(() => filters.value.department_id, async (newDepartmentId) => {
    if (newDepartmentId) {
        try {
            const response = await axios.get(`/departments/${newDepartmentId}/streams`);
            filterStreams.value = response.data;
        } catch (error) {
            console.error('Error fetching streams:', error);
            filterStreams.value = props.academicStreams.filter(
                stream => stream.department_id == newDepartmentId
            );
        }
    } else {
        filterStreams.value = [];
    }
    // Reset stream filter when department changes
    filters.value.stream_id = '';
});

// Watch department selection in create form
watch(() => form.department_id, async (newDepartmentId) => {
    if (newDepartmentId) {
        try {
            const response = await axios.get(`/departments/${newDepartmentId}/streams`);
            filteredStreams.value = response.data;
        } catch (error) {
            console.error('Error fetching streams:', error);
            filteredStreams.value = props.academicStreams.filter(
                stream => stream.department_id == newDepartmentId
            );
        }
    } else {
        filteredStreams.value = [];
    }
    form.academic_stream_id = '';
});

// Watch department selection in edit form
watch(() => editForm.department_id, async (newDepartmentId) => {
    if (newDepartmentId) {
        try {
            const response = await axios.get(`/departments/${newDepartmentId}/streams`);
            filteredEditStreams.value = response.data;
        } catch (error) {
            console.error('Error fetching streams:', error);
            filteredEditStreams.value = props.academicStreams.filter(
                stream => stream.department_id == newDepartmentId
            );
        }
    } else {
        filteredEditStreams.value = [];
    }
});

// Apply filters
const applyFilters = () => {
    router.get(route('courses.index'), 
        {
            search: filters.value.search,
            department_id: filters.value.department_id,
            level: filters.value.level,
            stream_id: filters.value.stream_id
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
};

// Reset filters
const resetFilters = () => {
    filters.value = {
        search: '',
        department_id: '',
        level: '',
        stream_id: ''
    };
};

// Open modal functions
const openModal = () => {
    showModal.value = true;
    form.reset();
    form.clearErrors();
    filteredStreams.value = [];
};

const openEditModal = (course) => {
    selectedCourse.value = course;
    
    editForm.course_name = course.course_name;
    editForm.course_code = course.course_code;
    editForm.level = course.level || '';
    editForm.department_id = course.department_id || '';
    editForm.academic_stream_id = course.academic_stream_id || '';
    
    if (course.department_id) {
        filteredEditStreams.value = props.academicStreams.filter(
            stream => stream.department_id == course.department_id
        );
    } else {
        filteredEditStreams.value = [];
    }
    
    showEditModal.value = true;
    editForm.clearErrors();
};

// Close modal functions
const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
    filteredStreams.value = [];
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedCourse.value = null;
    editForm.reset();
    editForm.clearErrors();
    filteredEditStreams.value = [];
};

// Show toast function
const showSuccessToast = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
    
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};

// Get level display name
const getLevelDisplayName = (levelId) => {
    if (!levelId) return 'Not set';
    const level = availableLevels.find(l => l.id === levelId);
    return level ? level.name : levelId;
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

// Submit form - Create new course
const submitForm = () => {
    form.post(route('courses.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessToast('Course created successfully!');
            closeModal();
            filters.value = {
                search: '',
                department_id: '',
                level: '',
                stream_id: ''
            };
            router.get(route('courses.index'));
        },
        onError: (errors) => {
            console.log('Form errors:', errors);
            const firstError = Object.values(errors)[0];
            if (firstError) showSuccessToast(firstError, 'error');
        }
    });
};

// Update course
const updateCourse = () => {
    if (!selectedCourse.value) return;
    
    editForm.put(route('courses.update', selectedCourse.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessToast('Course updated successfully!');
            closeEditModal();
            filters.value = {
                search: '',
                department_id: '',
                level: '',
                stream_id: ''
            };
            router.get(route('courses.index'));
        },
        onError: (errors) => {
            console.log('Form errors:', errors);
            const firstError = Object.values(errors)[0];
            if (firstError) showSuccessToast(firstError, 'error');
        }
    });
};

// Delete course
const deleteCourse = (id) => {
    if (confirm('Are you sure you want to delete this course?')) {
        router.delete(route('courses.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                showSuccessToast('Course deleted successfully!');
                filters.value = {
                    search: '',
                    department_id: '',
                    level: '',
                    stream_id: ''
                };
                router.get(route('courses.index'));
            }
        });
    }
};

// Watch for flash messages
watch(() => page.props.flash, (newFlash) => {
    if (newFlash?.success) {
        showSuccessToast(newFlash.success);
    }
}, { deep: true });

// Change page
const changePage = (url) => {
    if (url) {
        router.get(url, {}, {
            preserveState: true,
            preserveScroll: true
        });
    }
};

onMounted(() => {
    console.log('Courses loaded:', props.courses.data?.length || 0);
    console.log('Departments:', props.departments.length);
    console.log('Streams:', props.academicStreams.length);
});
</script>

<template>
    <AuthenticatedLayout>
        <!-- Toast Notification -->
        <div v-if="showToast" class="fixed top-4 right-4 z-50 animate-slide-in">
            <div :class="[
                'px-6 py-4 rounded-2xl shadow-2xl flex items-center space-x-3 backdrop-blur-sm border border-white/20',
                toastType === 'success' ? 'bg-gradient-to-r from-emerald-500 to-teal-600' : 'bg-gradient-to-r from-red-500 to-rose-600'
            ]">
                <div class="bg-white/20 rounded-full p-1">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="toastType === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <span class="text-white font-medium">{{ toastMessage }}</span>
            </div>
        </div>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Hero Header Section with Gradient -->
                <div class="relative mb-8 overflow-hidden bg-gradient-to-br from-indigo-900 via-purple-800 to-pink-700 rounded-3xl shadow-2xl">
                    <div class="absolute inset-0 bg-black/20"></div>
                    <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 1px); background-size: 40px 40px;"></div>
                    <div class="relative p-8 backdrop-blur-sm">
                        <div class="flex justify-between items-center flex-wrap gap-4">
                            <div>
                                <div class="flex items-center space-x-3 mb-2">
                                    <div class="bg-white/20 p-2 rounded-xl backdrop-blur">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="text-3xl font-bold text-white tracking-tight">Course Management</h1>
                                        <p class="text-indigo-100 mt-1">Manage your academic courses by department</p>
                                    </div>
                                </div>
                            </div>
                            
                            <button
                                @click="openModal"
                                class="group relative inline-flex items-center px-6 py-3 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl font-semibold text-white shadow-lg hover:bg-white/20 hover:scale-105 transition-all duration-300 overflow-hidden"
                            >
                                <span class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></span>
                                <svg class="w-5 h-5 mr-2 relative z-10 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="relative z-10">Create Course</span>
                            </button>
                        </div>

                        <!-- Stats Row -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
                            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white/70 text-xs uppercase tracking-wider">Total Courses</p>
                                        <p class="text-white text-2xl font-bold">{{ courses.total || 0 }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white/70 text-xs uppercase tracking-wider">Departments</p>
                                        <p class="text-white text-2xl font-bold">{{ courses.data ? courses.data.length : 0 }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white/70 text-xs uppercase tracking-wider">With Stream</p>
                                        <p class="text-white text-2xl font-bold">{{ courses.total_with_stream || 0 }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white/70 text-xs uppercase tracking-wider">With Level</p>
                                        <p class="text-white text-2xl font-bold">{{ courses.total_with_level || 0 }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Section - Glassmorphism Card -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl mb-6 border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2">
                                <div class="p-1.5 bg-indigo-100 rounded-lg">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">Filter Courses</h3>
                            </div>
                            <button 
                                @click="resetFilters"
                                class="text-sm text-gray-500 hover:text-indigo-600 transition-colors flex items-center space-x-1 px-3 py-1.5 rounded-lg hover:bg-indigo-50"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Reset</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Search -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">🔍 Search</label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        v-model="filters.search" 
                                        placeholder="Search by name or code..."
                                        class="w-full px-4 py-2.5 pl-10 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                    >
                                    <svg class="absolute left-3 top-3 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Department Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">🏛️ Department</label>
                                <select 
                                    v-model="filters.department_id" 
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                >
                                    <option value="">All Departments</option>
                                    <option v-for="department in departments" :key="department.id" :value="department.id">
                                        {{ department.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Stream Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">📚 Stream</label>
                                <select 
                                    v-model="filters.stream_id" 
                                    :disabled="!filters.department_id"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <option value="">All Streams</option>
                                    <option v-for="stream in filterStreams" :key="stream.id" :value="stream.id">
                                        {{ stream.stream_name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Level Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">📊 Level</label>
                                <select 
                                    v-model="filters.level" 
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                >
                                    <option value="">All Levels</option>
                                    <option v-for="level in availableLevels" :key="level.id" :value="level.id">
                                        {{ level.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Courses Table Section -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                    <!-- Always show courses if they exist -->
                    <div v-if="courses.data && courses.data.length > 0">
                        <div v-for="group in courses.data" :key="group.department_id" class="mb-6 last:mb-0">
                            <!-- Department Header -->
                            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 px-6 py-4 border-b border-indigo-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center shadow-md">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-800">{{ group.department_name }}</h3>
                                            <p class="text-sm text-gray-500">{{ group.total_courses }} {{ group.total_courses === 1 ? 'course' : 'courses' }}</p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700">Department</span>
                                </div>
                            </div>

                            <!-- Courses Table -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Course Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Course Code</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Level</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Stream</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        <tr v-for="(course, index) in group.courses" :key="course.id" class="hover:bg-indigo-50/30 transition-colors duration-200 group">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-500">
                                                <span class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white w-7 h-7 rounded-lg inline-flex items-center justify-center text-xs">
                                                    {{ index + 1 }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-lg flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                        </svg>
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-800">{{ course.course_name }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-mono font-semibold bg-blue-100 text-blue-700">
                                                    {{ course.course_code }}
                                                </span>
                                             </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span v-if="course.level" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-purple-100 text-purple-700">
                                                    {{ getLevelDisplayName(course.level) }}
                                                </span>
                                                <span v-else class="text-gray-400 italic text-xs">Not set</span>
                                             </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span v-if="course.stream_name" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-green-100 text-green-700">
                                                    {{ course.stream_name }}
                                                </span>
                                                <span v-else class="text-gray-400 italic text-xs">Not assigned</span>
                                             </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="flex items-center space-x-1">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    <span>{{ formatDate(course.created_at) }}</span>
                                                </div>
                                             </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <button @click="openEditModal(course)" class="p-2 text-indigo-600 hover:bg-indigo-100 rounded-lg transition-all duration-200 hover:scale-110" title="Edit">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button>
                                                    <button @click="deleteCourse(course.id)" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-all duration-200 hover:scale-110" title="Delete">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                             </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="bg-gradient-to-br from-gray-100 to-gray-200 p-6 rounded-full mb-4">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" opacity="0.5" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Courses Found</h3>
                            <p class="text-gray-500 mb-6">Try adjusting your filters or create a new course.</p>
                            <button @click="openModal" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create Your First Course
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="courses.links && courses.links.length > 3" class="mt-6">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="text-sm text-gray-600">
                            Showing <span class="font-semibold text-indigo-600">{{ courses.from || 0 }}</span> to <span class="font-semibold text-indigo-600">{{ courses.to || 0 }}</span> of <span class="font-semibold text-indigo-600">{{ courses.total || 0 }}</span> results
                        </div>
                        <div class="flex space-x-2">
                            <template v-for="link in courses.links" :key="link.label">
                                <button
                                    v-if="link.url"
                                    @click="changePage(link.url)"
                                    :class="[
                                        'px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200',
                                        link.active 
                                            ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md' 
                                            : 'border border-gray-300 text-gray-700 hover:bg-gray-100'
                                    ]"
                                    v-html="link.label"
                                >
                                </button>
                                <span
                                    v-else
                                    class="px-4 py-2 rounded-xl text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed"
                                    v-html="link.label"
                                >
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Course Modal -->
        <div v-if="showModal" class="fixed inset-0 overflow-y-auto z-50">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="bg-white/20 p-2 rounded-xl">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-white">Create New Course</h3>
                            </div>
                            <button @click="closeModal" class="text-white/80 hover:text-white transition-colors text-2xl">&times;</button>
                        </div>
                    </div>
                    <div class="bg-white px-6 py-6">
                        <form @submit.prevent="submitForm">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📖 Course Name <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="form.course_name" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="e.g., Introduction to Programming">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">🔖 Course Code <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="form.course_code" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="e.g., CSE-101">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📊 Level <span class="text-red-500">*</span></label>
                                    <select v-model="form.level" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
                                        <option value="">Select Level</option>
                                        <option v-for="level in availableLevels" :key="level.id" :value="level.id">{{ level.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">🏛️ Department</label>
                                    <select v-model="form.department_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
                                        <option value="">Select Department</option>
                                        <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📚 Academic Stream</label>
                                    <select v-model="form.academic_stream_id" :disabled="!form.department_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <option value="">Select Stream</option>
                                        <option v-for="stream in filteredStreams" :key="stream.id" :value="stream.id">{{ stream.stream_name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-8 flex justify-end space-x-3">
                                <button type="button" @click="closeModal" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all duration-200">Cancel</button>
                                <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50">
                                    {{ form.processing ? 'Creating...' : 'Create Course' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Course Modal -->
        <div v-if="showEditModal" class="fixed inset-0 overflow-y-auto z-50">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="closeEditModal"></div>
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="bg-white/20 p-2 rounded-xl">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-white">Edit Course</h3>
                            </div>
                            <button @click="closeEditModal" class="text-white/80 hover:text-white transition-colors text-2xl">&times;</button>
                        </div>
                    </div>
                    <div class="bg-white px-6 py-6">
                        <form @submit.prevent="updateCourse">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📖 Course Name <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="editForm.course_name" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">🔖 Course Code <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="editForm.course_code" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📊 Level <span class="text-red-500">*</span></label>
                                    <select v-model="editForm.level" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200">
                                        <option value="">Select Level</option>
                                        <option v-for="level in availableLevels" :key="level.id" :value="level.id">{{ level.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">🏛️ Department</label>
                                    <select v-model="editForm.department_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200">
                                        <option value="">Select Department</option>
                                        <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📚 Academic Stream</label>
                                    <select v-model="editForm.academic_stream_id" :disabled="!editForm.department_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <option value="">Select Stream</option>
                                        <option v-for="stream in filteredEditStreams" :key="stream.id" :value="stream.id">{{ stream.stream_name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-8 flex justify-end space-x-3">
                                <button type="button" @click="closeEditModal" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all duration-200">Cancel</button>
                                <button type="submit" :disabled="editForm.processing" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50">
                                    {{ editForm.processing ? 'Updating...' : 'Update Course' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-slide-in {
    animation: slide-in 0.3s ease-out;
}

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
    background: linear-gradient(135deg, #6366f1, #a855f7);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #4f46e5, #9333ea);
}
</style>