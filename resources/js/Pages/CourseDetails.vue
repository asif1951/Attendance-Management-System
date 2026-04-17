<template>
    <Head title="Course Details" />

    <AuthenticatedLayout>
        <!-- Toast Notifications -->
        <div v-if="toast.visible" 
             class="fixed top-4 right-4 z-50 transform transition-all duration-500 ease-in-out"
             :class="[toast.visible ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0']">
            <div class="rounded-xl shadow-2xl overflow-hidden backdrop-blur-md"
                 :class="{
                     'bg-gradient-to-r from-green-500 to-emerald-600': toast.type === 'success',
                     'bg-gradient-to-r from-red-500 to-pink-600': toast.type === 'error',
                     'bg-gradient-to-r from-yellow-500 to-amber-600': toast.type === 'warning',
                     'bg-gradient-to-r from-blue-500 to-indigo-600': toast.type === 'info'
                 }">
                <div class="px-6 py-4 flex items-center gap-4 min-w-[300px] max-w-md">
                    <div class="text-2xl animate-pulse">
                        <span v-if="toast.type === 'success'">🎉</span>
                        <span v-else-if="toast.type === 'error'">😢</span>
                        <span v-else-if="toast.type === 'warning'">⚠️</span>
                        <span v-else-if="toast.type === 'info'">💡</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-white font-semibold">{{ toast.message }}</p>
                        <p v-if="toast.submessage" class="text-white/80 text-sm mt-1">{{ toast.submessage }}</p>
                    </div>
                    <button @click="hideToast" class="text-white/80 hover:text-white transition-all hover:scale-110">
                        <span class="text-xl">✕</span>
                    </button>
                </div>
                <div class="h-1 bg-white/30 w-full">
                    <div class="h-full bg-white transition-all duration-[5000ms] ease-linear"
                         :style="{ width: toast.progress + '%' }"></div>
                </div>
            </div>
        </div>
        
        <!-- Hero Section with Animated Gradient -->
        <div class="relative overflow-hidden rounded-3xl shadow-2xl mb-8 group">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 animate-gradient-x"></div>
            <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-all duration-500"></div>
            <div class="absolute top-0 -right-32 w-64 h-64 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-0 -left-32 w-64 h-64 bg-white/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
            <div class="relative z-10 px-8 py-12 md:px-12 lg:px-20">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="inline-flex items-center bg-white/20 backdrop-blur-md px-5 py-2.5 rounded-full mb-4 shadow-lg">
                            <span class="text-white text-sm font-semibold tracking-wide">✨ Attendance Management System ✨</span>
                        </div>
                        <h1 class="text-4xl md:text-5xl font-bold text-white mb-2 tracking-tight">
                            Course Attendance
                        </h1>
                        <p class="text-xl text-gray-100 font-medium">{{ courseCode || 'CSE-101' }} • {{ courseName }}</p>
                        <p class="text-sm text-gray-200 mt-2 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                            Stream: {{ streamName || 'Not specified' }}
                        </p>
                    </div>
                    <div class="hidden md:block text-7xl animate-float">
                        📋
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State with Skeleton -->
        <div v-if="loading" class="flex justify-center items-center py-20">
            <div class="relative">
                <div class="animate-spin rounded-full h-16 w-16 border-4 border-blue-200 border-t-blue-600"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-8 h-8 bg-blue-600 rounded-full animate-ping"></div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div v-else class="space-y-8">
            <!-- Quick Stats Cards with 3D Hover Effect -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="group relative overflow-hidden rounded-2xl shadow-xl p-6 text-white transform transition-all duration-500 hover:scale-105 hover:shadow-2xl"
                     :class="{
                         'bg-gradient-to-br from-blue-500 to-blue-700': true,
                     }">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">Total Students</p>
                                <p class="text-4xl font-bold mt-2">{{ filteredStudents.length }}</p>
                            </div>
                            <div class="text-5xl bg-white/20 rounded-full p-3 backdrop-blur-sm animate-bounce-slow">👥</div>
                        </div>
                        <div class="mt-4 h-1 bg-white/20 rounded-full overflow-hidden">
                            <div class="h-full bg-white/50 rounded-full transition-all duration-500" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="group relative overflow-hidden rounded-2xl shadow-xl p-6 text-white transform transition-all duration-500 hover:scale-105 hover:shadow-2xl"
                     :class="{
                         'bg-gradient-to-br from-green-500 to-green-700': true,
                     }">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium">Present Today</p>
                                <p class="text-4xl font-bold mt-2">{{ filteredPresentCount }}</p>
                            </div>
                            <div class="text-5xl bg-white/20 rounded-full p-3 backdrop-blur-sm animate-bounce-slow">✅</div>
                        </div>
                        <div class="mt-4 h-1 bg-white/20 rounded-full overflow-hidden">
                            <div class="h-full bg-white/50 rounded-full transition-all duration-500" :style="{ width: (filteredStudents.length ? (filteredPresentCount / filteredStudents.length) * 100 : 0) + '%' }"></div>
                        </div>
                    </div>
                </div>
                
                <div class="group relative overflow-hidden rounded-2xl shadow-xl p-6 text-white transform transition-all duration-500 hover:scale-105 hover:shadow-2xl"
                     :class="{
                         'bg-gradient-to-br from-red-500 to-red-700': true,
                     }">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-sm font-medium">Absent Today</p>
                                <p class="text-4xl font-bold mt-2">{{ filteredAbsentCount }}</p>
                            </div>
                            <div class="text-5xl bg-white/20 rounded-full p-3 backdrop-blur-sm animate-bounce-slow">❌</div>
                        </div>
                        <div class="mt-4 h-1 bg-white/20 rounded-full overflow-hidden">
                            <div class="h-full bg-white/50 rounded-full transition-all duration-500" :style="{ width: (filteredStudents.length ? (filteredAbsentCount / filteredStudents.length) * 100 : 0) + '%' }"></div>
                        </div>
                    </div>
                </div>
                
                <div class="group relative overflow-hidden rounded-2xl shadow-xl p-6 text-white transform transition-all duration-500 hover:scale-105 hover:shadow-2xl"
                     :class="{
                         'bg-gradient-to-br from-yellow-500 to-orange-600': true,
                     }">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-yellow-100 text-sm font-medium">Late Today</p>
                                <p class="text-4xl font-bold mt-2">{{ filteredLateCount }}</p>
                            </div>
                            <div class="text-5xl bg-white/20 rounded-full p-3 backdrop-blur-sm animate-bounce-slow">⏰</div>
                        </div>
                        <div class="mt-4 h-1 bg-white/20 rounded-full overflow-hidden">
                            <div class="h-full bg-white/50 rounded-full transition-all duration-500" :style="{ width: (filteredStudents.length ? (filteredLateCount / filteredStudents.length) * 100 : 0) + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Form Card with Glassmorphism -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl overflow-hidden border border-white/20">
                <!-- Card Header with Animated Gradient -->
                <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 px-8 py-6 relative overflow-hidden">
                    <div class="absolute inset-0 bg-white/10 transform -skew-x-12"></div>
                    <div class="relative flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                                <span class="text-3xl animate-pulse">📝</span> Take Attendance
                            </h2>
                            <p class="text-blue-100 mt-1">Mark attendance for today's class with ease</p>
                        </div>
                        <div class="bg-white/20 backdrop-blur-md px-5 py-2.5 rounded-xl shadow-lg">
                            <span class="text-white font-semibold">{{ currentDate }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <!-- Date Selection Row -->
                    <div class="flex flex-wrap items-center gap-6 mb-8 p-6 bg-gradient-to-r from-gray-50 to-blue-50 rounded-2xl">
                        <div class="flex-1 min-w-[250px]">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <span class="flex items-center gap-2">
                                    <span class="text-lg">📅</span> Select Attendance Date
                                </span>
                            </label>
                            <div class="relative">
                                <input 
                                    type="date" 
                                    v-model="selectedDate"
                                    @change="loadAttendanceForDate"
                                    class="w-full px-5 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-200 transition-all duration-300 bg-white shadow-sm hover:shadow-md"
                                >
                                <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none">
                                    📆
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3 mt-6">
                            <div class="flex items-center gap-2 bg-green-100 px-4 py-2 rounded-full shadow-sm">
                                <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                                <span class="text-sm text-gray-700 font-medium">Present: {{ filteredPresentCount }}</span>
                            </div>
                            <div class="flex items-center gap-2 bg-red-100 px-4 py-2 rounded-full shadow-sm">
                                <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                                <span class="text-sm text-gray-700 font-medium">Absent: {{ filteredAbsentCount }}</span>
                            </div>
                            <div class="flex items-center gap-2 bg-yellow-100 px-4 py-2 rounded-full shadow-sm">
                                <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
                                <span class="text-sm text-gray-700 font-medium">Late: {{ filteredLateCount }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter Section -->
                    <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
                        <div class="flex-1 min-w-[250px]">
                            <div class="relative group">
                                <input 
                                    type="text" 
                                    v-model="searchQuery"
                                    @input="debouncedSearch"
                                    placeholder="🔍 Search by Name, ID or Roll Number..."
                                    class="w-full px-5 py-3 pl-12 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-200 transition-all duration-300 bg-white shadow-sm hover:shadow-md"
                                >
                                <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-blue-500 transition-colors">🔍</span>
                                <button 
                                    v-if="searchQuery"
                                    @click="clearSearch"
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hover:scale-110 transition-all"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex gap-3">
                            <select 
                                v-model="percentageFilter"
                                @change="applyFilters"
                                class="px-5 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-200 transition-all duration-300 bg-white shadow-sm hover:shadow-md cursor-pointer"
                            >
                                <option value="all">📊 All Percentages</option>
                                <option value="high">✅ High (≥75%)</option>
                                <option value="medium">🟡 Medium (60-74%)</option>
                                <option value="low">⚠️ Low (&lt;60%)</option>
                                <option value="zero">📭 Zero (0%)</option>
                            </select>
                            
                            <button 
                                @click="clearFilters"
                                class="px-5 py-3 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 rounded-xl transition-all duration-300 flex items-center gap-2 shadow-sm hover:shadow-md transform hover:scale-105"
                            >
                                <span class="text-lg">🔄</span> Clear
                            </button>
                        </div>
                    </div>

                    <!-- Mark All Present Button -->
                    <div class="mb-6 flex justify-end">
                        <button 
                            @click="toggleAllPresent"
                            class="group relative overflow-hidden bg-gradient-to-r from-green-500 to-teal-500 text-white px-8 py-3 rounded-xl font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300"
                            :class="{ 'opacity-90': allFilteredStudentsPresent }"
                        >
                            <span class="relative z-10 flex items-center gap-2">
                                <span class="text-xl group-hover:rotate-12 transition-transform">{{ allFilteredStudentsPresent ? '🔄' : '✅' }}</span>
                                <span>{{ allFilteredStudentsPresent ? 'Unmark All' : 'Mark All Present' }}</span>
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-green-600 to-teal-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </button>
                    </div>

                    <!-- Students Table -->
                    <div class="overflow-x-auto rounded-2xl border border-gray-200 shadow-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Student ID</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Student Name</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Level/Session</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Attendance %</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Attendance Status</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Remarks</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr 
                                    v-for="student in filteredStudents" 
                                    :key="student.id" 
                                    class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 transition-all duration-300 group"
                                    :class="{
                                        'bg-gradient-to-r from-green-50 to-emerald-50': student.status === 'present',
                                        'bg-gradient-to-r from-red-50 to-pink-50': student.status === 'absent',
                                        'bg-gradient-to-r from-yellow-50 to-amber-50': student.status === 'late'
                                    }"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mr-3 shadow-md group-hover:scale-110 transition-transform">
                                                <span class="text-white text-xs font-bold">{{ student.id }}</span>
                                            </div>
                                            <div class="text-sm font-bold text-gray-900">{{ student.roll }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-semibold">{{ student.name }}</div>
                                        <div class="text-xs text-gray-500">{{ student.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Level: {{ student.level || 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">Session: {{ student.session || 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col items-start">
                                            <div class="text-sm font-bold" 
                                                 :class="{
                                                     'text-green-600': (student.percentage || 0) >= 75,
                                                     'text-yellow-600': (student.percentage || 0) >= 60 && (student.percentage || 0) < 75,
                                                     'text-red-600': (student.percentage || 0) < 60
                                                 }">
                                                {{ student.percentage || 0 }}%
                                            </div>
                                            <div class="w-24 h-2 bg-gray-200 rounded-full mt-1 overflow-hidden">
                                                <div class="h-2 rounded-full transition-all duration-500" 
                                                     :class="{
                                                         'bg-green-500': (student.percentage || 0) >= 75,
                                                         'bg-yellow-500': (student.percentage || 0) >= 60 && (student.percentage || 0) < 75,
                                                         'bg-red-500': (student.percentage || 0) < 60
                                                     }"
                                                     :style="{ width: (student.percentage || 0) + '%' }">
                                                </div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ student.attended_classes || 0 }}/{{ student.total_classes || 0 }} classes
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex gap-3">
                                            <label class="relative flex items-center cursor-pointer group">
                                                <input 
                                                    type="checkbox" 
                                                    :checked="student.status === 'present'"
                                                    @change="markAttendance(student.id, $event.target.checked ? 'present' : '')"
                                                    class="w-5 h-5 text-green-600 bg-white border-2 border-gray-300 rounded-lg focus:ring-green-500 focus:ring-2 transition-all duration-200 cursor-pointer"
                                                >
                                                <span class="ml-2 text-sm text-gray-700 group-hover:text-green-600 transition-colors font-medium">Present</span>
                                            </label>
                                            
                                            <label class="relative flex items-center cursor-pointer group">
                                                <input 
                                                    type="checkbox" 
                                                    :checked="student.status === 'absent'"
                                                    @change="markAttendance(student.id, $event.target.checked ? 'absent' : '')"
                                                    class="w-5 h-5 text-red-600 bg-white border-2 border-gray-300 rounded-lg focus:ring-red-500 focus:ring-2 transition-all duration-200 cursor-pointer"
                                                >
                                                <span class="ml-2 text-sm text-gray-700 group-hover:text-red-600 transition-colors font-medium">Absent</span>
                                            </label>
                                            
                                            <label class="relative flex items-center cursor-pointer group">
                                                <input 
                                                    type="checkbox" 
                                                    :checked="student.status === 'late'"
                                                    @change="markAttendance(student.id, $event.target.checked ? 'late' : '')"
                                                    class="w-5 h-5 text-yellow-600 bg-white border-2 border-gray-300 rounded-lg focus:ring-yellow-500 focus:ring-2 transition-all duration-200 cursor-pointer"
                                                >
                                                <span class="ml-2 text-sm text-gray-700 group-hover:text-yellow-600 transition-colors font-medium">Late</span>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input 
                                            v-model="student.remarks"
                                            type="text" 
                                            placeholder="Add remarks..."
                                            class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-300 text-sm"
                                        >
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button 
                                            v-if="(student.percentage || 0) < 60 && (student.percentage || 0) > 0"
                                            @click="sendEmailToStudent(student)"
                                            :disabled="emailLoading"
                                            class="bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white px-4 py-2 rounded-xl text-xs font-semibold transition-all duration-300 flex items-center gap-2 shadow-md hover:shadow-lg transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <span v-if="emailLoading" class="animate-spin">⏳</span>
                                            <span v-else>📧</span>
                                            <span>Email</span>
                                        </button>
                                        <div v-else class="text-xs text-gray-400 italic bg-gray-100 px-3 py-2 rounded-lg">
                                            Attendance OK
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- No Students Message -->
                    <div v-if="filteredStudents.length === 0" class="text-center py-16">
                        <div class="text-8xl mb-4 animate-bounce">📭</div>
                        <h3 class="text-2xl font-bold text-gray-700 mb-2">No Students Found</h3>
                        <p class="text-gray-500">Try adjusting your search or filter criteria.</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-4 mt-8">
                        <button 
                            @click="submitAttendance"
                            :disabled="submitting"
                            class="flex-1 min-w-[200px] bg-gradient-to-r from-green-500 to-emerald-600 text-white px-8 py-4 rounded-xl font-bold shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-3 group disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="submitting" class="animate-spin">⏳</span>
                            <span v-else class="text-2xl group-hover:scale-110 transition-transform">💾</span>
                            <span>{{ submitting ? 'Saving...' : 'Save Attendance' }}</span>
                        </button>
                        
                        <button 
                            @click="resetAttendance"
                            class="flex-1 min-w-[200px] bg-gradient-to-r from-gray-500 to-gray-600 text-white px-8 py-4 rounded-xl font-bold shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-3 group"
                        >
                            <span class="text-2xl group-hover:rotate-180 transition-transform">🔄</span>
                            <span>Reset</span>
                        </button>
                        
                        <button 
                            @click="generateReport"
                            class="flex-1 min-w-[200px] bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-8 py-4 rounded-xl font-bold shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-3 group"
                        >
                            <span class="text-2xl group-hover:scale-110 transition-transform">📊</span>
                            <span>Generate Report</span>
                        </button>
                        
                        <button 
                            v-if="filteredLowAttendanceStudents.length > 0"
                            @click="openEmailModal"
                            :disabled="emailLoading"
                            class="flex-1 min-w-[200px] bg-gradient-to-r from-red-500 to-pink-600 text-white px-8 py-4 rounded-xl font-bold shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-3 group disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="emailLoading" class="animate-spin">⏳</span>
                            <span v-else class="text-2xl group-hover:scale-110 transition-transform">📧</span>
                            <span>{{ emailLoading ? 'Sending...' : `Email All (${filteredLowAttendanceStudents.length})` }}</span>
                        </button>
                        
                        <button 
                            @click="printReport"
                            class="flex-1 min-w-[200px] bg-gradient-to-r from-purple-500 to-purple-700 text-white px-8 py-4 rounded-xl font-bold shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-3 group"
                        >
                            <span class="text-2xl group-hover:scale-110 transition-transform">🖨️</span>
                            <span>Print Report</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Attendance Records -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl p-8 border border-white/20">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                    <h3 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <span class="text-3xl animate-pulse">📅</span> Recent Attendance Records
                    </h3>
                    
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2 bg-gray-100 rounded-xl p-1">
                            <input 
                                type="date" 
                                v-model="filters.start_date"
                                @change="applyFilters"
                                class="px-3 py-2 border-0 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white"
                            >
                            <span class="text-gray-400">→</span>
                            <input 
                                type="date" 
                                v-model="filters.end_date"
                                @change="applyFilters"
                                class="px-3 py-2 border-0 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white"
                            >
                        </div>
                        
                        <select 
                            v-model="filters.status"
                            @change="applyFilters"
                            class="px-3 py-2 border-0 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 bg-white shadow-sm"
                        >
                            <option value="all">📊 All Records</option>
                            <option value="high_attendance">✅ High Attendance (≥75%)</option>
                            <option value="medium_attendance">🟡 Medium (60-74%)</option>
                            <option value="low_attendance">⚠️ Low (&lt;60%)</option>
                        </select>
                        
                        <select 
                            v-model="perPage"
                            @change="loadAttendanceRecords"
                            class="px-3 py-2 border-0 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 bg-white shadow-sm"
                        >
                            <option value="6">6 per page</option>
                            <option value="12">12 per page</option>
                            <option value="24">24 per page</option>
                            <option value="48">48 per page</option>
                        </select>
                        
                        <button 
                            @click="clearRecordFilters"
                            class="px-3 py-2 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 rounded-xl text-sm transition-all flex items-center gap-1 shadow-sm"
                        >
                            🔄 Clear
                        </button>
                    </div>
                </div>
                
                <div v-if="attendanceLoading" class="flex justify-center items-center py-12">
                    <div class="relative">
                        <div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-200 border-t-blue-600"></div>
                    </div>
                </div>
                
                <div v-else-if="displayedAttendanceRecords.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="record in displayedAttendanceRecords" 
                        :key="record.id"
                        class="group relative overflow-hidden rounded-2xl p-6 transition-all duration-500 hover:shadow-2xl hover:-translate-y-2 cursor-pointer"
                        :class="{
                            'bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200': record.present > record.absent && record.present > record.late,
                            'bg-gradient-to-br from-red-50 to-pink-50 border-2 border-red-200': record.absent > record.present && record.absent > record.late,
                            'bg-gradient-to-br from-yellow-50 to-amber-50 border-2 border-yellow-200': record.late > record.present && record.late > record.absent
                        }"
                    >
                        <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-white/20 to-transparent rounded-full -mr-12 -mt-12 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-3xl">📆</span>
                                    <span class="text-lg font-bold text-gray-800">{{ record.date }}</span>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-bold shadow-sm"
                                     :class="{
                                         'bg-green-500 text-white': record.present > record.absent && record.present > record.late,
                                         'bg-red-500 text-white': record.absent > record.present && record.absent > record.late,
                                         'bg-yellow-500 text-white': record.late > record.present && record.late > record.absent
                                     }">
                                    {{ record.status }}
                                </span>
                            </div>
                            
                            <div class="space-y-3 mb-4">
                                <div class="flex justify-between items-center p-2 bg-white/50 rounded-lg">
                                    <span class="text-gray-700 font-medium">✅ Present:</span>
                                    <span class="font-bold text-green-600 text-lg">{{ record.present }}</span>
                                </div>
                                <div class="flex justify-between items-center p-2 bg-white/50 rounded-lg">
                                    <span class="text-gray-700 font-medium">❌ Absent:</span>
                                    <span class="font-bold text-red-600 text-lg">{{ record.absent }}</span>
                                </div>
                                <div class="flex justify-between items-center p-2 bg-white/50 rounded-lg">
                                    <span class="text-gray-700 font-medium">⏰ Late:</span>
                                    <span class="font-bold text-yellow-600 text-lg">{{ record.late }}</span>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 mt-4">
                                <button @click="viewRecordDetails(record)" class="flex-1 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl font-medium hover:shadow-lg transition-all text-sm transform hover:scale-105">
                                    View Details
                                </button>
                                <button @click="printSpecificRecord(record)" class="px-4 py-2.5 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-xl hover:shadow-lg transition-all transform hover:scale-105" title="Print this record">
                                    🖨️
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-12 text-gray-500">
                    <div class="text-6xl mb-4">📭</div>
                    <p>No attendance records found</p>
                </div>
                
                <div v-if="pagination.last_page > 1" class="flex justify-center items-center gap-2 mt-6 pt-4 border-t border-gray-200">
                    <button 
                        @click="changePage(pagination.current_page - 1)"
                        :disabled="pagination.current_page <= 1"
                        class="px-4 py-2 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 rounded-xl text-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                    >
                        ← Previous
                    </button>
                    
                    <div class="flex gap-1">
                        <button 
                            v-for="page in visiblePages"
                            :key="page"
                            @click="changePage(page)"
                            :class="[
                                'px-4 py-2 rounded-xl text-sm transition-all shadow-sm font-medium',
                                pagination.current_page === page 
                                    ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white transform scale-105' 
                                    : 'bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700'
                            ]"
                        >
                            {{ page }}
                        </button>
                    </div>
                    
                    <button 
                        @click="changePage(pagination.current_page + 1)"
                        :disabled="pagination.current_page >= pagination.last_page"
                        class="px-4 py-2 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 rounded-xl text-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                    >
                        Next →
                    </button>
                </div>
                
                <div v-if="displayedAttendanceRecords.length > 0" class="text-center mt-4 text-sm text-gray-500">
                    Showing {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }} to {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of {{ pagination.total }} records
                </div>
            </div>

            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button @click="exportToCSV" class="group relative overflow-hidden rounded-2xl p-6 text-center transition-all duration-500 hover:shadow-2xl transform hover:-translate-y-2"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="relative z-10">
                        <div class="text-4xl mb-2 text-white group-hover:scale-110 transition-transform inline-block">📤</div>
                        <div class="font-bold text-white">Export CSV</div>
                        <div class="text-xs text-white/80 mt-1">Download data</div>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                </button>
                
                <button 
                    @click="openEmailModal"
                    :disabled="filteredLowAttendanceStudents.length === 0 || emailLoading"
                    class="group relative overflow-hidden rounded-2xl p-6 text-center transition-all duration-500 hover:shadow-2xl transform hover:-translate-y-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="{
                        'opacity-50 cursor-not-allowed': filteredLowAttendanceStudents.length === 0 || emailLoading
                    }"
                    style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="relative z-10">
                        <div class="text-4xl mb-2 text-white group-hover:scale-110 transition-transform inline-block">{{ emailLoading ? '⏳' : '📧' }}</div>
                        <div class="font-bold text-white">{{ emailLoading ? 'Sending...' : 'Email Absent' }}</div>
                        <div class="text-xs text-white/80 mt-1">{{ emailLoading ? 'Please wait' : 'Send notifications' }}</div>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                </button>
                
                <button @click="showAnalytics" class="group relative overflow-hidden rounded-2xl p-6 text-center transition-all duration-500 hover:shadow-2xl transform hover:-translate-y-2"
                        style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <div class="relative z-10">
                        <div class="text-4xl mb-2 text-white group-hover:scale-110 transition-transform inline-block">📊</div>
                        <div class="font-bold text-white">Analytics</div>
                        <div class="text-xs text-white/80 mt-1">View statistics</div>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                </button>
                
                <button @click="printSummary" class="group relative overflow-hidden rounded-2xl p-6 text-center transition-all duration-500 hover:shadow-2xl transform hover:-translate-y-2"
                        style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                    <div class="relative z-10">
                        <div class="text-4xl mb-2 text-gray-800 group-hover:scale-110 transition-transform inline-block">📋</div>
                        <div class="font-bold text-gray-800">Print Summary</div>
                        <div class="text-xs text-gray-600 mt-1">Quick report</div>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                </button>
            </div>
        </div>

        <!-- Email Modal -->
        <div v-if="showEmailModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fadeIn">
            <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full transform animate-slideUp">
                <div class="bg-gradient-to-r from-red-600 to-pink-600 px-6 py-4 rounded-t-3xl">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <span class="text-2xl">📧</span> Send Email Notifications
                    </h2>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-gray-700 mb-2">You are about to send email notifications to <span class="font-bold text-red-600 text-lg">{{ filteredLowAttendanceStudents.length }}</span> students with attendance below 60%.</p>
                        
                        <div class="max-h-60 overflow-y-auto border-2 border-gray-100 rounded-xl p-3 mb-4">
                            <h3 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">📝 Recipients:</h3>
                            <ul class="space-y-2">
                                <li v-for="student in filteredLowAttendanceStudents" :key="student.id" class="text-sm border-b border-gray-100 pb-2 hover:bg-red-50 p-2 rounded-lg transition-colors">
                                    <div class="flex justify-between">
                                        <span class="font-medium">{{ student.name }}</span>
                                        <span class="text-red-600 font-bold">{{ student.percentage }}%</span>
                                    </div>
                                    <div class="text-xs text-gray-500">{{ student.email }}</div>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-3">
                            <p class="text-sm text-yellow-800 flex items-start gap-2">
                                <span class="text-lg">💡</span>
                                <span><span class="font-bold">Note:</span> Each student will receive an email notification about their low attendance status.</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6">
                        <button 
                            @click="sendEmailToAllLowAttendance"
                            :disabled="emailLoading"
                            class="flex-1 bg-gradient-to-r from-red-600 to-pink-600 text-white px-6 py-3 rounded-xl font-bold hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="emailLoading" class="animate-spin">⏳</span>
                            <span v-else>📧</span>
                            <span>{{ emailLoading ? 'Sending...' : 'Send Emails' }}</span>
                        </button>
                        <button 
                            @click="showEmailModal = false"
                            :disabled="emailLoading"
                            class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-bold hover:bg-gray-300 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Record Details Modal -->
        <div v-if="showRecordDetailsModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fadeIn">
            <div class="bg-white rounded-3xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto transform animate-slideUp">
                <div class="sticky top-0 bg-white border-b-2 border-gray-100 px-6 py-4 flex justify-between items-center rounded-t-3xl">
                    <h2 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Attendance Record Details</h2>
                    <div class="flex gap-2">
                        <button @click="printRecordDetails" class="bg-gradient-to-r from-purple-600 to-purple-700 text-white px-5 py-2 rounded-xl hover:shadow-lg transition-all flex items-center gap-2 transform hover:scale-105">
                            <span>🖨️</span> Print
                        </button>
                        <button @click="closeRecordDetailsModal" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-xl hover:bg-gray-300 transition-all transform hover:scale-105">
                            Close
                        </button>
                    </div>
                </div>
                <div class="p-6" id="record-details-content">
                    <div v-if="recordDetails.details && recordDetails.details.length > 0" class="print-content">
                        <div class="text-center mb-8">
                            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">{{ courseName }}</h1>
                            <h2 class="text-xl text-gray-600">{{ courseCode }}</h2>
                            <p class="text-gray-500">Stream: {{ streamName || 'Not specified' }}</p>
                            <p class="text-gray-500 font-semibold mt-2">Date: {{ recordDetails.date || selectedRecord?.date || '' }}</p>
                        </div>
                        
                        <div class="grid grid-cols-4 gap-4 mb-8">
                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-4 text-center text-white shadow-lg">
                                <div class="text-3xl font-bold">{{ recordDetails.total_students || recordDetails.details.length || 0 }}</div>
                                <div class="text-sm text-blue-100">Total Students</div>
                            </div>
                            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-4 text-center text-white shadow-lg">
                                <div class="text-3xl font-bold">{{ recordDetails.present_count || 0 }}</div>
                                <div class="text-sm text-green-100">Present</div>
                            </div>
                            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-4 text-center text-white shadow-lg">
                                <div class="text-3xl font-bold">{{ recordDetails.absent_count || 0 }}</div>
                                <div class="text-sm text-red-100">Absent</div>
                            </div>
                            <div class="bg-gradient-to-br from-yellow-500 to-orange-500 rounded-xl p-4 text-center text-white shadow-lg">
                                <div class="text-3xl font-bold">{{ recordDetails.late_count || 0 }}</div>
                                <div class="text-sm text-yellow-100">Late</div>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto rounded-xl border-2 border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">ID</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Name</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Level/Session</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="detail in recordDetails.details" :key="detail.student_id" class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 text-sm font-medium">{{ detail.student_id }}</td>
                                        <td class="px-4 py-3 text-sm">{{ detail.name }}</td>
                                        <td class="px-4 py-3 text-sm">{{ detail.level || 'N/A' }} / {{ detail.session || 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span :class="{
                                                'text-green-600 font-bold': detail.status === 'present',
                                                'text-red-600 font-bold': detail.status === 'absent',
                                                'text-yellow-600 font-bold': detail.status === 'late'
                                            }" class="px-2 py-1 rounded-full">
                                                {{ detail.status || 'Not marked' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">{{ detail.remarks || '-' }}</td>
                                    </tr>
                                </tbody>
                             </table>
                        </div>
                        
                        <div class="mt-8 text-sm text-gray-500 text-center">
                            <p>Generated on: {{ new Date().toLocaleString() }}</p>
                            <p>Generated by: {{ userName || 'Administrator' }}</p>
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <div class="relative inline-block">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600"></div>
                        </div>
                        <p class="mt-4 text-gray-600">Loading record details...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print Modal -->
        <div v-if="showPrintModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fadeIn">
            <div class="bg-white rounded-3xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto transform animate-slideUp">
                <div class="sticky top-0 bg-white border-b-2 border-gray-100 px-6 py-4 flex justify-between items-center rounded-t-3xl">
                    <h2 class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Print Preview</h2>
                    <div class="flex gap-2">
                        <button @click="doPrint" class="bg-gradient-to-r from-purple-600 to-purple-700 text-white px-5 py-2 rounded-xl hover:shadow-lg transition-all flex items-center gap-2 transform hover:scale-105">
                            <span>🖨️</span> Print
                        </button>
                        <button @click="showPrintModal = false" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-xl hover:bg-gray-300 transition-all transform hover:scale-105">
                            Close
                        </button>
                    </div>
                </div>
                <div class="p-6" id="printable-content">
                    <div class="print-content">
                        <div class="text-center mb-8">
                            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">{{ courseName }}</h1>
                            <h2 class="text-xl text-gray-600">{{ courseCode }}</h2>
                            <p class="text-gray-500">Stream: {{ streamName || 'Not specified' }}</p>
                            <p class="text-gray-500">Date: {{ selectedDate || new Date().toLocaleDateString() }}</p>
                        </div>
                        
                        <div class="grid grid-cols-4 gap-4 mb-8">
                            <div class="bg-blue-100 p-4 rounded-xl text-center">
                                <div class="text-2xl font-bold text-blue-800">{{ filteredStudents.length }}</div>
                                <div class="text-sm text-blue-600">Total Students</div>
                            </div>
                            <div class="bg-green-100 p-4 rounded-xl text-center">
                                <div class="text-2xl font-bold text-green-800">{{ filteredPresentCount }}</div>
                                <div class="text-sm text-green-600">Present</div>
                            </div>
                            <div class="bg-red-100 p-4 rounded-xl text-center">
                                <div class="text-2xl font-bold text-red-800">{{ filteredAbsentCount }}</div>
                                <div class="text-sm text-red-600">Absent</div>
                            </div>
                            <div class="bg-yellow-100 p-4 rounded-xl text-center">
                                <div class="text-2xl font-bold text-yellow-800">{{ filteredLateCount }}</div>
                                <div class="text-sm text-yellow-600">Late</div>
                            </div>
                        </div>
                        
                        <div v-if="filteredLowAttendanceStudents.length > 0" class="mb-8 p-4 bg-red-50 rounded-xl">
                            <h3 class="text-lg font-semibold text-red-800 mb-3 flex items-center gap-2">
                                <span>⚠️</span> Students with Low Attendance (&lt;60%)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div v-for="student in filteredLowAttendanceStudents" :key="student.id" class="text-sm">
                                    • {{ student.name }} - {{ student.percentage }}%
                                </div>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto rounded-xl border">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Level/Session</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="student in filteredStudents" :key="student.id">
                                        <td class="px-4 py-2 text-sm">{{ student.roll }}</td>
                                        <td class="px-4 py-2 text-sm">{{ student.name }}</td>
                                        <td class="px-4 py-2 text-sm">{{ student.level || 'N/A' }} / {{ student.session || 'N/A' }}</td>
                                        <td class="px-4 py-2 text-sm">
                                            <span :class="{
                                                'text-green-600 font-semibold': student.status === 'present',
                                                'text-red-600 font-semibold': student.status === 'absent',
                                                'text-yellow-600 font-semibold': student.status === 'late'
                                            }">
                                                {{ student.status || 'Not marked' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-sm">{{ student.remarks || '-' }}</td>
                                    </tr>
                                </tbody>
                             </table>
                        </div>
                        
                        <div class="mt-8 text-sm text-gray-500 text-center">
                            <p>Generated on: {{ new Date().toLocaleString() }}</p>
                            <p>Generated by: {{ userName || 'Administrator' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    courseId: {
        type: [String, Number],
        required: true
    },
    students: {
        type: Array,
        default: () => []
    },
    attendanceRecords: {
        type: Array,
        default: () => []
    },
    attendanceRecordsPaginated: {
        type: Object,
        default: () => ({})
    },
    totalStudents: {
        type: Number,
        default: 0
    },
    courseName: {
        type: String,
        default: 'Course'
    },
    courseCode: {
        type: String,
        default: 'CSE-101'
    },
    streamName: {
        type: String,
        default: ''
    },
    userName: {
        type: String,
        default: 'Administrator'
    }
});

// Toast State
const toast = ref({
    visible: false,
    message: '',
    submessage: '',
    type: 'success',
    progress: 100,
    timeout: null
});

// Modal states
const showPrintModal = ref(false);
const showEmailModal = ref(false);
const showRecordDetailsModal = ref(false);
const selectedRecord = ref(null);
const recordDetails = ref({
    details: [],
    total_students: 0,
    present_count: 0,
    absent_count: 0,
    late_count: 0,
    date: ''
});

// Loading states
const loading = ref(false);
const submitting = ref(false);
const emailLoading = ref(false);
const attendanceLoading = ref(false);
const studentFilterLoading = ref(false);
const recordDetailsLoading = ref(false);

// Filter and Pagination states for attendance records
const filters = ref({
    start_date: '',
    end_date: '',
    status: 'all'
});
const perPage = ref(6);
const pagination = ref({
    current_page: 1,
    per_page: 6,
    total: 0,
    last_page: 1
});
const attendanceRecordsData = ref([]);

// Student filter states
const searchQuery = ref('');
const percentageFilter = ref('all');
const filteredStudentsData = ref([]);
let searchTimeout = null;

// Toast Functions
const showToast = ({ message, submessage = '', type = 'success', duration = 5000 }) => {
    if (toast.value.timeout) {
        clearTimeout(toast.value.timeout);
    }
    
    toast.value = {
        ...toast.value,
        visible: true,
        message,
        submessage,
        type,
        progress: 100
    };
    
    const startTime = Date.now();
    const interval = setInterval(() => {
        const elapsed = Date.now() - startTime;
        toast.value.progress = Math.max(0, 100 - (elapsed / duration) * 100);
    }, 100);
    
    toast.value.timeout = setTimeout(() => {
        clearInterval(interval);
        hideToast();
    }, duration);
};

const hideToast = () => {
    toast.value.visible = false;
    if (toast.value.timeout) {
        clearTimeout(toast.value.timeout);
    }
};

const toastSuccess = (message, submessage = '', duration = 5000) => {
    showToast({ message, submessage, type: 'success', duration });
};

const toastError = (message, submessage = '', duration = 5000) => {
    showToast({ message, submessage, type: 'error', duration });
};

const toastWarning = (message, submessage = '', duration = 5000) => {
    showToast({ message, submessage, type: 'warning', duration });
};

const toastInfo = (message, submessage = '', duration = 5000) => {
    showToast({ message, submessage, type: 'info', duration });
};

// Current date for display
const currentDate = new Date().toLocaleDateString('en-US', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
});

// Selected date for attendance
const selectedDate = ref(new Date().toISOString().split('T')[0]);

// Filtered students data
const filteredStudents = computed(() => filteredStudentsData.value);

// Filtered counts
const filteredPresentCount = computed(() => filteredStudents.value.filter(s => s.status === 'present').length);
const filteredAbsentCount = computed(() => filteredStudents.value.filter(s => s.status === 'absent').length);
const filteredLateCount = computed(() => filteredStudents.value.filter(s => s.status === 'late').length);

// Check if all filtered students are marked as present
const allFilteredStudentsPresent = computed(() => {
    return filteredStudents.value.length > 0 && filteredStudents.value.every(s => s.status === 'present');
});

// Filtered students with low attendance (< 60%)
const filteredLowAttendanceStudents = computed(() => {
    return filteredStudents.value.filter(s => (s.percentage || 0) < 60 && (s.percentage || 0) > 0);
});

// Computed property for displayed records
const displayedAttendanceRecords = computed(() => attendanceRecordsData.value);

// Computed property for visible pages in pagination
const visiblePages = computed(() => {
    const total = pagination.value.last_page;
    const current = pagination.value.current_page;
    const delta = 2;
    const range = [];
    
    for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
        range.push(i);
    }
    
    if (current - delta > 2) {
        range.unshift('...');
    }
    if (current + delta < total - 1) {
        range.push('...');
    }
    
    range.unshift(1);
    if (total !== 1) {
        range.push(total);
    }
    
    return range.filter((v, i, a) => a.indexOf(v) === i);
});

// Load filtered students from backend
const loadFilteredStudents = async () => {
    studentFilterLoading.value = true;
    
    try {
        const response = await axios.get(`/course/${props.courseId}/filtered-students`, {
            params: {
                search: searchQuery.value,
                percentage_filter: percentageFilter.value
            }
        });
        
        if (response.data.success) {
            filteredStudentsData.value = response.data.students;
        }
    } catch (error) {
        console.error('Error loading filtered students:', error);
        toastError('Failed to load students', 'Please try again');
    } finally {
        studentFilterLoading.value = false;
    }
};

// Debounced search function
const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        loadFilteredStudents();
    }, 500);
};

