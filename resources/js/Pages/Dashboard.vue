<script setup>
import { usePoll, usePrefetch, Link, Deferred } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3'

// Define props
const props = defineProps({
    pollData: Object,
    userStats: {
        type: Object,
        default: null,
    },
    users: {
        type: Object,
        default: () => ({
            data: [],
            current_page: 1,
            last_page: 1,
        }),
    },
});

const isPolling = ref(false);
const isLoading = ref(false);
const userList = ref(props.users.data || []);
const page = ref(props.users.current_page || 1);

const {start, stop} = usePoll(5000, {
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
    flush(); // Clear any existing cache for this route
    window.addEventListener('scroll', handleScroll);
});

const handleScroll = () => {
    const bottomOfWindow = document.documentElement.scrollTop + window.innerHeight === document.documentElement.offsetHeight;

    if (bottomOfWindow && !isLoading.value && hasMoreUsers.value) {
        loadMoreUsers();
    }
};

const loadMoreUsers = () => {
    if (isLoading.value) return;

    isLoading.value = true;
    page.value++;

    router.get(
        '/dashboard',
        {page: page.value},
        {
            preserveState: true,
            preserveScroll: true,
            only: ['users'],
            onSuccess: (page) => {
                if (page.props.users && Array.isArray(page.props.users.data)) {
                    userList.value = [...userList.value, ...page.props.users.data];
                }
                isLoading.value = false;
            },
            onError: () => {
                isLoading.value = false;
                page.value--;
            },
        }
    );
};

const hasMoreUsers = computed(() => {
    // console.log('props.users', props.users);
    // console.log('page.value', page.value);
    return props.users && props.users.last_page ? page.value < props.users.last_page : false;
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
                    <div class="p-6 border-t">
                        <h3 class="text-lg font-semibold mb-4">User Statistics (Deferred):</h3>
                        <Deferred data="userStats">
                            <template #fallback>
                                <div class="text-gray-500">Loading user statistics...</div>
                            </template>
                            <div v-if="userStats">
                                <p>Total Users: {{ userStats.totalUsers }}</p>
                                <p>New Users Today: {{ userStats.newUsersToday }}</p>
                                <p>Active Users (Last Week): {{ userStats.activeUsersLastWeek }}</p>
                            </div>
                        </Deferred>
                    </div>
                    <div class="p-6 border-t">
                        <h3 class="text-lg font-semibold mb-4">User List (Infinite Scroll):</h3>
                        <ul v-if="userList.length > 0" class="space-y-4">
                            <li v-for="user in userList" :key="user.id" class="border p-4 rounded">
                                <p class="font-semibold">{{ user.name }}</p>
                                <p class="text-sm text-gray-600">{{ user.email }}</p>
                                <p class="text-xs text-gray-500">Joined:
                                    {{ new Date(user.created_at).toLocaleDateString() }}</p>
                            </li>
                        </ul>
                        <div v-else class="text-center text-gray-600">
                            No users found.
                        </div>
                        <div v-if="isLoading" class="mt-4 text-center text-gray-600">
                            Loading more users...
                        </div>
                        <div v-if="!hasMoreUsers && userList.length > 0" class="mt-4 text-center text-gray-600">
                            No more users to load.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
