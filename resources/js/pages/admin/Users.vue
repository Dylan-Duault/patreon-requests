<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    CheckCircle,
    Coins,
    Minus,
    Plus,
    Search,
    ThumbsUp,
    Users,
    XCircle,
} from 'lucide-vue-next';
import { ref } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
    avatar: string | null;
    patron_status: string | null;
    patron_tier_cents: number;
    is_active_patron: boolean;
    monthly_limit: number;
    credit_balance: number;
    request_count: number;
    rated_count: number;
    up_percentage: number | null;
}

defineProps<{
    users: User[];
    search: string;
}>();

const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Admin',
        href: '/admin/requests',
    },
    {
        title: 'Users',
        href: '/admin/users',
    },
];

const searchQuery = ref('');

const formatTier = (cents: number) => {
    return `$${(cents / 100).toFixed(2)}`;
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const handleSearch = () => {
    router.get('/admin/users', { search: searchQuery.value }, {
        preserveState: true,
        replace: true,
    });
};

// Credit adjustment dialog
const dialogOpen = ref(false);
const selectedUser = ref<User | null>(null);
const adjustmentAmount = ref(1);
const adjustmentReason = ref('');
const isAdding = ref(true);

const openAdjustDialog = (user: User, adding: boolean) => {
    selectedUser.value = user;
    isAdding.value = adding;
    adjustmentAmount.value = 1;
    adjustmentReason.value = '';
    dialogOpen.value = true;
};

const submitAdjustment = () => {
    if (!selectedUser.value) return;

    const amount = isAdding.value ? adjustmentAmount.value : -adjustmentAmount.value;

    router.post(`/admin/users/${selectedUser.value.id}/credits`, {
        amount,
        reason: adjustmentReason.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            dialogOpen.value = false;
        },
    });
};
</script>

