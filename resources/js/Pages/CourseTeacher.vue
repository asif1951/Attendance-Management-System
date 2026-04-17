<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch, computed, onMounted } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    courseTeachers: {
        type: Object,
        default: () => ({})
    },
    users: {
        type: Array,
        default: () => []
    },
    departments: {
        type: Array,
        default: () => []
    },
    academicStreams: {
        type: Array,
        default: () => []
    },
    courses: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const page = usePage();
const showModal = ref(false);
const showEditModal = ref(false);
const showToast = ref(false);
const toastMessage = ref('');
const selectedTeacher = ref(null);

// Filter state
const filters = ref({
    search: props.filters.search || '',
    department_id: props.filters.department_id || '',
    academic_stream_id: props.filters.academic_stream_id || '',
    level: props.filters.level || '',
    session: props.filters.session || ''
});

// Available levels
const availableLevels = [
    { id: '1/1', name: 'Level 1/1' },
    { id: '1/2', name: 'Level 1/2' },
    { id: '2/1', name: 'Level 2/1' },
    { id: '2/2', name: 'Level 2/2' },
    { id: '3/1', name: 'Level 3/1' },
    { id: '3/2', name: 'Level 3/2' },
    { id: '4/1', name: 'Level 4/1' },
    { id: '4/2', name: 'Level 4/2' },
    { id: '5/1', name: 'Level 5/1' },
    { id: '5/2', name: 'Level 5/2' }
];

// Reactive data
const filteredStreams = ref([]);
const filteredEditStreams = ref([]);
const filteredCourses = ref([]);
const filteredEditCourses = ref([]);

// Forms
const form = useForm({
    user_id: '',
    phone_number: '',
    department_id: '',
    academic_stream_id: '',
    level: '',
    course_id: '',
    session: ''
});

const editForm = useForm({
    user_id: '',
    phone_number: '',
    department_id: '',
    academic_stream_id: '',
    level: '',
    course_id: '',
    session: ''
});

// Debounce function
let debounceTimeout = null;
const debounce = (fn, delay) => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(fn, delay);
};