// Apply filters
const applyFilters = () => {
    loadFilteredStudents();
};

// Clear all filters
const clearFilters = () => {
    searchQuery.value = '';
    percentageFilter.value = 'all';
    loadFilteredStudents();
};

// Clear search
const clearSearch = () => {
    searchQuery.value = '';
    loadFilteredStudents();
};

// Load attendance records with filters and pagination
const loadAttendanceRecords = async () => {
    attendanceLoading.value = true;
    
    try {
        const response = await axios.get(`/course/${props.courseId}/attendance-records`, {
            params: {
                page: pagination.value.current_page,
                per_page: perPage.value,
                start_date: filters.value.start_date,
                end_date: filters.value.end_date,
                status: filters.value.status
            }
        });
        
        if (response.data.success) {
            attendanceRecordsData.value = response.data.data;
            pagination.value = response.data.pagination;
        }
    } catch (error) {
        console.error('Error loading attendance records:', error);
        toastError('Failed to load attendance records', 'Please try again');
    } finally {
        attendanceLoading.value = false;
    }
};

// Apply record filters
const applyRecordFilters = () => {
    pagination.value.current_page = 1;
    loadAttendanceRecords();
};

// Clear record filters
const clearRecordFilters = () => {
    filters.value = {
        start_date: '',
        end_date: '',
        status: 'all'
    };
    pagination.value.current_page = 1;
    loadAttendanceRecords();
};

