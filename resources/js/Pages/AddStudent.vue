<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch, onMounted } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

// Reactive state
const showModal = ref(false);
const showEditModal = ref(false);
const editingStudent = ref(null);
const students = ref([]);
const loading = ref(false);

const form = useForm({
    first_name: '',
    last_name: '',
    phone: '',
    email: '',
    department_id: '',
    academic_stream_id: '',
    student_id: '',
    level: '',
    session: ''
});

const editForm = useForm({
    id: null,
    first_name: '',
    last_name: '',
    phone: '',
    email: '',
    department_id: '',
    academic_stream_id: '',
    student_id: '',
    level: '',
    session: '',
    is_active: true
});

const errors = ref({});
const editErrors = ref({});
const submitting = ref(false);
const editSubmitting = ref(false);

// Toast notification
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

// Dropdown data
const departments = ref([]);
const academicStreams = ref([]);
const editAcademicStreams = ref([]);
const loadingStreams = ref(false);
const loadingEditStreams = ref(false);

// Filter data
const filterDepartment = ref('');
const filterStream = ref('');
const filterLevel = ref('');
const filterSession = ref('');
const filterStatus = ref('');
const levelsList = ref([]);
const sessionsList = ref([]);
const allStreams = ref([]);

// Pagination
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);
const searchQuery = ref('');
const searchTimeout = ref(null);

const page = usePage();

// Get filter data from props
const props = defineProps({
    departmentsData: Array,
    streamsData: Array,
    levelsData: Array,
    sessionsData: Array
});

// Initialize filter data
onMounted(() => {
    if (props.departmentsData) {
        departments.value = props.departmentsData;
    }
    if (props.streamsData) {
        allStreams.value = props.streamsData;
    }
    if (props.levelsData) {
        levelsList.value = props.levelsData;
    }
    if (props.sessionsData) {
        sessionsList.value = props.sessionsData;
    }
    fetchStudents();
    fetchDepartments();
});

// Fetch students with filters
const fetchStudents = async () => {
    loading.value = true;
    try {
        let url = `/students/list/data?page=${currentPage.value}`;
        
        if (searchQuery.value) {
            url += `&search=${encodeURIComponent(searchQuery.value)}`;
        }
        
        if (filterDepartment.value) url += `&department_id=${filterDepartment.value}`;
        if (filterStream.value) url += `&academic_stream_id=${filterStream.value}`;
        if (filterLevel.value) url += `&level=${filterLevel.value}`;
        if (filterSession.value) url += `&session=${filterSession.value}`;
        if (filterStatus.value !== '') url += `&is_active=${filterStatus.value}`;
        
        const response = await fetch(url);
        const data = await response.json();
        students.value = data.data;
        currentPage.value = data.current_page;
        lastPage.value = data.last_page;
        total.value = data.total;
    } catch (error) {
        console.error('Error fetching students:', error);
        showToastMessage('Failed to load students', 'error');
    } finally {
        loading.value = false;
    }
};

// Debounced search
const handleSearch = () => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }
    searchTimeout.value = setTimeout(() => {
        currentPage.value = 1;
        fetchStudents();
    }, 500);
};

// Apply filters
const applyFilters = () => {
    currentPage.value = 1;
    fetchStudents();
};

// Reset filters
const resetFilters = () => {
    filterDepartment.value = '';
    filterStream.value = '';
    filterLevel.value = '';
    filterSession.value = '';
    filterStatus.value = '';
    searchQuery.value = '';
    currentPage.value = 1;
    fetchStudents();
};

// Page change
const changePage = (page) => {
    if (page < 1 || page > lastPage.value) return;
    currentPage.value = page;
    fetchStudents();
};

// Toast message
const showToastMessage = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};

// Watch for flash messages
watch(() => page.props.flash, (newFlash) => {
    if (newFlash?.success) {
        showToastMessage(newFlash.success, 'success');
        fetchStudents();
    }
    if (newFlash?.error) {
        showToastMessage(newFlash.error, 'error');
    }
}, { deep: true, immediate: true });

