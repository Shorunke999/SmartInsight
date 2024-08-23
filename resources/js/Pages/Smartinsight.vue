<template>
    <div>
        <Head title="Dashboard" />

        <GuestLayout>
            <template #header>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">SmartInsight</h2>
            </template>

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <p v-if="statusMessage">{{ statusMessage }}</p>
                            <p v-else>Live Update On User Count:</p>
                            <div v-if="userCount !== null">
                                {{ userCount }} -- {{ percentage }}%
                            </div>
                            <div v-else>
                                Loading.......
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </GuestLayout>
    </div>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import '../app.js';

const userCount = ref(null); // Reactive reference for user count
const percentage = ref(null); // Reactive reference for percentage
const statusMessage = ref(''); // Reactive reference for status message

// Watcher to handle when the percentage reaches 100%
watch(percentage, (newPercentage) => {
    if (newPercentage >= 100) {
        statusMessage.value = 'Previous batch of data created successfully... preparing for new batch';

        // Reload UI after a delay (e.g., 5 seconds)
        setTimeout(() => {
            userCount.value = null;
            percentage.value = null;
            statusMessage.value = '';
        }, 5000);
    }
});

// WebSocket connection and event listener setup
Echo.channel('UserCount')
    .subscribed(() => {
        console.log('connected to WebSocket');
    })
    .listen('BroadcastUserCountEvent', (e) => {
        userCount.value = e.userCount;
        percentage.value = ((e.userCount / 500) * 100).toFixed(2);
    });
</script>