// Change page
const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        pagination.value.current_page = page;
        loadAttendanceRecords();
    }
};

// Load initial data on mount
onMounted(() => {
    loadFilteredStudents();
    loadAttendanceRecords();
});

// Function to load attendance for selected date
const loadAttendanceForDate = () => {
    if (!selectedDate.value) return;
    
    loading.value = true;
    router.get(`/course/${props.courseId}/attendance/report`, { 
        date: selectedDate.value 
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.attendanceData) {
                const reportData = page.props.attendanceData;
                
                filteredStudentsData.value = filteredStudentsData.value.map(student => {
                    const attendance = reportData.details.find(d => d.student_id === student.roll);
                    if (attendance) {
                        return {
                            ...student,
                            status: attendance.status,
                            remarks: attendance.remarks,
                            percentage: attendance.percentage || student.percentage,
                            total_classes: attendance.total_classes || student.total_classes,
                            attended_classes: attendance.attended_classes || student.attended_classes
                        };
                    }
                    return {
                        ...student,
                        status: '',
                        remarks: ''
                    };
                });
            }
            
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
            toastError('Failed to load attendance data', 'Please try again');
        }
    });
};

// Function to mark attendance
const markAttendance = (studentId, status) => {
    const index = filteredStudentsData.value.findIndex(s => s.id === studentId);
    if (index !== -1) {
        if (filteredStudentsData.value[index].status === status) {
            filteredStudentsData.value[index].status = '';
        } else {
            filteredStudentsData.value[index].status = status;
        }
        filteredStudentsData.value = [...filteredStudentsData.value];
    }
};

