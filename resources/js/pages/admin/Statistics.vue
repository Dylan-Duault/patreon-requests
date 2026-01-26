<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { parseDate } from '@internationalized/date';
import { CalendarDays, ChevronLeft, ChevronRight, Clock, TrendingUp, Trophy, Video } from 'lucide-vue-next';
import {
    PopoverContent,
    PopoverPortal,
    PopoverRoot,
    PopoverTrigger,
    RangeCalendarCell,
    RangeCalendarCellTrigger,
    RangeCalendarGrid,
    RangeCalendarGridBody,
    RangeCalendarGridHead,
    RangeCalendarGridRow,
    RangeCalendarHeadCell,
    RangeCalendarHeader,
    RangeCalendarHeading,
    RangeCalendarNext,
    RangeCalendarPrev,
    RangeCalendarRoot,
} from 'reka-ui';
import { computed, ref } from 'vue';

import StatisticsChart from '@/components/admin/StatisticsChart.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface ChartDataPoint {
    period: string;
    label: string;
    value: number | null;
}

interface DateRangeProps {
    start: string;
    end: string;
}

interface LeaderboardEntry {
    user: {
        id: number;
        name: string;
        avatar: string | null;
    };
    request_count: number;
}

const props = defineProps<{
    requestsOverTime: ChartDataPoint[];
    avgDurationOverTime: ChartDataPoint[];
    avgCompletionTimeOverTime: ChartDataPoint[];
    oldestPendingDays: number | null;
    memberLeaderboard: LeaderboardEntry[];
    granularity: 'daily' | 'weekly' | 'monthly';
    dateRange: DateRangeProps;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Admin', href: '/admin/requests' },
    { title: 'Statistics', href: '/admin/statistics' },
];

// Initialize date range from props using parseDate
const dateValue = ref({
    start: parseDate(props.dateRange.start),
    end: parseDate(props.dateRange.end),
});

const popoverOpen = ref(false);

const formattedDateRange = computed(() => {
    const options: Intl.DateTimeFormatOptions = {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    };
    const start = new Date(props.dateRange.start).toLocaleDateString('en-US', options);
    const end = new Date(props.dateRange.end).toLocaleDateString('en-US', options);
    return `${start} - ${end}`;
});

const granularityLabel = computed(() => {
    const labels: Record<string, string> = {
        daily: 'day',
        weekly: 'week',
        monthly: 'month',
    };
    return labels[props.granularity] || props.granularity;
});

const applyDateRange = () => {
    if (dateValue.value.start && dateValue.value.end) {
        popoverOpen.value = false;
        router.get(
            '/admin/statistics',
            {
                start_date: dateValue.value.start.toString(),
                end_date: dateValue.value.end.toString(),
            },
            { preserveState: true }
        );
    }
};

const setPreset = (days: number) => {
    const end = new Date();
    const start = new Date();
    start.setDate(start.getDate() - days);

    router.get(
        '/admin/statistics',
        {
            start_date: start.toISOString().split('T')[0],
            end_date: end.toISOString().split('T')[0],
        },
        { preserveState: true }
    );
};

// Chart data transformations
const requestsChartData = computed(() => ({
    categories: props.requestsOverTime.map((d) => d.label),
    series: [
        {
            name: 'Requests',
            data: props.requestsOverTime.map((d) => d.value ?? 0),
        },
    ],
}));

const durationChartData = computed(() => ({
    categories: props.avgDurationOverTime.map((d) => d.label),
    series: [
        {
            name: 'Duration (min)',
            data: props.avgDurationOverTime.map((d) =>
                d.value !== null ? Math.round(d.value / 60) : null
            ),
        },
    ],
}));

const completionTimeChartData = computed(() => ({
    categories: props.avgCompletionTimeOverTime.map((d) => d.label),
    series: [
        {
            name: 'Hours',
            data: props.avgCompletionTimeOverTime.map((d) => d.value),
        },
    ],
}));

