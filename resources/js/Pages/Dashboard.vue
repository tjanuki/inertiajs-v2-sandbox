<script setup>
import { usePoll, usePrefetch, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const pollData = ref(null);
const isPolling = ref(false);

const { start, stop } = usePoll(5000, {
    onStart() {
        console.log('Polling request started');
        isPolling.value = true;
    },
    onFinish() {
        console.log('Polling request finished');
    },
    onSuccess(page) {
        pollData.value = page.props.pollData;
    }
}, {
    autoStart: false,
    keepAlive: true
});

const togglePolling = () => {
    if (isPolling.value) {
        stop();
        isPolling.value = false;
    } else {
        start();
    }
};

// Prefetch example
const {lastUpdatedAt, isPrefetching, isPrefetched, flush} = usePrefetch(
    '/users',
    {method: 'get', data: {page: 1}},
    {cacheFor: '1m'}
);

onMounted(() => {
    // Programmatically prefetch users data on component mount
    flush(); // Clear any existing cache for this route
});
</script>

<template>
    <Head title="Dashboard"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        You're logged in!
                    </div>
                    <div class="p-6">
                        <button @click="togglePolling"
                                class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                            {{ isPolling ? 'Stop' : 'Start' }} Polling
                        </button>
                        <p class="mt-2">
                            Polling status: {{ isPolling ? 'Active' : 'Inactive' }}
                        </p>
                        <div v-if="pollData" class="mt-4">
                            <h3 class="text-lg font-semibold">Polled Data:</h3>
                            <p>Current Time: {{ pollData.currentTime }}</p>
                            <p>Random Number: {{ pollData.randomNumber }}</p>
                            <p>Active Users: {{ pollData.activeUsers }}</p>
                        </div>
                    </div>
                    <div class="p-6 border-t">
                        <h3 class="text-lg font-semibold mb-4">Prefetch Examples:</h3>
                        <Link href="/users" class="text-blue-500 hover:underline" prefetch>Users (Hover to prefetch)
                        </Link>
                        <p class="mt-2">Prefetch status: {{ isPrefetched ? 'Prefetched' : 'Not prefetched' }}</p>
                        <p v-if="lastUpdatedAt">Last updated: {{ new Date(lastUpdatedAt).toLocaleString() }}</p>
                        <button @click="flush"
                                class="mt-2 px-4 py-2 text-white bg-green-500 rounded hover:bg-green-600">
                            Clear Users Prefetch Cache
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