<template>
    <Head title="Admin - Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl sm:text-2xl font-semibold tracking-tight">Manage Users</h1>
                    <p class="text-sm sm:text-base text-muted-foreground">
                        View users and manage their credits
                    </p>
                </div>
                <Button variant="outline" as-child class="w-full sm:w-auto">
                    <Link href="/admin/requests">
                        View Requests
                    </Link>
                </Button>
            </div>

            <!-- Search -->
            <div class="flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1 sm:max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search by name or email..."
                        class="pl-9"
                        @keyup.enter="handleSearch"
                    />
                </div>
                <Button @click="handleSearch" class="w-full sm:w-auto">Search</Button>
            </div>

            <!-- Flash Messages -->
            <div
                v-if="page.props.flash?.success"
                class="rounded-md bg-green-50 p-3 text-sm font-medium text-green-600 dark:bg-green-900/20 dark:text-green-400"
            >
                {{ page.props.flash.success }}
            </div>

            <!-- Users List -->
            <Card v-if="users.length > 0">
                <CardHeader>
                    <CardTitle>Users</CardTitle>
                    <CardDescription>
                        {{ users.length }} user{{ users.length !== 1 ? 's' : '' }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-3">
                        <div
                            v-for="user in users"
                            :key="user.id"
                            class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 rounded-lg border p-3 sm:p-4"
                        >
                            <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
                                <!-- Avatar -->
                                <Avatar class="h-9 w-9 sm:h-10 sm:w-10 flex-shrink-0">
                                    <AvatarImage
                                        v-if="user.avatar"
                                        :src="user.avatar"
                                        :alt="user.name"
                                    />
                                    <AvatarFallback class="text-xs">
                                        {{ getInitials(user.name) }}
                                    </AvatarFallback>
                                </Avatar>

                                <!-- User Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                        <span class="font-medium truncate text-sm sm:text-base">{{ user.name }}</span>
                                        <Badge
                                            v-if="user.is_active_patron"
                                            variant="default"
                                            class="flex items-center gap-1 text-xs"
                                        >
                                            <CheckCircle class="h-3 w-3" />
                                            <span class="hidden sm:inline">Active</span>
                                            <span class="sm:hidden">✓</span>
                                        </Badge>
                                        <Badge
                                            v-else
                                            variant="secondary"
                                            class="flex items-center gap-1 text-xs"
                                        >
                                            <XCircle class="h-3 w-3" />
                                            <span class="hidden sm:inline">Inactive</span>
                                            <span class="sm:hidden">✗</span>
                                        </Badge>
                                    </div>
                                    <p class="text-xs sm:text-sm text-muted-foreground truncate">{{ user.email }}</p>

                                    <!-- Mobile Stats -->
                                    <div class="flex flex-wrap gap-x-3 gap-y-1 mt-1 sm:hidden text-xs text-muted-foreground">
                                        <span v-if="user.patron_tier_cents > 0">
                                            Tier: {{ formatTier(user.patron_tier_cents) }}
                                        </span>
                                        <span>Monthly: {{ user.monthly_limit }}</span>
                                        <span>Requests: {{ user.request_count }}</span>
                                        <span v-if="user.rated_count > 0" class="flex items-center gap-1">
                                            <ThumbsUp class="h-3 w-3" />
                                            {{ user.up_percentage }}%
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Desktop Stats -->
                            <div class="hidden sm:flex items-center gap-4">
                                <!-- Tier -->
                                <div class="text-center">
                                    <p class="text-xs text-muted-foreground">Tier</p>
                                    <p class="font-medium text-sm">
                                        {{ user.patron_tier_cents > 0 ? formatTier(user.patron_tier_cents) : '-' }}
                                    </p>
                                </div>

                                <!-- Monthly Credits -->
                                <div class="text-center">
                                    <p class="text-xs text-muted-foreground">Per Month</p>
                                    <p class="font-medium text-sm">{{ user.monthly_limit }}</p>
                                </div>

                                <!-- Request Count -->
                                <div class="text-center">
                                    <p class="text-xs text-muted-foreground">Requests</p>
                                    <p class="font-medium text-sm">{{ user.request_count }}</p>
                                </div>

                                <!-- Rating -->
                                <div v-if="user.rated_count > 0" class="text-center">
                                    <p class="text-xs text-muted-foreground flex items-center justify-center gap-1">
                                        <ThumbsUp class="h-3 w-3" />
                                        Rating
                                    </p>
                                    <p
                                        class="font-medium text-sm"
                                        :class="user.up_percentage! >= 50 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                    >
                                        {{ user.up_percentage }}%
                                    </p>
                                </div>
                            </div>

                            <!-- Credit Balance and Actions -->
                            <div class="flex items-center justify-between sm:justify-end gap-3">
                                <!-- Credit Balance -->
                                <div class="text-center sm:text-left">
                                    <p class="text-xs text-muted-foreground">Balance</p>
                                    <p class="font-bold text-base sm:text-lg flex items-center gap-1">
                                        <Coins class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-yellow-500" />
                                        {{ user.credit_balance }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-1">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="openAdjustDialog(user, true)"
                                    >
                                        <Plus class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="openAdjustDialog(user, false)"
                                    >
                                        <Minus class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Empty State -->
            <Card v-else>
                <CardContent class="flex flex-col items-center justify-center py-12">
                    <Users class="h-12 w-12 text-muted-foreground mb-4" />
                    <h3 class="text-lg font-medium mb-2">No users found</h3>
                    <p class="text-muted-foreground text-center">
                        <template v-if="search">
                            No users match "{{ search }}". Try a different search.
                        </template>
                        <template v-else>
                            No users have registered yet.
                        </template>
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Credit Adjustment Dialog -->
        <Dialog v-model:open="dialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{ isAdding ? 'Add Credits' : 'Remove Credits' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ isAdding ? 'Add' : 'Remove' }} credits for {{ selectedUser?.name }}
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="amount">Amount</Label>
                        <Input
                            id="amount"
                            v-model.number="adjustmentAmount"
                            type="number"
                            min="1"
                            placeholder="Enter amount"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="reason">Reason</Label>
                        <Input
                            id="reason"
                            v-model="adjustmentReason"
                            placeholder="e.g., Bonus for contest winner"
                        />
                    </div>

                    <div class="rounded-lg bg-muted p-3 text-sm">
                        <p class="text-muted-foreground">
                            Current balance: <strong>{{ selectedUser?.credit_balance }}</strong>
                        </p>
                        <p class="text-muted-foreground">
                            New balance:
                            <strong>
                                {{ isAdding
                                    ? (selectedUser?.credit_balance ?? 0) + adjustmentAmount
                                    : (selectedUser?.credit_balance ?? 0) - adjustmentAmount
                                }}
                            </strong>
                        </p>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="dialogOpen = false">
                        Cancel
                    </Button>
                    <Button
                        :variant="isAdding ? 'default' : 'destructive'"
                        :disabled="!adjustmentAmount || !adjustmentReason"
                        @click="submitAdjustment"
                    >
                        {{ isAdding ? 'Add' : 'Remove' }} {{ adjustmentAmount }} Credit{{ adjustmentAmount !== 1 ? 's' : '' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