// Fetch departments
const fetchDepartments = async () => {
    try {
        const response = await fetch('/students/departments');
        const data = await response.json();
        if (data.data && departments.value.length === 0) {
            departments.value = data.data;
        }
    } catch (error) {
        console.error('Error fetching departments:', error);
    }
};

// Fetch streams
const fetchStreamsByDepartment = async (departmentId, isEdit = false) => {
    if (!departmentId) {
        if (isEdit) {
            editAcademicStreams.value = [];
        } else {
            academicStreams.value = [];
        }
        return;
    }
    
    if (isEdit) {
        loadingEditStreams.value = true;
    } else {
        loadingStreams.value = true;
    }
    
    try {
        const response = await fetch(`/students/department/${departmentId}/streams`);
        const data = await response.json();
        
        if (isEdit) {
            editAcademicStreams.value = data.data;
        } else {
            academicStreams.value = data.data;
        }
    } catch (error) {
        console.error('Error fetching streams:', error);
        if (isEdit) {
            editAcademicStreams.value = [];
        } else {
            academicStreams.value = [];
        }
    } finally {
        if (isEdit) {
            loadingEditStreams.value = false;
        } else {
            loadingStreams.value = false;
        }
    }
};

// Watch for department changes
watch(() => form.department_id, (newDepartmentId) => {
    if (newDepartmentId) {
        fetchStreamsByDepartment(newDepartmentId, false);
    } else {
        academicStreams.value = [];
    }
    form.academic_stream_id = '';
});

watch(() => editForm.department_id, (newDepartmentId) => {
    if (newDepartmentId) {
        fetchStreamsByDepartment(newDepartmentId, true);
    } else {
        editAcademicStreams.value = [];
    }
    editForm.academic_stream_id = '';
});

// Submit form
const submitForm = async () => {
    submitting.value = true;
    
    form.post('/students', {
        preserveScroll: true,
        onSuccess: () => {
            showToastMessage('Student created successfully!', 'success');
            resetForm();
            showModal.value = false;
            fetchStudents();
            submitting.value = false;
        },
        onError: (serverErrors) => {
            errors.value = serverErrors;
            const firstError = Object.values(serverErrors)[0];
            if (firstError) showToastMessage(firstError, 'error');
            submitting.value = false;
        }
    });
};

// Edit student
const editStudent = (student) => {
    editingStudent.value = student;
    editForm.id = student.id;
    editForm.first_name = student.first_name;
    editForm.last_name = student.last_name;
    editForm.phone = student.phone;
    editForm.email = student.email;
    editForm.department_id = student.department_id;
    editForm.academic_stream_id = student.academic_stream_id;
    editForm.student_id = student.student_id;
    editForm.level = student.level;
    editForm.session = student.session;
    editForm.is_active = student.is_active;
    
    if (student.department_id) {
        fetchStreamsByDepartment(student.department_id, true);
    }
    
    showEditModal.value = true;
};

// Submit edit form
const submitEditForm = async () => {
    editSubmitting.value = true;
    
    editForm.put(`/students/${editForm.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showToastMessage('Student updated successfully!', 'success');
            resetEditForm();
            showEditModal.value = false;
            fetchStudents();
            editSubmitting.value = false;
        },
        onError: (serverErrors) => {
            editErrors.value = serverErrors;
            const firstError = Object.values(serverErrors)[0];
            if (firstError) showToastMessage(firstError, 'error');
            editSubmitting.value = false;
        }
    });
};

// Delete student
const deleteStudent = async (id) => {
    if (!confirm('Are you sure you want to delete this student?')) return;
    
    try {
        const response = await fetch(`/students/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToastMessage('Student deleted successfully!', 'success');
            fetchStudents();
        } else {
            showToastMessage(data.message || 'Failed to delete student', 'error');
        }
    } catch (error) {
        showToastMessage('An error occurred while deleting the student', 'error');
    }
};

// Reset forms
const resetForm = () => {
    form.reset();
    errors.value = {};
    academicStreams.value = [];
};

const resetEditForm = () => {
    editForm.reset();
    editErrors.value = {};
    editAcademicStreams.value = [];
    editingStudent.value = null;
};

const closeModal = () => {
    resetForm();
    showModal.value = false;
};

