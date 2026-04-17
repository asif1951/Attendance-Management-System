<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    users: {
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
    role: props.filters.role || ''
});

// Modal states
const showModal = ref(false);
const showEditModal = ref(false);
const showToast = ref(false);
const toastMessage = ref('');
const selectedUser = ref(null);

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
        router.get(route('user.index'), filters.value, {
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
        role: ''
    };
    applyFilters();
};

// Watch filters
watch(() => filters.value.search, () => applyFilters());
watch(() => filters.value.role, () => applyFilters());

// Pagination
const goToPage = (page) => {
    if (props.users.current_page === page) return;
    isLoading.value = true;
    router.get(route('user.index'), { ...filters.value, page: page }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

// Form handling for create
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user'
});

// Form handling for edit
const editForm = useForm({
    name: '',
    email: '',
    role: '',
    password: '',
    password_confirmation: ''
});

// Open create modal
const openModal = () => {
    showModal.value = true;
    form.reset();
    form.clearErrors();
};

// Open edit modal
const openEditModal = (user) => {
    selectedUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.role = user.role;
    editForm.password = '';
    editForm.password_confirmation = '';
    showEditModal.value = true;
    editForm.clearErrors();
};

// Close modals
const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedUser.value = null;
    editForm.reset();
    editForm.clearErrors();
};

// Show toast
const showSuccessToast = (message) => {
    toastMessage.value = message;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};

// Show error toast
const showErrorToast = (message) => {
    toastMessage.value = message;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};

// Create user
const submitForm = () => {
    form.post(route('user.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessToast('User created successfully!');
            closeModal();
        },
        onError: () => {
            // Handle errors
        }
    });
};

// Update user
const updateUser = () => {
    if (!selectedUser.value) return;
    
    editForm.put(route('user.update', selectedUser.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessToast('User updated successfully!');
            closeEditModal();
        },
        onError: (errors) => {
            console.log('Update errors:', errors);
        }
    });
};

// Delete user
const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('user.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                showSuccessToast('User deleted successfully!');
            },
            onError: (error) => {
                if (error.response?.data?.message) {
                    showErrorToast(error.response.data.message);
                }
            }
        });
    }
};

// Check if user is the first admin (ID 1)
const isFirstAdmin = (userId) => {
    return userId === 1;
};

// Format date
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        return 'Invalid Date';
    }
};

// Get role badge class
const getRoleBadgeClass = (role) => {
    return role === 'admin' 
        ? 'bg-purple-100 text-purple-800' 
        : 'bg-indigo-100 text-indigo-800';
};
</script>