const hasRequestsData = computed(() => props.requestsOverTime.some((d) => d.value !== null && d.value > 0));
const hasDurationData = computed(() => props.avgDurationOverTime.some((d) => d.value !== null));
const hasCompletionData = computed(() => props.avgCompletionTimeOverTime.some((d) => d.value !== null));

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};
</script>

<template>
    <Head title="Admin - Statistics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">Statistics</h1>
                    <p class="text-muted-foreground">
                        Request analytics and trends ({{ granularityLabel }} view)
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Quick Presets -->
                    <div class="flex gap-1">
                        <Button variant="outline" size="sm" @click="setPreset(7)">7D</Button>
                        <Button variant="outline" size="sm" @click="setPreset(30)">30D</Button>
                        <Button variant="outline" size="sm" @click="setPreset(90)">90D</Button>
                    </div>

                    <!-- Date Range Picker -->
                    <PopoverRoot v-model:open="popoverOpen">
                        <PopoverTrigger as-child>
                            <Button variant="outline" class="gap-2">
                                <CalendarDays class="h-4 w-4" />
                                {{ formattedDateRange }}
                            </Button>
                        </PopoverTrigger>
                        <PopoverPortal>
                            <PopoverContent
                                align="end"
                                :side-offset="4"
                                class="z-50 w-auto rounded-lg border bg-card p-0 shadow-lg"
                            >
                                <!-- @vue-expect-error Type compatibility issue between @internationalized/date and reka-ui -->
                                <RangeCalendarRoot
                                    v-slot="{ weekDays, grid }"
                                    v-model="dateValue"
                                    :number-of-months="2"
                                    class="p-3"
                                >
                                    <RangeCalendarHeader class="flex items-center justify-between">
                                        <RangeCalendarPrev
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-accent"
                                        >
                                            <ChevronLeft class="h-4 w-4" />
                                        </RangeCalendarPrev>
                                        <RangeCalendarHeading class="text-sm font-medium" />
                                        <RangeCalendarNext
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-accent"
                                        >
                                            <ChevronRight class="h-4 w-4" />
                                        </RangeCalendarNext>
                                    </RangeCalendarHeader>

                                    <div class="mt-4 flex gap-4">
                                        <RangeCalendarGrid
                                            v-for="month in grid"
                                            :key="month.value.toString()"
                                        >
                                            <RangeCalendarGridHead>
                                                <RangeCalendarGridRow>
                                                    <RangeCalendarHeadCell
                                                        v-for="day in weekDays"
                                                        :key="day"
                                                        class="w-9 text-xs font-normal text-muted-foreground"
                                                    >
                                                        {{ day }}
                                                    </RangeCalendarHeadCell>
                                                </RangeCalendarGridRow>
                                            </RangeCalendarGridHead>
                                            <RangeCalendarGridBody>
                                                <RangeCalendarGridRow
                                                    v-for="(weekDates, index) in month.rows"
                                                    :key="`week-${index}`"
                                                    class="mt-2"
                                                >
                                                    <RangeCalendarCell
                                                        v-for="weekDate in weekDates"
                                                        :key="weekDate.toString()"
                                                        :date="weekDate"
                                                        class="p-0"
                                                    >
                                                        <RangeCalendarCellTrigger
                                                            :day="weekDate"
                                                            :month="month.value"
                                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md text-sm hover:bg-accent data-[disabled]:pointer-events-none data-[disabled]:opacity-50 data-[selected]:bg-primary data-[selected]:text-primary-foreground data-[selection-end]:rounded-r-md data-[selection-start]:rounded-l-md data-[highlighted]:bg-accent data-[outside-view]:text-muted-foreground data-[outside-view]:opacity-50"
                                                        />
                                                    </RangeCalendarCell>
                                                </RangeCalendarGridRow>
                                            </RangeCalendarGridBody>
                                        </RangeCalendarGrid>
                                    </div>
                                </RangeCalendarRoot>

                                <div class="flex justify-end border-t p-3">
                                    <Button size="sm" @click="applyDateRange">Apply</Button>
                                </div>
                            </PopoverContent>
                        </PopoverPortal>
                    </PopoverRoot>
                </div>
            </div>

            <!-- Stat Cards Row -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Oldest Pending Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Oldest Pending</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            <template v-if="oldestPendingDays !== null">
                                {{ oldestPendingDays }} day{{ oldestPendingDays !== 1 ? 's' : '' }}
                            </template>
                            <template v-else>
                                <span class="text-green-600 dark:text-green-400">None</span>
                            </template>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{
                                oldestPendingDays !== null
                                    ? 'waiting for completion'
                                    : 'No pending requests'
                            }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Member Leaderboard Card -->
                <Card class="md:col-span-2 lg:col-span-3">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Trophy class="h-5 w-5" />
                            Member Leaderboard
                        </CardTitle>
                        <CardDescription>
                            Top members by number of requests in the selected period
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="memberLeaderboard.length > 0" class="space-y-3">
                            <div
                                v-for="(entry, index) in memberLeaderboard"
                                :key="entry.user.id"
                                class="flex items-center gap-3"
                            >
                                <span
                                    class="w-6 text-center text-sm font-medium"
                                    :class="{
                                        'text-yellow-500': index === 0,
                                        'text-gray-400': index === 1,
                                        'text-amber-600': index === 2,
                                        'text-muted-foreground': index > 2,
                                    }"
                                >
                                    {{ index + 1 }}
                                </span>
                                <Avatar class="h-8 w-8">
                                    <AvatarImage v-if="entry.user.avatar" :src="entry.user.avatar" :alt="entry.user.name" />
                                    <AvatarFallback>{{ getInitials(entry.user.name) }}</AvatarFallback>
                                </Avatar>
                                <span class="flex-1 truncate text-sm font-medium">
                                    {{ entry.user.name }}
                                </span>
                                <span class="text-sm text-muted-foreground">
                                    {{ entry.request_count }} request{{ entry.request_count !== 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex h-[100px] items-center justify-center text-muted-foreground"
                        >
                            No requests in the selected period
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts -->
            <div class="grid gap-6">
                <!-- Requests Over Time Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <TrendingUp class="h-5 w-5" />
                            New Requests Over Time
                        </CardTitle>
                        <CardDescription>
                            Number of video requests submitted per {{ granularityLabel }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <StatisticsChart
                            v-if="hasRequestsData"
                            :categories="requestsChartData.categories"
                            :series="requestsChartData.series"
                            chart-type="area"
                            y-axis-title="Requests"
                            :height="300"
                        />
                        <div
                            v-else
                            class="flex h-[300px] items-center justify-center text-muted-foreground"
                        >
                            No request data for the selected period
                        </div>
                    </CardContent>
                </Card>

                <!-- Average Duration Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Video class="h-5 w-5" />
                            Average Video Duration
                        </CardTitle>
                        <CardDescription>
                            Average duration of requested videos (in minutes)
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <StatisticsChart
                            v-if="hasDurationData"
                            :categories="durationChartData.categories"
                            :series="durationChartData.series"
                            chart-type="line"
                            y-axis-title="Minutes"
                            :height="300"
                            :connect-nulls="false"
                        />
                        <div
                            v-else
                            class="flex h-[300px] items-center justify-center text-muted-foreground"
                        >
                            No duration data for the selected period
                        </div>
                    </CardContent>
                </Card>

                <!-- Average Completion Time Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Clock class="h-5 w-5" />
                            Average Time to Complete
                        </CardTitle>
                        <CardDescription>
                            Average hours from request submission to completion
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <StatisticsChart
                            v-if="hasCompletionData"
                            :categories="completionTimeChartData.categories"
                            :series="completionTimeChartData.series"
                            chart-type="line"
                            y-axis-title="Hours"
                            :height="300"
                            :connect-nulls="false"
                        />
                        <div
                            v-else
                            class="flex h-[300px] items-center justify-center text-muted-foreground"
                        >
                            No completion data for the selected period
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