const closeEditModal = () => {
    resetEditForm();
    showEditModal.value = false;
};

const openModal = () => {
    showModal.value = true;
};

// Watch modals
watch(showModal, (newValue) => {
    if (!newValue) resetForm();
});

watch(showEditModal, (newValue) => {
    if (!newValue) resetEditForm();
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Toast Notification -->
                <div v-if="showToast" class="fixed top-4 right-4 z-50 animate-slide-in">
                    <div :class="toastType === 'success' ? 'bg-gradient-to-r from-emerald-500 to-teal-600' : 'bg-gradient-to-r from-red-500 to-rose-600'" class="text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center space-x-3 backdrop-blur-sm border border-white/20">
                        <div class="bg-white/20 rounded-full p-1">
                            <svg v-if="toastType === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <span class="font-medium">{{ toastMessage }}</span>
                    </div>
                </div>
                
                <!-- Hero Header Section -->
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
                                        <h1 class="text-3xl font-bold text-white tracking-tight">Student Management</h1>
                                        <p class="text-indigo-100 mt-1">Manage student records and information</p>
                                    </div>
                                </div>
                            </div>
                            <button @click="openModal" class="group relative inline-flex items-center px-6 py-3 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl font-semibold text-white shadow-lg hover:bg-white/20 hover:scale-105 transition-all duration-300 overflow-hidden">
                                <span class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></span>
                                <svg class="h-5 w-5 mr-2 relative z-10 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span class="relative z-10">Add New Student</span>
                            </button>
                        </div>

                        <!-- Stats Row -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
                            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white/70 text-xs uppercase tracking-wider">Total Students</p>
                                        <p class="text-white text-2xl font-bold">{{ total }}</p>
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
                                        <p class="text-white text-2xl font-bold">{{ departments.length }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white/70 text-xs uppercase tracking-wider">Active Students</p>
                                        <p class="text-white text-2xl font-bold">{{ students.filter(s => s.is_active).length }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white/70 text-xs uppercase tracking-wider">Current Page</p>
                                        <p class="text-white text-2xl font-bold">{{ currentPage }} / {{ lastPage }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filters Section -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl mb-6 border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2">
                                <div class="p-1.5 bg-indigo-100 rounded-lg">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">Filter Students</h3>
                            </div>
                            <button @click="resetFilters" class="text-sm text-gray-500 hover:text-indigo-600 transition-colors flex items-center space-x-1 px-3 py-1.5 rounded-lg hover:bg-indigo-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Reset</span>
                            </button>
                        </div>
                        
                        <!-- Search Bar -->
                        <div class="relative mb-4">
                            <input type="text" v-model="searchQuery" @input="handleSearch" placeholder="Search by name, email, student ID or phone..." class="w-full px-4 py-2.5 pl-10 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
                            <svg class="absolute left-3 top-3 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        
                        <!-- Filter Row -->
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <select v-model="filterDepartment" @change="applyFilters" class="px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
                                <option value="">🏛️ All Departments</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                            </select>
                            
                            <select v-model="filterStream" @change="applyFilters" class="px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
                                <option value="">📚 All Streams</option>
                                <option v-for="stream in allStreams" :key="stream.id" :value="stream.id">{{ stream.name }}</option>
                            </select>
                            
                            <select v-model="filterLevel" @change="applyFilters" class="px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
                                <option value="">📊 All Levels</option>
                                <option v-for="level in levelsList" :key="level.level" :value="level.level">{{ level.level }}</option>
                            </select>
                            
                            <select v-model="filterSession" @change="applyFilters" class="px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
                                <option value="">📅 All Sessions</option>
                                <option v-for="session in sessionsList" :key="session.session" :value="session.session">{{ session.session }}</option>
                            </select>
                            
                            <select v-model="filterStatus" @change="applyFilters" class="px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
                                <option value="">🔄 All Status</option>
                                <option value="1">✅ Active</option>
                                <option value="0">⭕ Inactive</option>
                            </select>
                        </div>
                        
                        <!-- Filter Actions -->
                        <div class="flex gap-3 mt-4">
                            <button @click="applyFilters" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                Apply Filters
                            </button>
                            <button @click="resetFilters" class="inline-flex items-center px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Reset Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Students Table Card -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                    <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">👨‍🎓 Student Records</h2>
                                <p class="text-sm text-gray-600 mt-1">List of all students in the system</p>
                            </div>
                            <div class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm font-semibold">
                                {{ total }} Total
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <div v-if="loading" class="text-center py-16">
                            <svg class="animate-spin h-10 w-10 text-indigo-600 mx-auto" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="mt-3 text-gray-600 font-medium">Loading students...</p>
                        </div>
                        
                        <table v-else class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student ID</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Department</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Stream</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Level/Session</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="(student, idx) in students" :key="student.id" class="hover:bg-indigo-50/30 transition-colors duration-200 group">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-500">
                                        <span class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white w-7 h-7 rounded-lg inline-flex items-center justify-center text-xs">
                                            {{ (currentPage - 1) * 10 + idx + 1 }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-sm font-bold shadow-md">
                                                {{ student.first_name?.charAt(0) }}{{ student.last_name?.charAt(0) }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-semibold text-gray-900">{{ student.first_name }} {{ student.last_name }}</div>
                                                <div class="text-xs text-gray-500">{{ student.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-mono font-semibold bg-blue-100 text-blue-700">
                                            {{ student.student_id }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ student.department_name || '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="student.stream_name" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-green-100 text-green-700">
                                            {{ student.stream_name }}
                                        </span>
                                        <span v-else class="text-gray-400 italic text-xs">Not assigned</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-800">{{ student.level || '-' }}</span>
                                            <span class="text-xs text-gray-500">{{ student.session || '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <span class="text-sm text-gray-600">{{ student.phone }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="student.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="student.is_active ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                            {{ student.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button @click="editStudent(student)" class="p-2 text-indigo-600 hover:bg-indigo-100 rounded-lg transition-all duration-200 hover:scale-110" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button @click="deleteStudent(student.id)" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-all duration-200 hover:scale-110" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="students.length === 0">
                                    <td colspan="9" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="bg-gradient-to-br from-gray-100 to-gray-200 p-6 rounded-full mb-4">
                                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 3.75l-2.25 1.313-2.25-1.313m-9 0l-2.25 1.313-2.25-1.313" />
                                                </svg>
                                            </div>
                                            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Students Found</h3>
                                            <p class="text-gray-500 mb-6">Try adjusting your filters or add a new student.</p>
                                            <button @click="openModal" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Add Your First Student
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="!loading && students.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div class="text-sm text-gray-600">
                                Showing <span class="font-semibold text-indigo-600">{{ (currentPage - 1) * 10 + 1 }}</span> to <span class="font-semibold text-indigo-600">{{ Math.min(currentPage * 10, total) }}</span> of <span class="font-semibold text-indigo-600">{{ total }}</span> students
                            </div>
                            <div class="flex space-x-2">
                                <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                                    ← Previous
                                </button>
                                <button @click="changePage(currentPage + 1)" :disabled="currentPage === lastPage" class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                                    Next →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Student Modal -->
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
                                        <h3 class="text-xl font-bold text-white">Add New Student</h3>
                                    </div>
                                    <button @click="closeModal" class="text-white/80 hover:text-white transition-colors text-2xl">&times;</button>
                                </div>
                            </div>
                            <div class="bg-white px-6 py-6 max-h-[70vh] overflow-y-auto">
                                <form @submit.prevent="submitForm">
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">👤 First Name *</label>
                                            <input type="text" v-model="form.first_name" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" :class="{'border-red-500': errors.first_name}">
                                            <div v-if="errors.first_name" class="text-red-500 text-sm mt-1">{{ errors.first_name }}</div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">👤 Last Name *</label>
                                            <input type="text" v-model="form.last_name" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" :class="{'border-red-500': errors.last_name}">
                                            <div v-if="errors.last_name" class="text-red-500 text-sm mt-1">{{ errors.last_name }}</div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">📞 Phone *</label>
                                            <input type="text" v-model="form.phone" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" :class="{'border-red-500': errors.phone}">
                                            <div v-if="errors.phone" class="text-red-500 text-sm mt-1">{{ errors.phone }}</div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">📧 Email *</label>
                                            <input type="email" v-model="form.email" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" :class="{'border-red-500': errors.email}">
                                            <div v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">🏛️ Department *</label>
                                            <select v-model="form.department_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" :class="{'border-red-500': errors.department_id}">
                                                <option value="">Select Department</option>
                                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                            </select>
                                            <div v-if="errors.department_id" class="text-red-500 text-sm mt-1">{{ errors.department_id }}</div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">📚 Academic Stream</label>
                                            <select v-model="form.academic_stream_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 disabled:opacity-50" :disabled="!form.department_id || loadingStreams" :class="{'border-red-500': errors.academic_stream_id}">
                                                <option value="">Select Stream</option>
                                                <option v-for="stream in academicStreams" :key="stream.id" :value="stream.id">{{ stream.name }}</option>
                                            </select>
                                            <div v-if="errors.academic_stream_id" class="text-red-500 text-sm mt-1">{{ errors.academic_stream_id }}</div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">🆔 Student ID *</label>
                                            <input type="text" v-model="form.student_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" :class="{'border-red-500': errors.student_id}">
                                            <div v-if="errors.student_id" class="text-red-500 text-sm mt-1">{{ errors.student_id }}</div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">📊 Level *</label>
                                            <input type="text" v-model="form.level" placeholder="4/2" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" :class="{'border-red-500': errors.level}">
                                            <div v-if="errors.level" class="text-red-500 text-sm mt-1">{{ errors.level }}</div>
                                        </div>
                                    </div>
                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">📅 Session *</label>
                                        <input type="text" v-model="form.session" placeholder="Spring-2022" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" :class="{'border-red-500': errors.session}">
                                        <div v-if="errors.session" class="text-red-500 text-sm mt-1">{{ errors.session }}</div>
                                    </div>
                                    <div class="flex justify-end gap-3 pt-4 border-t">
                                        <button type="button" @click="closeModal" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all duration-200">Cancel</button>
                                        <button type="submit" :disabled="submitting" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50">
                                            {{ submitting ? 'Adding...' : 'Add Student' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Student Modal -->
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
                                        <h3 class="text-xl font-bold text-white">Edit Student</h3>
                                    </div>
                                    <button @click="closeEditModal" class="text-white/80 hover:text-white transition-colors text-2xl">&times;</button>
                                </div>
                            </div>
                            <div class="bg-white px-6 py-6 max-h-[70vh] overflow-y-auto">
                                <form @submit.prevent="submitEditForm">
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">👤 First Name *</label>
                                            <input type="text" v-model="editForm.first_name" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">👤 Last Name *</label>
                                            <input type="text" v-model="editForm.last_name" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">📞 Phone *</label>
                                            <input type="text" v-model="editForm.phone" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">📧 Email *</label>
                                            <input type="email" v-model="editForm.email" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">🏛️ Department *</label>
                                            <select v-model="editForm.department_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200">
                                                <option value="">Select Department</option>
                                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">📚 Academic Stream</label>
                                            <select v-model="editForm.academic_stream_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 disabled:opacity-50" :disabled="!editForm.department_id || loadingEditStreams">
                                                <option value="">Select Stream</option>
                                                <option v-for="stream in editAcademicStreams" :key="stream.id" :value="stream.id">{{ stream.name }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">🆔 Student ID *</label>
                                            <input type="text" v-model="editForm.student_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">📊 Level *</label>
                                            <input type="text" v-model="editForm.level" placeholder="4/2" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mb-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">📅 Session *</label>
                                            <input type="text" v-model="editForm.session" placeholder="Spring-2022" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">🔄 Status</label>
                                            <select v-model="editForm.is_active" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200">
                                                <option :value="true">✅ Active</option>
                                                <option :value="false">⭕ Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-3 pt-4 border-t">
                                        <button type="button" @click="closeEditModal" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all duration-200">Cancel</button>
                                        <button type="submit" :disabled="editSubmitting" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50">
                                            {{ editSubmitting ? 'Updating...' : 'Update Student' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes slide-in {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
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