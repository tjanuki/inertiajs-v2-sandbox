<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { WhenVisible } from '@inertiajs/vue3';

const props = defineProps({
    users: {
        type: Object,
        default: () => ({
            data: [],
            current_page: 1,
            last_page: 1,
        }),
    },
});

const userList = ref(props.users.data || []);
const page = ref(props.users.current_page || 1);
const isLoading = ref(false);

const loadMoreUsers = () => {
    if (isLoading.value || page.value >= props.users.last_page) return;

    isLoading.value = true;
    page.value++;

    router.get(
        '/dashboard',
        { page: page.value },
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

const hasMoreUsers = () => page.value < props.users.last_page;

// Watch for changes in the users prop
watch(() => props.users, (newUsers) => {
    if (newUsers && Array.isArray(newUsers.data)) {
        userList.value = newUsers.data;
        page.value = newUsers.current_page;
    }
}, { deep: true });
</script>

<template>
    <div>
        <h2 class="text-2xl font-bold mb-4">User List</h2>
        <div class="space-y-4">
            <div v-for="user in userList" :key="user.id" class="border p-4 rounded">
                <p class="font-semibold">{{ user.name }}</p>
                <p class="text-sm text-gray-600">{{ user.email }}</p>
            </div>
        </div>

        <div style="height: 1000px"/>

        <WhenVisible v-if="hasMoreUsers()" data="users" always :buffer="200">
            <template #fallback>
                <div class="text-center py-4">
                    <p class="text-gray-500">Loading more users...</p>
                </div>
            </template>

            <div @inertia:visible="loadMoreUsers">
                <!-- This div will trigger loadMoreUsers when it becomes visible -->
                <div v-for="user in userList.slice(-10)" :key="user.id" class="border p-4 rounded mt-4">
                    <p class="font-semibold">{{ user.name }}</p>
                    <p class="text-sm text-gray-600">{{ user.email }}</p>
                </div>
            </div>
        </WhenVisible>

        <div v-if="!hasMoreUsers() && userList.length > 0" class="text-center py-4">
            <p class="text-gray-500">No more users to load.</p>
        </div>
    </div>
</template>