// Apply filters
const applyFilters = () => {
    debounce(() => {
        router.get(route('course-teachers.index'), filters.value, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300);
};

// Reset filters
const resetFilters = () => {
    filters.value = {
        search: '',
        department_id: '',
        academic_stream_id: '',
        level: '',
        session: ''
    };
    applyFilters();
};

// Watch filters
watch(() => filters.value.search, () => applyFilters());
watch(() => filters.value.department_id, () => {
    filters.value.academic_stream_id = '';
    applyFilters();
});
watch(() => filters.value.academic_stream_id, () => applyFilters());
watch(() => filters.value.level, () => applyFilters());
watch(() => filters.value.session, () => applyFilters());

// Watch for user selection to get email
watch(() => form.user_id, async (userId) => {
    if (userId) {
        try {
            const response = await axios.get(`/get-email-by-user/${userId}`);
            console.log('User email:', response.data.email);
        } catch (error) {
            console.error('Error fetching email:', error);
        }
    }
});

// Watch for edit user selection
watch(() => editForm.user_id, async (userId) => {
    if (userId) {
        try {
            const response = await axios.get(`/get-email-by-user/${userId}`);
            console.log('User email:', response.data.email);
        } catch (error) {
            console.error('Error fetching email:', error);
        }
    }
});

// Watch department selection for streams
watch(() => form.department_id, async (departmentId) => {
    if (departmentId) {
        try {
            const response = await axios.get(`/departments/${departmentId}/streams`);
            filteredStreams.value = response.data;
        } catch (error) {
            console.error('Error fetching streams:', error);
            filteredStreams.value = [];
        }
        form.academic_stream_id = '';
        form.level = '';
        form.course_id = '';
    } else {
        filteredStreams.value = [];
        form.academic_stream_id = '';
        form.level = '';
        form.course_id = '';
    }
});

// Watch for department, stream, level to filter courses
watch(() => [form.department_id, form.academic_stream_id, form.level], async ([deptId, streamId, level]) => {
    if (deptId || streamId || level) {
        try {
            const response = await axios.get('/get-courses-by-filters', {
                params: {
                    department_id: deptId,
                    academic_stream_id: streamId,
                    level: level
                }
            });
            filteredCourses.value = response.data;
        } catch (error) {
            console.error('Error fetching courses:', error);
            filteredCourses.value = [];
        }
    } else {
        filteredCourses.value = [];
    }
}, { immediate: true });

// Edit form watchers
watch(() => editForm.department_id, async (departmentId) => {
    if (departmentId) {
        try {
            const response = await axios.get(`/departments/${departmentId}/streams`);
            filteredEditStreams.value = response.data;
        } catch (error) {
            console.error('Error fetching streams:', error);
            filteredEditStreams.value = [];
        }
    } else {
        filteredEditStreams.value = [];
    }
});

watch(() => [editForm.department_id, editForm.academic_stream_id, editForm.level], async ([deptId, streamId, level]) => {
    if (deptId || streamId || level) {
        try {
            const response = await axios.get('/get-courses-by-filters', {
                params: {
                    department_id: deptId,
                    academic_stream_id: streamId,
                    level: level
                }
            });
            filteredEditCourses.value = response.data;
        } catch (error) {
            console.error('Error fetching courses:', error);
            filteredEditCourses.value = [];
        }
    } else {
        filteredEditCourses.value = [];
    }
}, { immediate: true });

// Modal functions
const openModal = () => {
    showModal.value = true;
    form.clearErrors();
    filteredStreams.value = [];
    filteredCourses.value = [];
};

const openEditModal = (teacher) => {
    selectedTeacher.value = teacher;
    
    editForm.user_id = teacher.user_id;
    editForm.phone_number = teacher.phone_number;
    editForm.department_id = teacher.department_id;
    editForm.academic_stream_id = teacher.academic_stream_id;
    editForm.level = teacher.level;
    editForm.course_id = teacher.course_id;
    editForm.session = teacher.session || '';
    
    showEditModal.value = true;
    editForm.clearErrors();
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
    filteredStreams.value = [];
    filteredCourses.value = [];
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedTeacher.value = null;
    editForm.reset();
    editForm.clearErrors();
    filteredEditStreams.value = [];
    filteredEditCourses.value = [];
};

// Toast function
const showSuccessToast = (message) => {
    toastMessage.value = message;
    showToast.value = true;
    
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};

// Get selected user email
const getSelectedUserEmail = computed(() => {
    const user = props.users.find(u => u.id == form.user_id);
    return user ? user.email : '';
});

// Get selected edit user email
const getSelectedEditUserEmail = computed(() => {
    const user = props.users.find(u => u.id == editForm.user_id);
    return user ? user.email : '';
});

// Form submission
const submitForm = () => {
    form.post(route('course-teachers.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessToast('Course teacher assigned successfully!');
            closeModal();
        },
        onError: (errors) => {
            console.log('Form errors:', errors);
        },
    });
};

const updateTeacher = () => {
    if (!selectedTeacher.value) return;
    
    editForm.put(route('course-teachers.update', selectedTeacher.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessToast('Course teacher updated successfully!');
            closeEditModal();
        },
        onError: (errors) => {
            console.log('Form errors:', errors);
        },
    });
};

const deleteTeacher = (id) => {
    if (confirm('Are you sure you want to remove this course teacher?')) {
        router.delete(route('course-teachers.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                showSuccessToast('Course teacher removed successfully!');
            },
        });
    }
};