// Function to toggle all students present/unmark
const toggleAllPresent = () => {
    if (allFilteredStudentsPresent.value) {
        filteredStudentsData.value = filteredStudentsData.value.map(student => ({
            ...student,
            status: ''
        }));
    } else {
        filteredStudentsData.value = filteredStudentsData.value.map(student => ({
            ...student,
            status: 'present'
        }));
    }
};

// Function to submit attendance and reload page
const submitAttendance = () => {
    if (!selectedDate.value) {
        toastWarning('Please select a date', 'Choose a date for attendance');
        return;
    }

    const hasAttendance = filteredStudentsData.value.some(s => s.status);
    if (!hasAttendance) {
        toastWarning('No attendance marked', 'Please mark at least one student');
        return;
    }

    submitting.value = true;

    const attendanceData = filteredStudentsData.value.map(student => ({
        student_id: student.id,
        status: student.status || '',
        remarks: student.remarks || ''
    }));

    axios.post(`/course/${props.courseId}/attendance`, {
        date: selectedDate.value,
        attendance: attendanceData
    })
    .then(response => {
        const data = response.data;
        
        if (data.success) {
            toastSuccess(
                'Attendance Saved Successfully! ✅',
                'Page will reload to update data',
                3000
            );
            
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            submitting.value = false;
            toastError('Failed to save attendance', data.message || 'Unknown error');
        }
    })
    .catch(error => {
        submitting.value = false;
        console.error('Attendance error:', error);
        
        if (error.response?.status === 419) {
            toastError('Session expired', 'Please refresh the page and try again');
        } else {
            const errorMessage = error.response?.data?.message || error.message || 'Please try again';
            toastError('Failed to save attendance', errorMessage);
        }
    });
};

