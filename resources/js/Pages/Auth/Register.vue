<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div class="min-h-screen flex flex-col md:flex-row bg-[#f8f9fa] font-sans selection:bg-black selection:text-white">
        <Head title="Create Flowchart Account" />

        <!-- Left Column: Form Section -->
        <div class="w-full md:w-[450px] lg:w-[500px] flex flex-col justify-between p-8 md:p-12 lg:p-16 bg-white border-r border-gray-200 overflow-y-auto">
            <div class="space-y-12">
                <!-- Branding -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-black flex items-center justify-center">
                        <span class="text-white font-bold text-xl">FC</span>
                    </div>
                    <span class="text-xl font-bold tracking-tighter uppercase">Flowchart</span>
                </div>

                <!-- Header -->
                <div class="space-y-4">
                    <h1 class="text-4xl font-extrabold tracking-tight text-black">
                        Join the grid.
                    </h1>
                    <p class="text-gray-500 text-lg leading-relaxed max-w-sm">
                        Create your account to start mapping your architectural blueprints.
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2">
                        <InputLabel for="name" value="Full Name" class="text-xs uppercase tracking-widest font-bold text-gray-400" />
                        <TextInput
                            id="name"
                            type="text"
                            class="block w-full border-0 border-b-2 border-gray-200 rounded-none px-0 py-3 focus:ring-0 focus:border-black transition-all duration-300 text-lg placeholder:text-gray-300"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="John Doe"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <InputLabel for="email" value="Email Address" class="text-xs uppercase tracking-widest font-bold text-gray-400" />
                        <TextInput
                            id="email"
                            type="email"
                            class="block w-full border-0 border-b-2 border-gray-200 rounded-none px-0 py-3 focus:ring-0 focus:border-black transition-all duration-300 text-lg placeholder:text-gray-300"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="name@firm.com"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="space-y-2">
                        <InputLabel for="password" value="Password" class="text-xs uppercase tracking-widest font-bold text-gray-400" />
                        <TextInput
                            id="password"
                            type="password"
                            class="block w-full border-0 border-b-2 border-gray-200 rounded-none px-0 py-3 focus:ring-0 focus:border-black transition-all duration-300 text-lg placeholder:text-gray-300"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="space-y-2">
                        <InputLabel for="password_confirmation" value="Confirm Password" class="text-xs uppercase tracking-widest font-bold text-gray-400" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="block w-full border-0 border-b-2 border-gray-200 rounded-none px-0 py-3 focus:ring-0 focus:border-black transition-all duration-300 text-lg placeholder:text-gray-300"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <div class="pt-8">
                        <button
                            type="submit"
                            class="w-full bg-black text-white py-4 px-8 font-bold uppercase tracking-[0.2em] hover:bg-gray-800 transition-all duration-300 active:scale-[0.98] disabled:opacity-50 flex items-center justify-center space-x-4"
                            :disabled="form.processing"
                        >
                            <span>Create Account</span>
                            <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            <svg v-else class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="pt-2 text-center">
                        <Link
                            :href="route('login')"
                            class="text-xs font-bold text-gray-500 hover:text-black transition-colors uppercase tracking-tighter"
                        >
                            Already registered? Sign In
                        </Link>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Visual Section -->
        <div class="hidden md:flex flex-1 bg-black relative overflow-hidden">
            <!-- Flowchart Grid Background -->
            <div class="absolute inset-0 opacity-20" 
                 style="background-image: linear-gradient(#333 1px, transparent 1px), linear-gradient(90deg, #333 1px, transparent 1px); background-size: 40px 40px;">
            </div>
            <div class="absolute inset-0 opacity-10" 
                 style="background-image: linear-gradient(#444 1px, transparent 1px), linear-gradient(90deg, #444 1px, transparent 1px); background-size: 8px 8px;">
            </div>

            <!-- Decorative Elements -->
            <div class="absolute top-12 right-12 text-white/30 font-mono text-[10px] tracking-[0.5em] uppercase vertical-text">
                Ref: ARC-2026-REG-02
            </div>
            
            <div class="absolute bottom-12 left-12 flex items-end space-x-8">
                <div class="space-y-1">
                    <div class="text-white/20 text-[10px] uppercase tracking-widest font-bold">Status</div>
                    <div class="text-white/60 font-mono text-sm">INITIALIZING_SEQUENCE</div>
                </div>
            </div>

            <!-- Central Graphic: Abstract Blueprint -->
            <div class="relative z-10 w-full h-full flex items-center justify-center p-24">
                <div class="relative w-full aspect-square max-w-lg">
                    <!-- Geometric Shapes (Blueprint Style) -->
                    <div class="absolute inset-0 border border-white/20"></div>
                    <div class="absolute top-1/4 left-1/4 w-1/2 h-1/2 border border-white/40 rotate-45 transition-transform duration-[10s] animate-[spin_20s_linear_infinite]"></div>
                    <div class="absolute top-1/3 left-1/3 w-1/3 h-1/3 border border-white/60 -rotate-12"></div>
                    
                    <!-- Coordinate Markers -->
                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 text-white/40 font-mono text-[10px]">0,0,0</div>
                    <div class="absolute top-1/2 -right-12 translate-y-1/2 rotate-90 text-white/40 font-mono text-[10px]">X-AXIS</div>
                    
                    <!-- Flowchart Nodes (Abstract) -->
                    <div class="absolute top-10 left-10 w-4 h-4 bg-white/80"></div>
                    <div class="absolute bottom-10 right-10 w-4 h-4 bg-white/80 animate-pulse"></div>
                    
                    <!-- Connecting Lines -->
                    <div class="absolute top-12 left-14 right-14 h-[1px] bg-white/20"></div>
                    <div class="absolute top-14 bottom-14 left-12 w-[1px] bg-white/20"></div>
                </div>
            </div>

            <!-- Corner Accents -->
            <div class="absolute top-0 left-0 w-24 h-24 border-t border-l border-white/10"></div>
            <div class="absolute bottom-0 right-0 w-24 h-24 border-b border-r border-white/10"></div>
        </div>
    </div>
</template>

<style scoped>
.vertical-text {
    writing-mode: vertical-rl;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>