<script setup lang="ts">
import Highcharts from 'highcharts';
import { Chart } from 'highcharts-vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface SeriesData {
    name: string;
    data: (number | null)[];
}

interface Props {
    categories: string[];
    series: SeriesData[];
    chartType?: 'line' | 'area' | 'column';
    yAxisTitle?: string;
    height?: number;
    connectNulls?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    chartType: 'line',
    yAxisTitle: '',
    height: 300,
    connectNulls: true,
});

// Track theme changes to force chart re-render with correct colors
const themeKey = ref(0);

const getCssVar = (name: string): string => {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
};

const getChartColors = () => [
    getCssVar('--chart-1'),
    getCssVar('--chart-2'),
    getCssVar('--chart-3'),
    getCssVar('--chart-4'),
    getCssVar('--chart-5'),
];

const getThemeColors = () => ({
    mutedForeground: getCssVar('--muted-foreground'),
    border: getCssVar('--border'),
    foreground: getCssVar('--foreground'),
    card: getCssVar('--card'),
    cardForeground: getCssVar('--card-foreground'),
});

// Watch for theme changes via MutationObserver on the html element's class
let observer: MutationObserver | null = null;

onMounted(() => {
    observer = new MutationObserver(() => {
        themeKey.value++;
    });
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });
});

onUnmounted(() => {
    observer?.disconnect();
});

const chartOptions = computed<Highcharts.Options>(() => {
    // eslint-disable-next-line @typescript-eslint/no-unused-expressions
    themeKey.value; // Dependency to trigger recompute on theme change
    const chartColors = getChartColors();
    const themeColors = getThemeColors();

    return {
        chart: {
            type: props.chartType,
            height: props.height,
            backgroundColor: 'transparent',
            style: {
                fontFamily: 'inherit',
            },
        },
        title: {
            text: undefined,
        },
        credits: {
            enabled: false,
        },
        colors: chartColors,
        xAxis: {
            categories: props.categories,
            labels: {
                style: {
                    color: themeColors.mutedForeground,
                    fontSize: '12px',
                },
            },
            lineColor: themeColors.border,
            tickColor: themeColors.border,
        },
        yAxis: {
            title: {
                text: props.yAxisTitle,
                style: {
                    color: themeColors.mutedForeground,
                },
            },
            labels: {
                style: {
                    color: themeColors.mutedForeground,
                    fontSize: '12px',
                },
            },
            gridLineColor: themeColors.border,
            min: 0,
        },
        legend: {
            enabled: props.series.length > 1,
            itemStyle: {
                color: themeColors.foreground,
                fontWeight: 'normal',
            },
            itemHoverStyle: {
                color: themeColors.foreground,
            },
        },
        tooltip: {
            backgroundColor: themeColors.card,
            borderColor: themeColors.border,
            borderRadius: 8,
            style: {
                color: themeColors.cardForeground,
            },
            shared: true,
        },
        plotOptions: {
            line: {
                connectNulls: props.connectNulls,
                marker: {
                    enabled: props.categories.length < 30,
                    radius: 4,
                    symbol: 'circle',
                },
                lineWidth: 2,
            },
            area: {
                fillOpacity: 0.3,
                marker: {
                    enabled: props.categories.length < 30,
                    radius: 4,
                },
            },
            series: {
                animation: {
                    duration: 500,
                },
            },
        },
        series: props.series.map((s, index) => ({
            type: props.chartType,
            name: s.name,
            data: s.data,
            color: chartColors[index] || chartColors[0],
        })) as Highcharts.SeriesOptionsType[],
    };
});
</script>

<template>
    <Chart :options="chartOptions" />
</template>
