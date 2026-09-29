// Chart libraries, bundled and served from /build instead of jsdelivr.
// Loaded once from the layout as a module (deferred): it runs before
// DOMContentLoaded, so window.Chart / window.ApexCharts exist before Livewire
// starts and runs the @script blocks that draw the charts, including after
// wire:navigate (the layout's head is not re-executed on navigate).
import Chart from 'chart.js/auto';
import ApexCharts from 'apexcharts';

window.Chart = Chart;
window.ApexCharts = ApexCharts;