// Pagination
const goToPage = (page) => {
    if (props.courseTeachers.current_page === page) return;
    router.get(route('course-teachers.index'), { ...filters.value, page: page }, {
        preserveState: true,
        preserveScroll: true
    });
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

// Watch for flash messages
watch(() => page.props.flash, (newFlash) => {
    if (newFlash?.success) {
        showSuccessToast(newFlash.success);
    }
}, { deep: true, immediate: true });

onMounted(() => {
    console.log('Course teachers loaded:', props.courseTeachers.data?.length || 0);
});
</script>

<template>
    <AuthenticatedLayout>
        <!-- Toast Notification -->
        <div v-if="showToast" class="fixed top-4 right-4 z-50 animate-slide-in">
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center space-x-3 backdrop-blur-sm border border-white/20">
                <div class="bg-white/20 rounded-full p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <span class="font-medium">{{ toastMessage }}</span>
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
                                        <h1 class="text-3xl font-bold text-white tracking-tight">Course Teacher Management</h1>
                                        <p class="text-indigo-100 mt-1">Manage and assign teachers to academic courses</p>
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
                                <span class="relative z-10">Assign Teacher</span>
                            </button>
                        </div>

                        <!-- Stats Row -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
                            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white/70 text-xs uppercase tracking-wider">Total Assignments</p>
                                        <p class="text-white text-2xl font-bold">{{ courseTeachers.total || 0 }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white/70 text-xs uppercase tracking-wider">Unique Teachers</p>
                                        <p class="text-white text-2xl font-bold">{{ new Set(courseTeachers.data?.map(t => t.user_id)).size }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 3.75l-2.25 1.313-2.25-1.313m-9 0l-2.25 1.313-2.25-1.313" />
                                    </svg>
                                </div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white/70 text-xs uppercase tracking-wider">Departments</p>
                                        <p class="text-white text-2xl font-bold">{{ new Set(courseTeachers.data?.filter(t => t.department_id).map(t => t.department_id)).size }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white/70 text-xs uppercase tracking-wider">Courses</p>
                                        <p class="text-white text-2xl font-bold">{{ new Set(courseTeachers.data?.filter(t => t.course_id).map(t => t.course_id)).size }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters Section - Glassmorphism Card -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl mb-6 border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2">
                                <div class="p-1.5 bg-indigo-100 rounded-lg">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">Filter Assignments</h3>
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
                        
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">🔍 Search</label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        v-model="filters.search"
                                        placeholder="Search by teacher name, email, phone..."
                                        class="w-full px-4 py-2.5 pl-10 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                    />
                                    <svg class="absolute left-3 top-3 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">🏛️ Department</label>
                                <select
                                    v-model="filters.department_id"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                >
                                    <option value="">All Departments</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                        {{ dept.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">📚 Stream</label>
                                <select
                                    v-model="filters.academic_stream_id"
                                    :disabled="!filters.department_id"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <option value="">All Streams</option>
                                    <option v-for="stream in academicStreams" :key="stream.id" :value="stream.id">
                                        {{ stream.stream_name }}
                                    </option>
                                </select>
                            </div>

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

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">📅 Session</label>
                                <input
                                    type="text"
                                    v-model="filters.session"
                                    placeholder="Search by session..."
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Teachers Table Card -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                    <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">📋 Teacher Assignments</h2>
                                <p class="text-sm text-gray-600 mt-1">List of all course-teacher assignments in the system</p>
                            </div>
                            <div class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm font-semibold">
                                {{ courseTeachers.total || 0 }} Total
                            </div>
                        </div>
                    </div>

                    <div v-if="courseTeachers.data?.length === 0" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="bg-gradient-to-br from-gray-100 to-gray-200 p-6 rounded-full mb-4">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Course Teachers Assigned</h3>
                            <p class="text-gray-500 mb-6">Get started by assigning a teacher to a course.</p>
                            <button
                                @click="openModal"
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Assign First Teacher
                            </button>
                        </div>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Teacher</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Department & Stream</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Course</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Level & Session</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Assigned Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="(teacher, index) in courseTeachers.data" :key="teacher.id" class="hover:bg-indigo-50/30 transition-colors duration-200 group">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-500">
                                        <span class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white w-7 h-7 rounded-lg inline-flex items-center justify-center text-xs">
                                            {{ (courseTeachers.current_page - 1) * courseTeachers.per_page + index + 1 }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-sm font-bold shadow-md">
                                                {{ teacher.teacher_name?.charAt(0) || 'T' }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-semibold text-gray-900">{{ teacher.teacher_name }}</div>
                                                <div class="text-xs text-gray-500">{{ teacher.teacher_email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <span>{{ teacher.phone_number || 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-if="teacher.department_name" class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-blue-100 text-blue-700">
                                                {{ teacher.department_name }}
                                            </span>
                                            <span v-if="teacher.stream_name" class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-green-100 text-green-700">
                                                {{ teacher.stream_name }}
                                            </span>
                                            <span v-if="!teacher.department_name" class="text-gray-400 italic text-xs">Not assigned</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ teacher.course_name || 'N/A' }}</div>
                                        <div class="text-xs text-gray-500 font-mono">{{ teacher.course_code || '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-if="teacher.level" class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-purple-100 text-purple-700">
                                                {{ teacher.level }}
                                            </span>
                                            <span v-if="teacher.session" class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-amber-100 text-amber-700">
                                                {{ teacher.session }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ formatDate(teacher.created_at) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-1">
                                            <button
                                                @click="openEditModal(teacher)"
                                                class="p-2 text-indigo-600 hover:bg-indigo-100 rounded-lg transition-all duration-200 hover:scale-110"
                                                title="Edit"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button
                                                @click="deleteTeacher(teacher.id)"
                                                class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-all duration-200 hover:scale-110"
                                                title="Remove"
                                            >
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

                    <!-- Pagination -->
                    <div v-if="courseTeachers.last_page > 1" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div class="text-sm text-gray-600">
                                Showing <span class="font-semibold text-indigo-600">{{ courseTeachers.from }}</span> to <span class="font-semibold text-indigo-600">{{ courseTeachers.to }}</span> of <span class="font-semibold text-indigo-600">{{ courseTeachers.total }}</span> results
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    @click="goToPage(courseTeachers.current_page - 1)"
                                    :disabled="courseTeachers.current_page === 1"
                                    class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                                >
                                    ← Previous
                                </button>
                                <template v-for="page in courseTeachers.last_page" :key="page">
                                    <button
                                        v-if="Math.abs(page - courseTeachers.current_page) <= 2 || page === 1 || page === courseTeachers.last_page"
                                        @click="goToPage(page)"
                                        :class="[
                                            'px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200',
                                            page === courseTeachers.current_page
                                                ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md'
                                                : 'border border-gray-300 text-gray-700 hover:bg-gray-100'
                                        ]"
                                    >
                                        {{ page }}
                                    </button>
                                    <span
                                        v-else-if="page === courseTeachers.current_page - 3 || page === courseTeachers.current_page + 3"
                                        class="px-2 py-2 text-gray-500"
                                    >
                                        ...
                                    </span>
                                </template>
                                <button
                                    @click="goToPage(courseTeachers.current_page + 1)"
                                    :disabled="courseTeachers.current_page === courseTeachers.last_page"
                                    class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                                >
                                    Next →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign Teacher Modal -->
        <div v-if="showModal" class="fixed inset-0 overflow-y-auto z-50">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>
            
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="bg-white/20 p-2 rounded-xl">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-white">Assign Teacher to Course</h3>
                            </div>
                            <button @click="closeModal" class="text-white/80 hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="bg-white px-6 py-6">
                        <form @submit.prevent="submitForm">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">👨‍🏫 Teacher Name <span class="text-red-500">*</span></label>
                                    <select
                                        v-model="form.user_id"
                                        :disabled="form.processing"
                                        required
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                        :class="{ 'border-red-500': form.errors.user_id }"
                                    >
                                        <option value="">Select Teacher</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.user_id" class="mt-1 text-sm text-red-600">{{ form.errors.user_id }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📧 Teacher Email</label>
                                    <input
                                        type="text"
                                        :value="getSelectedUserEmail"
                                        disabled
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-gray-500 cursor-not-allowed"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📞 Phone Number <span class="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        v-model="form.phone_number"
                                        :disabled="form.processing"
                                        required
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Enter phone number"
                                        :class="{ 'border-red-500': form.errors.phone_number }"
                                    />
                                    <p v-if="form.errors.phone_number" class="mt-1 text-sm text-red-600">{{ form.errors.phone_number }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">🏛️ Department</label>
                                    <select
                                        v-model="form.department_id"
                                        :disabled="form.processing"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                        :class="{ 'border-red-500': form.errors.department_id }"
                                    >
                                        <option value="">Select Department</option>
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                            {{ dept.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.department_id" class="mt-1 text-sm text-red-600">{{ form.errors.department_id }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📚 Academic Stream</label>
                                    <select
                                        v-model="form.academic_stream_id"
                                        :disabled="form.processing || !form.department_id"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="{ 'border-red-500': form.errors.academic_stream_id }"
                                    >
                                        <option value="">Select Stream</option>
                                        <option v-for="stream in filteredStreams" :key="stream.id" :value="stream.id">
                                            {{ stream.stream_name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.academic_stream_id" class="mt-1 text-sm text-red-600">{{ form.errors.academic_stream_id }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📊 Level</label>
                                    <select
                                        v-model="form.level"
                                        :disabled="form.processing"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                        :class="{ 'border-red-500': form.errors.level }"
                                    >
                                        <option value="">Select Level</option>
                                        <option v-for="level in availableLevels" :key="level.id" :value="level.id">
                                            {{ level.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.level" class="mt-1 text-sm text-red-600">{{ form.errors.level }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📅 Session <span class="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        v-model="form.session"
                                        :disabled="form.processing"
                                        required
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Enter session (e.g., Spring-2023, Fall-2024)"
                                        :class="{ 'border-red-500': form.errors.session }"
                                    />
                                    <p v-if="form.errors.session" class="mt-1 text-sm text-red-600">{{ form.errors.session }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📖 Course</label>
                                    <select
                                        v-model="form.course_id"
                                        :disabled="form.processing || filteredCourses.length === 0"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="{ 'border-red-500': form.errors.course_id }"
                                    >
                                        <option value="">Select Course</option>
                                        <option v-for="course in filteredCourses" :key="course.id" :value="course.id">
                                            {{ course.course_name }} ({{ course.course_code }})
                                        </option>
                                    </select>
                                    <p v-if="form.errors.course_id" class="mt-1 text-sm text-red-600">{{ form.errors.course_id }}</p>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end space-x-3">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    :disabled="form.processing"
                                    class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all duration-200"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50"
                                >
                                    <span v-if="form.processing" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Assigning...
                                    </span>
                                    <span v-else class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Assign Teacher
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Teacher Modal -->
        <div v-if="showEditModal" class="fixed inset-0 overflow-y-auto z-50">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="closeEditModal"></div>
            
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="bg-white/20 p-2 rounded-xl">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-white">Edit Teacher Assignment</h3>
                            </div>
                            <button @click="closeEditModal" class="text-white/80 hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="bg-white px-6 py-6">
                        <form @submit.prevent="updateTeacher">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">👨‍🏫 Teacher Name <span class="text-red-500">*</span></label>
                                    <select
                                        v-model="editForm.user_id"
                                        :disabled="editForm.processing"
                                        required
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200"
                                        :class="{ 'border-red-500': editForm.errors.user_id }"
                                    >
                                        <option value="">Select Teacher</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                    <p v-if="editForm.errors.user_id" class="mt-1 text-sm text-red-600">{{ editForm.errors.user_id }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📧 Teacher Email</label>
                                    <input
                                        type="text"
                                        :value="getSelectedEditUserEmail"
                                        disabled
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-gray-500 cursor-not-allowed"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📞 Phone Number <span class="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        v-model="editForm.phone_number"
                                        :disabled="editForm.processing"
                                        required
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Enter phone number"
                                        :class="{ 'border-red-500': editForm.errors.phone_number }"
                                    />
                                    <p v-if="editForm.errors.phone_number" class="mt-1 text-sm text-red-600">{{ editForm.errors.phone_number }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">🏛️ Department</label>
                                    <select
                                        v-model="editForm.department_id"
                                        :disabled="editForm.processing"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200"
                                        :class="{ 'border-red-500': editForm.errors.department_id }"
                                    >
                                        <option value="">Select Department</option>
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                            {{ dept.name }}
                                        </option>
                                    </select>
                                    <p v-if="editForm.errors.department_id" class="mt-1 text-sm text-red-600">{{ editForm.errors.department_id }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📚 Academic Stream</label>
                                    <select
                                        v-model="editForm.academic_stream_id"
                                        :disabled="editForm.processing || !editForm.department_id"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="{ 'border-red-500': editForm.errors.academic_stream_id }"
                                    >
                                        <option value="">Select Stream</option>
                                        <option v-for="stream in filteredEditStreams" :key="stream.id" :value="stream.id">
                                            {{ stream.stream_name }}
                                        </option>
                                    </select>
                                    <p v-if="editForm.errors.academic_stream_id" class="mt-1 text-sm text-red-600">{{ editForm.errors.academic_stream_id }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📊 Level</label>
                                    <select
                                        v-model="editForm.level"
                                        :disabled="editForm.processing"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200"
                                        :class="{ 'border-red-500': editForm.errors.level }"
                                    >
                                        <option value="">Select Level</option>
                                        <option v-for="level in availableLevels" :key="level.id" :value="level.id">
                                            {{ level.name }}
                                        </option>
                                    </select>
                                    <p v-if="editForm.errors.level" class="mt-1 text-sm text-red-600">{{ editForm.errors.level }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📅 Session <span class="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        v-model="editForm.session"
                                        :disabled="editForm.processing"
                                        required
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Enter session (e.g., Spring-2023, Fall-2024)"
                                        :class="{ 'border-red-500': editForm.errors.session }"
                                    />
                                    <p v-if="editForm.errors.session" class="mt-1 text-sm text-red-600">{{ editForm.errors.session }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📖 Course</label>
                                    <select
                                        v-model="editForm.course_id"
                                        :disabled="editForm.processing || filteredEditCourses.length === 0"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="{ 'border-red-500': editForm.errors.course_id }"
                                    >
                                        <option value="">Select Course</option>
                                        <option v-for="course in filteredEditCourses" :key="course.id" :value="course.id">
                                            {{ course.course_name }} ({{ course.course_code }})
                                        </option>
                                    </select>
                                    <p v-if="editForm.errors.course_id" class="mt-1 text-sm text-red-600">{{ editForm.errors.course_id }}</p>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end space-x-3">
                                <button
                                    type="button"
                                    @click="closeEditModal"
                                    :disabled="editForm.processing"
                                    class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all duration-200"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="editForm.processing"
                                    class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50"
                                >
                                    <span v-if="editForm.processing" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Updating...
                                    </span>
                                    <span v-else class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Update Assignment
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showModal || showEditModal" class="fixed inset-0 bg-black/30 backdrop-blur-sm z-40"></div>
    </AuthenticatedLayout>
</template>

<style scoped>
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