// Function to reset attendance
const resetAttendance = () => {
    if (confirm('Are you sure you want to reset all attendance marks?')) {
        filteredStudentsData.value = filteredStudentsData.value.map(student => ({
            ...student,
            status: '',
            remarks: ''
        }));
        toastInfo('Attendance reset', 'All marks have been cleared');
    }
};

// Function to generate report
const generateReport = () => {
    loading.value = true;
    router.get(`/course/${props.courseId}/attendance/report`, { 
        date: selectedDate.value,
        download: true 
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            loading.value = false;
            
            if (page.props.attendanceData?.data) {
                const data = page.props.attendanceData.data;
                toastSuccess(
                    'Report Generated',
                    `Date: ${data.date} | Present: ${data.present} | Absent: ${data.absent} | Late: ${data.late}`,
                    8000
                );
            } else {
                toastInfo('Report Generated', 'Check console for details');
            }
        },
        onError: () => {
            loading.value = false;
            toastError('Failed to generate report', 'Please try again');
        }
    });
};

// Function to send email to a specific student
const sendEmailToStudent = async (student) => {
    if (!student.email) {
        toastError('No email address found', `Cannot send email to ${student.name}`);
        return;
    }
    
    emailLoading.value = true;
    
    try {
        const response = await axios.post(`/course/${props.courseId}/send-email`, {
            student_id: student.id,
            student_email: student.email,
            student_name: student.name,
            attendance_percentage: student.percentage || 0,
            course_name: props.courseName,
            course_code: props.courseCode
        });
        
        if (response.data.success) {
            toastSuccess(
                'Email Sent Successfully',
                `Notification sent to ${student.name} about low attendance (${student.percentage || 0}%)`,
                5000
            );
        } else {
            toastError('Failed to send email', response.data.message || 'Unknown error');
        }
    } catch (error) {
        console.error('Email error:', error);
        const errorMessage = error.response?.data?.message || error.message || 'Failed to send email';
        toastError('Failed to send email', errorMessage);
    } finally {
        emailLoading.value = false;
    }
};