<template>
    <AuthenticatedLayout>
        <!-- Loading Overlay -->
        <div v-if="isLoading" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center">
            <div class="bg-white rounded-2xl p-6 flex items-center space-x-3 shadow-2xl">
                <svg class="animate-spin h-8 w-8 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-700 font-semibold text-lg">Loading...</span>
            </div>
        </div>

        <!-- Toast Notification -->
        <div v-if="showToast" class="fixed top-4 right-4 z-50 animate-slide-in">
            <div :class="[toastMessage.includes('cannot') ? 'bg-gradient-to-r from-red-500 to-rose-600' : 'bg-gradient-to-r from-emerald-500 to-teal-600', 'text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center space-x-3 backdrop-blur-sm border border-white/20']">
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
                <!-- Hero Header Section -->
                <div class="relative mb-8 overflow-hidden bg-gradient-to-br from-purple-900 via-indigo-800 to-purple-700 rounded-3xl shadow-2xl">
                    <div class="absolute inset-0 bg-black/20"></div>
                    <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 1px); background-size: 40px 40px;"></div>
                    <div class="relative p-8 backdrop-blur-sm">
                        <div class="flex justify-between items-center flex-wrap gap-4">
                            <div>
                                <div class="flex items-center space-x-3 mb-2">
                                    <div class="bg-white/20 p-2 rounded-xl backdrop-blur">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 3.75l-2.25 1.313-2.25-1.313m-9 0l-2.25 1.313-2.25-1.313" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="text-3xl font-bold text-white tracking-tight">User Management</h1>
                                        <p class="text-purple-100 mt-1">Manage all system users and their roles</p>
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
                                <span class="relative z-10">Add New User</span>
                            </button>
                        </div>

                        <!-- Stats Row -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
                            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white/70 text-xs uppercase tracking-wider">Total Users</p>
                                        <p class="text-white text-2xl font-bold">{{ users.total || 0 }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 3.75l-2.25 1.313-2.25-1.313m-9 0l-2.25 1.313-2.25-1.313" />
                                    </svg>
                                </div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white/70 text-xs uppercase tracking-wider">Admins</p>
                                        <p class="text-white text-2xl font-bold">{{ users.data?.filter(u => u.role === 'admin').length || 0 }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white/70 text-xs uppercase tracking-wider">Users</p>
                                        <p class="text-white text-2xl font-bold">{{ users.data?.filter(u => u.role === 'user').length || 0 }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 transform hover:scale-105 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white/70 text-xs uppercase tracking-wider">Current Page</p>
                                        <p class="text-white text-2xl font-bold">{{ users.current_page || 1 }} / {{ users.last_page || 1 }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters Section -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl mb-6 border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2">
                                <div class="p-1.5 bg-purple-100 rounded-lg">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">Filter Users</h3>
                            </div>
                            <button
                                @click="resetFilters"
                                class="text-sm text-gray-500 hover:text-purple-600 transition-colors flex items-center space-x-1 px-3 py-1.5 rounded-lg hover:bg-purple-50"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Reset</span>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Search Input -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">🔍 Search</label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        v-model="filters.search"
                                        placeholder="Search by name or email..."
                                        class="w-full px-4 py-2.5 pl-10 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                    />
                                    <svg class="absolute left-3 top-3 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Role Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">👑 Role</label>
                                <select
                                    v-model="filters.role"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                >
                                    <option value="">All Roles</option>
                                    <option value="admin">Administrator</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                    <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">👥 System Users</h2>
                                <p class="text-sm text-gray-600 mt-1">List of all registered users in the system</p>
                            </div>
                            <div class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm font-semibold">
                                {{ users.total || 0 }} Total
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created At</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Updated At</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-if="users.data?.length === 0">
                                    <td colspan="7" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="bg-gradient-to-br from-gray-100 to-gray-200 p-6 rounded-full mb-4">
                                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                                </svg>
                                            </div>
                                            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Users Found</h3>
                                            <p class="text-gray-500 mb-6">Get started by creating your first user.</p>
                                            <button
                                                @click="openModal"
                                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300"
                                            >
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Add New User
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="(user, index) in users.data" :key="user.id" class="hover:bg-purple-50/30 transition-colors duration-200 group">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-500">
                                        <span class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white w-7 h-7 rounded-lg inline-flex items-center justify-center text-xs">
                                            {{ (users.current_page - 1) * users.per_page + index + 1 }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-500 flex items-center justify-center text-white text-sm font-bold shadow-md">
                                                {{ user.name?.charAt(0) }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ user.name }}
                                                    <span v-if="isFirstAdmin(user.id)" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-amber-100 text-amber-700">
                                                        👑 Primary Admin
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ user.email }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="['inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium', getRoleBadgeClass(user.role)]">
                                            <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="user.role === 'admin' ? 'bg-purple-500' : 'bg-indigo-500'"></span>
                                            {{ user.role === 'admin' ? 'Administrator' : 'User' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ formatDate(user.created_at) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            <span>{{ formatDate(user.updated_at) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button
                                                v-if="!isFirstAdmin(user.id)"
                                                @click="openEditModal(user)"
                                                class="p-2 text-purple-600 hover:bg-purple-100 rounded-lg transition-all duration-200 hover:scale-110"
                                                title="Edit"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button
                                                v-else
                                                class="p-2 text-gray-400 cursor-not-allowed rounded-lg"
                                                disabled
                                                title="Cannot edit primary admin"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button
                                                v-if="!isFirstAdmin(user.id)"
                                                @click="deleteUser(user.id)"
                                                class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-all duration-200 hover:scale-110"
                                                title="Delete"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                            <button
                                                v-else
                                                class="p-2 text-gray-400 cursor-not-allowed rounded-lg"
                                                disabled
                                                title="Cannot delete primary admin"
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
                    <div v-if="users.last_page > 1" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div class="text-sm text-gray-600">
                                Showing <span class="font-semibold text-purple-600">{{ users.from }}</span> to <span class="font-semibold text-purple-600">{{ users.to }}</span> of <span class="font-semibold text-purple-600">{{ users.total }}</span> results
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    @click="goToPage(users.current_page - 1)"
                                    :disabled="users.current_page === 1"
                                    class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                                >
                                    ← Previous
                                </button>
                                <template v-for="page in users.last_page" :key="page">
                                    <button
                                        v-if="Math.abs(page - users.current_page) <= 2 || page === 1 || page === users.last_page"
                                        @click="goToPage(page)"
                                        :class="[
                                            'px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200',
                                            page === users.current_page
                                                ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md'
                                                : 'border border-gray-300 text-gray-700 hover:bg-gray-100'
                                        ]"
                                    >
                                        {{ page }}
                                    </button>
                                    <span
                                        v-else-if="page === users.current_page - 3 || page === users.current_page + 3"
                                        class="px-2 py-2 text-gray-500"
                                    >
                                        ...
                                    </span>
                                </template>
                                <button
                                    @click="goToPage(users.current_page + 1)"
                                    :disabled="users.current_page === users.last_page"
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

        <!-- Create User Modal -->
        <div v-if="showModal" class="fixed inset-0 overflow-y-auto z-50">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="bg-white/20 p-2 rounded-xl">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-white">Create New User</h3>
                            </div>
                            <button @click="closeModal" class="text-white/80 hover:text-white transition-colors text-2xl">&times;</button>
                        </div>
                    </div>

                    <div class="bg-white px-6 py-6">
                        <form @submit.prevent="submitForm">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">👤 Name <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="form.name" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200" :class="{ 'border-red-500': form.errors.name }" />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📧 Email <span class="text-red-500">*</span></label>
                                    <input type="email" v-model="form.email" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200" :class="{ 'border-red-500': form.errors.email }" />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">👑 Role</label>
                                    <select v-model="form.role" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200">
                                        <option value="user">User</option>
                                        <option value="admin">Administrator</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">🔒 Password <span class="text-red-500">*</span></label>
                                    <input type="password" v-model="form.password" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200" :class="{ 'border-red-500': form.errors.password }" />
                                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">🔒 Confirm Password <span class="text-red-500">*</span></label>
                                    <input type="password" v-model="form.password_confirmation" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200" />
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end space-x-3">
                                <button type="button" @click="closeModal" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all duration-200">Cancel</button>
                                <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50">
                                    {{ form.processing ? 'Creating...' : 'Create User' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
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
                                <h3 class="text-xl font-bold text-white">Edit User</h3>
                            </div>
                            <button @click="closeEditModal" class="text-white/80 hover:text-white transition-colors text-2xl">&times;</button>
                        </div>
                    </div>

                    <div class="bg-white px-6 py-6">
                        <form @submit.prevent="updateUser">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">👤 Name <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="editForm.name" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200" :class="{ 'border-red-500': editForm.errors.name }" />
                                    <p v-if="editForm.errors.name" class="mt-1 text-sm text-red-600">{{ editForm.errors.name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">📧 Email <span class="text-red-500">*</span></label>
                                    <input type="email" v-model="editForm.email" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200" :class="{ 'border-red-500': editForm.errors.email }" />
                                    <p v-if="editForm.errors.email" class="mt-1 text-sm text-red-600">{{ editForm.errors.email }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">👑 Role</label>
                                    <select v-model="editForm.role" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200">
                                        <option value="user">User</option>
                                        <option value="admin">Administrator</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">🔒 Password (leave blank to keep current)</label>
                                    <input type="password" v-model="editForm.password" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">🔒 Confirm Password</label>
                                    <input type="password" v-model="editForm.password_confirmation" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200" />
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end space-x-3">
                                <button type="button" @click="closeEditModal" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all duration-200">Cancel</button>
                                <button type="submit" :disabled="editForm.processing" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50">
                                    {{ editForm.processing ? 'Updating...' : 'Update User' }}
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
    background: linear-gradient(135deg, #8b5cf6, #6366f1);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #7c3aed, #4f46e5);
}
</style>