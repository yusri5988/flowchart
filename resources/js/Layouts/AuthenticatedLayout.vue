<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-[#f8f9fa] font-sans selection:bg-black selection:text-white">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('dashboard')" class="flex items-center space-x-3 group">
                                <div class="w-8 h-8 bg-black flex items-center justify-center group-hover:bg-gray-800 transition-colors">
                                    <span class="text-white font-bold text-lg">FC</span>
                                </div>
                                <span class="text-lg font-bold tracking-tighter uppercase hidden sm:block">Flowchart</span>
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden sm:ms-12 sm:flex sm:space-x-8 h-full">
                            <NavLink
                                :href="route('dashboard')"
                                :active="route().current('dashboard')"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-xs font-bold uppercase tracking-widest transition-all duration-300"
                                :class="route().current('dashboard') ? 'border-black text-black' : 'border-transparent text-gray-400 hover:text-black hover:border-gray-200'"
                            >
                                Directory
                            </NavLink>
                            <NavLink
                                :href="route('flowchart')"
                                :active="route().current('flowchart')"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-xs font-bold uppercase tracking-widest transition-all duration-300"
                                :class="route().current('flowchart') ? 'border-black text-black' : 'border-transparent text-gray-400 hover:text-black hover:border-gray-200'"
                            >
                                Editor
                            </NavLink>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center">
                        <!-- Settings Dropdown -->
                        <div class="ms-3 relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button
                                        type="button"
                                        class="inline-flex items-center px-4 py-2 border border-gray-200 text-xs font-bold uppercase tracking-widest text-gray-500 bg-white hover:text-black hover:border-black focus:outline-none transition ease-in-out duration-300"
                                    >
                                        {{ $page.props.auth.user.name }}
                                        <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <div class="p-2 border border-black bg-white shadow-xl">
                                        <DropdownLink :href="route('profile.edit')" class="block px-4 py-2 text-xs font-bold uppercase tracking-widest text-gray-500 hover:bg-black hover:text-white transition-colors">
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button" class="w-full text-left block px-4 py-2 text-xs font-bold uppercase tracking-widest text-gray-500 hover:bg-black hover:text-white transition-colors">
                                            Log Out
                                        </DropdownLink>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <!-- Hamburger -->
                    <div class="-me-2 flex items-center sm:hidden">
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center p-2 text-gray-400 hover:text-black hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out"
                        >
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden bg-white border-b border-gray-200">
                <div class="pt-2 pb-3 space-y-1">
                    <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')" class="block ps-3 pe-4 py-2 border-l-4 text-xs font-bold uppercase tracking-widest" :class="route().current('dashboard') ? 'border-black text-black bg-gray-50' : 'border-transparent text-gray-500 hover:text-black hover:bg-gray-50 hover:border-gray-300'">
                        Directory
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('flowchart')" :active="route().current('flowchart')" class="block ps-3 pe-4 py-2 border-l-4 text-xs font-bold uppercase tracking-widest" :class="route().current('flowchart') ? 'border-black text-black bg-gray-50' : 'border-transparent text-gray-500 hover:text-black hover:bg-gray-50 hover:border-gray-300'">
                        Editor
                    </ResponsiveNavLink>
                </div>

                <!-- Responsive Settings Options -->
                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="px-4">
                        <div class="text-sm font-bold uppercase tracking-widest text-black">{{ $page.props.auth.user.name }}</div>
                        <div class="text-xs font-medium text-gray-500">{{ $page.props.auth.user.email }}</div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')" class="block ps-3 pe-4 py-2 text-xs font-bold uppercase tracking-widest text-gray-500 hover:text-black">
                            Profile
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button" class="block w-full text-left ps-3 pe-4 py-2 text-xs font-bold uppercase tracking-widest text-gray-500 hover:text-black">
                            Log Out
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header class="bg-white border-b border-gray-200" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <slot />
        </main>

        <!-- Global Footer -->
        <footer class="bg-white border-t border-gray-200 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 bg-black flex items-center justify-center">
                        <span class="text-white font-bold text-xs">FC</span>
                    </div>
                    <span class="text-xs font-bold tracking-widest uppercase">Flowchart Flowchart System</span>
                </div>
                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em]">
                    &copy; 2026 Ref: ARC-DASH-v1.0
                </div>
                <div class="flex space-x-8">
                    <a href="#" class="text-[10px] font-bold text-gray-400 hover:text-black transition-colors uppercase tracking-widest">Documentation</a>
                    <a href="#" class="text-[10px] font-bold text-gray-400 hover:text-black transition-colors uppercase tracking-widest">API Reference</a>
                    <a href="#" class="text-[10px] font-bold text-gray-400 hover:text-black transition-colors uppercase tracking-widest">System Status</a>
                </div>
            </div>
        </footer>
    </div>
</template>