// Function to send email to all students with low attendance
const sendEmailToAllLowAttendance = async () => {
    if (filteredLowAttendanceStudents.value.length === 0) {
        toastWarning('No students with low attendance', 'All students have attendance above 60%');
        showEmailModal.value = false;
        return;
    }
    
    emailLoading.value = true;
    
    try {
        const response = await axios.post(`/course/${props.courseId}/send-bulk-email`, {
            students: filteredLowAttendanceStudents.value.map(s => ({
                id: s.id,
                email: s.email,
                name: s.name,
                percentage: s.percentage || 0
            })),
            course_name: props.courseName,
            course_code: props.courseCode
        });
        
        showEmailModal.value = false;
        
        if (response.data.success) {
            const successCount = response.data.sent_count || filteredLowAttendanceStudents.value.length;
            const failedCount = response.data.failed_count || 0;
            
            if (failedCount > 0) {
                toastWarning(
                    'Bulk Emails Partially Sent',
                    `${successCount} sent successfully, ${failedCount} failed`,
                    6000
                );
            } else {
                toastSuccess(
                    'Bulk Emails Sent Successfully',
                    `Notifications sent to ${successCount} students`,
                    5000
                );
            }
        } else {
            toastError('Failed to send bulk emails', response.data.message || 'Unknown error');
        }
    } catch (error) {
        console.error('Bulk email error:', error);
        const errorMessage = error.response?.data?.message || error.message || 'Failed to send bulk emails';
        toastError('Failed to send bulk emails', errorMessage);
    } finally {
        emailLoading.value = false;
    }
};

