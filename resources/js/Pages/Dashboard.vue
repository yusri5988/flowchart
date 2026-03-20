<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    projects: {
        type: Array,
        default: () => [],
    },
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const deleteProject = (id) => {
    if (confirm('Are you sure you want to delete this flowchart blueprint?')) {
        router.delete(route('projects.destroy', { project: id }));
    }
};
</script>

<template>
    <Head title="Flowchart Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <h2 class="text-3xl font-extrabold tracking-tight text-black uppercase">
                        Project Directory
                    </h2>
                    <p class="text-sm text-gray-500 font-medium uppercase tracking-widest">
                        Manage your flowchart flowcharts and blueprints
                    </p>
                </div>
                <Link
                    :href="route('flowchart')"
                    class="bg-black text-white px-6 py-3 font-bold uppercase tracking-widest text-xs hover:bg-gray-800 transition-all duration-300 flex items-center space-x-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    <span>New Project</span>
                </Link>
            </div>
        </template>

        <div class="py-12 bg-[#f8f9fa] min-h-[calc(100vh-64px)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Project Grid -->
                <div v-if="projects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div 
                        v-for="project in projects" 
                        :key="project.id"
                        class="group bg-white border border-gray-200 overflow-hidden hover:border-black transition-all duration-300 flex flex-col"
                    >
                        <!-- Project Preview (Abstract Blueprint) -->
                        <div class="aspect-video bg-black relative overflow-hidden flex items-center justify-center p-8">
                            <div class="absolute inset-0 opacity-10" 
                                 style="background-image: linear-gradient(#333 1px, transparent 1px), linear-gradient(90deg, #333 1px, transparent 1px); background-size: 20px 20px;">
                            </div>
                            <!-- Abstract Graphic based on project ID to make them look different -->
                            <div class="relative w-full h-full border border-white/20 flex items-center justify-center">
                                <div class="w-1/2 h-1/2 border border-white/40" :style="{ transform: `rotate(${project.id * 45}deg)` }"></div>
                                <div class="absolute top-2 left-2 text-[8px] text-white/40 font-mono uppercase">Ref: {{ project.id.toString().padStart(4, '0') }}</div>
                            </div>
                        </div>

                        <!-- Project Info -->
                        <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                            <div>
                                <h3 class="text-xl font-bold text-black uppercase tracking-tight group-hover:text-black transition-colors">
                                    {{ project.title }}
                                </h3>
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2 leading-relaxed">
                                    {{ project.description || 'No description provided for this flowchart blueprint.' }}
                                </p>
                            </div>

                            <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase tracking-widest font-bold text-gray-400">Last Modified</span>
                                    <span class="text-xs font-bold text-black">{{ formatDate(project.updated_at) }}</span>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <button
                                        @click="deleteProject(project.id)"
                                        class="text-xs font-bold uppercase tracking-tighter text-red-400 hover:text-red-600 transition-colors flex items-center space-x-1"
                                    >
                                        <span>Delete</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <Link
                                        :href="route('flowchart', { project: project.id })"
                                        class="text-xs font-bold uppercase tracking-tighter text-gray-400 hover:text-black transition-colors flex items-center space-x-1"
                                    >
                                        <span>Open</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white border-2 border-dashed border-gray-200 p-16 flex flex-col items-center justify-center text-center space-y-6">
                    <div class="w-20 h-20 bg-gray-50 flex items-center justify-center border border-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-2xl font-bold text-black uppercase tracking-tight">No Projects Found</h3>
                        <p class="text-gray-500 max-w-xs mx-auto text-sm uppercase tracking-widest leading-relaxed">
                            Your flowchart archive is currently empty. Start by creating your first blueprint.
                        </p>
                    </div>
                    <Link
                        :href="route('flowchart')"
                        class="bg-black text-white px-8 py-4 font-bold uppercase tracking-[0.2em] text-xs hover:bg-gray-800 transition-all duration-300"
                    >
                        Create First Project
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