// Open email modal
const openEmailModal = () => {
    if (filteredLowAttendanceStudents.value.length === 0) {
        toastWarning('No students with low attendance', 'All students have attendance above 60%');
        return;
    }
    showEmailModal.value = true;
};

// View Record Details - Opens modal
const viewRecordDetails = async (record) => {
    selectedRecord.value = record;
    showRecordDetailsModal.value = true;
    recordDetailsLoading.value = true;
    
    recordDetails.value = {
        details: [],
        total_students: 0,
        present_count: 0,
        absent_count: 0,
        late_count: 0,
        date: record.date
    };
    
    try {
        const response = await axios.get(`/course/${props.courseId}/attendance/record/${record.id}`);
        
        if (response.data.success) {
            const details = response.data.record.details || [];
            
            const presentCount = details.filter(d => d.status === 'present').length;
            const absentCount = details.filter(d => d.status === 'absent').length;
            const lateCount = details.filter(d => d.status === 'late').length;
            
            recordDetails.value = {
                details: details,
                total_students: details.length,
                present_count: presentCount,
                absent_count: absentCount,
                late_count: lateCount,
                date: response.data.record.date || record.date
            };
        } else {
            toastError('Failed to load record details', response.data.message || 'Unknown error');
            closeRecordDetailsModal();
        }
    } catch (error) {
        console.error('Error loading record details:', error);
        toastError('Failed to load record details', 'Please try again');
        closeRecordDetailsModal();
    } finally {
        recordDetailsLoading.value = false;
    }
};

// Close record details modal
const closeRecordDetailsModal = () => {
    showRecordDetailsModal.value = false;
    selectedRecord.value = null;
    recordDetails.value = {
        details: [],
        total_students: 0,
        present_count: 0,
        absent_count: 0,
        late_count: 0,
        date: ''
    };
};

// Print record details
const printRecordDetails = () => {
    const printContent = document.getElementById('record-details-content').innerHTML;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Attendance Record - ${recordDetails.value.date || ''}</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; }
                    .text-center { text-align: center; }
                    .mb-8 { margin-bottom: 2rem; }
                    .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
                    .bg-blue-100 { background-color: #dbeafe; }
                    .bg-green-100 { background-color: #dcfce7; }
                    .bg-red-100 { background-color: #fee2e2; }
                    .bg-yellow-100 { background-color: #fef9c3; }
                    .p-4 { padding: 1rem; }
                    .rounded-xl { border-radius: 0.75rem; }
                    .text-2xl { font-size: 1.5rem; }
                    .text-3xl { font-size: 1.875rem; }
                    .font-bold { font-weight: bold; }
                    .text-blue-800 { color: #1e40af; }
                    .text-green-800 { color: #166534; }
                    .text-red-800 { color: #991b1b; }
                    .text-yellow-800 { color: #854d0e; }
                    .text-sm { font-size: 0.875rem; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #e5e7eb; padding: 0.5rem; text-align: left; }
                    th { background-color: #f9fafb; }
                </style>
            </head>
            <body>
                ${printContent}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
};

// Print Report Function
const printReport = () => {
    if (filteredStudents.value.length === 0) {
        toastWarning('No data to print', 'Please load student data first');
        return;
    }
    showPrintModal.value = true;
};

// Do Print Function
const doPrint = () => {
    const printContent = document.getElementById('printable-content').innerHTML;
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = `
        <html>
            <head>
                <title>Attendance Report - ${props.courseName}</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; }
                    .text-center { text-align: center; }
                    .mb-8 { margin-bottom: 2rem; }
                    .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
                    .bg-blue-100 { background-color: #dbeafe; }
                    .bg-green-100 { background-color: #dcfce7; }
                    .bg-red-100 { background-color: #fee2e2; }
                    .bg-yellow-100 { background-color: #fef9c3; }
                    .bg-red-50 { background-color: #fef2f2; }
                    .p-4 { padding: 1rem; }
                    .rounded-lg { border-radius: 0.5rem; }
                    .rounded-xl { border-radius: 0.75rem; }
                    .text-2xl { font-size: 1.5rem; }
                    .text-3xl { font-size: 1.875rem; }
                    .font-bold { font-weight: bold; }
                    .text-blue-800 { color: #1e40af; }
                    .text-green-800 { color: #166534; }
                    .text-red-800 { color: #991b1b; }
                    .text-yellow-800 { color: #854d0e; }
                    .text-sm { font-size: 0.875rem; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #e5e7eb; padding: 0.5rem; text-align: left; }
                    th { background-color: #f9fafb; }
                </style>
            </head>
            <body>
                ${printContent}
            </body>
        </html>
    `;
    
    window.print();
    
    document.body.innerHTML = originalContent;
    window.location.reload();
};

// Print Specific Record
const printSpecificRecord = (record) => {
    toastInfo('Loading record...', 'Please wait');
    router.get(`/course/${props.courseId}/attendance/record/${record.id}/print`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.recordData) {
                const recordContent = `
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold">${props.courseName}</h1>
                        <h2 class="text-xl">${props.courseCode}</h2>
                        <p>Date: ${record.date}</p>
                    </div>
                    <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${page.props.recordData.details.map(d => `
                                <tr>
                                    <td>${d.student_id}</td>
                                    <td>${d.student_name}</td>
                                    <td>${d.status}</td>
                                    <td>${d.remarks || '-'}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
                
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Attendance Record - ${record.date}</title>
                            <style>
                                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; }
                                .text-center { text-align: center; }
                                .mb-8 { margin-bottom: 2rem; }
                                .text-3xl { font-size: 1.875rem; }
                                .text-xl { font-size: 1.25rem; }
                                .font-bold { font-weight: bold; }
                                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                                th { background-color: #f2f2f2; }
                            </style>
                        </head>
                        <body>
                            ${recordContent}
                        </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.print();
            }
        }
    });
};

// Export to CSV
const exportToCSV = () => {
    if (filteredStudents.value.length === 0) {
        toastWarning('No data to export', 'Please load student data first');
        return;
    }
    
    let csvContent = "ID,Name,Email,Level,Session,Status,Remarks,Attendance %\n";
    
    filteredStudents.value.forEach(student => {
        csvContent += `${student.roll || ''},${student.name || ''},${student.email || ''},${student.level || ''},${student.session || ''},${student.status || ''},${student.remarks || ''},${student.percentage || 0}%\n`;
    });
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `${props.courseCode}_attendance_${selectedDate.value}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    toastSuccess('Export Successful', 'CSV file has been downloaded');
};

// Show Analytics
const showAnalytics = () => {
    const total = filteredStudents.value.length;
    const present = filteredPresentCount.value;
    const absent = filteredAbsentCount.value;
    const late = filteredLateCount.value;
    const lowAttendance = filteredLowAttendanceStudents.value.length;
    
    const presentPercent = total ? ((present / total) * 100).toFixed(1) : 0;
    const absentPercent = total ? ((absent / total) * 100).toFixed(1) : 0;
    const latePercent = total ? ((late / total) * 100).toFixed(1) : 0;
    const lowAttendancePercent = total ? ((lowAttendance / total) * 100).toFixed(1) : 0;
    
    const message = `
        📊 Attendance Analytics
        ------------------------
        Total Students: ${total}
        ✅ Present: ${present} (${presentPercent}%)
        ❌ Absent: ${absent} (${absentPercent}%)
        ⏰ Late: ${late} (${latePercent}%)
        ⚠️ Low Attendance (<60%): ${lowAttendance} (${lowAttendancePercent}%)
        
        📈 Overall Attendance Rate: ${presentPercent}%
    `;
    
    alert(message);
};

// Print Summary
const printSummary = () => {
    const summaryContent = `
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold">${props.courseName}</h1>
            <h2 class="text-xl">${props.courseCode}</h2>
            <p>Stream: ${props.streamName || 'Not specified'}</p>
            <p>Date: ${selectedDate.value || new Date().toLocaleDateString()}</p>
        </div>
        
        <div style="margin: 20px 0;">
            <h3>Attendance Summary</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px;">Category</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Count</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Percentage</th>
                </tr>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">Present</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${filteredPresentCount.value}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${filteredStudents.value.length ? ((filteredPresentCount.value / filteredStudents.value.length) * 100).toFixed(1) : 0}%</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">Absent</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${filteredAbsentCount.value}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${filteredStudents.value.length ? ((filteredAbsentCount.value / filteredStudents.value.length) * 100).toFixed(1) : 0}%</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">Late</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${filteredLateCount.value}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${filteredStudents.value.length ? ((filteredLateCount.value / filteredStudents.value.length) * 100).toFixed(1) : 0}%</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">Low Attendance (<60%)</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${filteredLowAttendanceStudents.value.length}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${filteredStudents.value.length ? ((filteredLowAttendanceStudents.value.length / filteredStudents.value.length) * 100).toFixed(1) : 0}%</td>
                </tr>
            </table>
        </div>
        
        <div style="margin-top: 20px;">
            <p>Generated on: ${new Date().toLocaleString()}</p>
        </div>
    `;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Attendance Summary - ${props.courseCode}</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; }
                    .text-center { text-align: center; }
                    .mb-8 { margin-bottom: 2rem; }
                    .text-3xl { font-size: 1.875rem; }
                    .text-xl { font-size: 1.25rem; }
                    .font-bold { font-weight: bold; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                </style>
            </head>
            <body>
                ${summaryContent}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
};
</script>

<style scoped>
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

@keyframes gradient-x {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-bounce {
    animation: bounce 2s ease-in-out infinite;
}

.animate-bounce-slow {
    animation: bounce 3s ease-in-out infinite;
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}

.animate-gradient-x {
    background-size: 200% 200%;
    animation: gradient-x 3s ease-in-out infinite;
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}

.animate-slideUp {
    animation: slideUp 0.3s ease-out;
}

.delay-1000 {
    animation-delay: 1s;
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
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}

/* Print styles */
@media print {
    body * {
        visibility: hidden;
    }
    #printable-content, #printable-content *,
    #record-details-content, #record-details-content * {
        visibility: visible;
    }
    #printable-content, #record-details-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print {
        display: none !important;
    }
}
</style>