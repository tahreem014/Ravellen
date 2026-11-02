<?php $__env->startSection('title', 'Overview'); ?>

<?php $__env->startSection('content'); ?>
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-card card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stat-icon" style="background:#DBEAFE;color:#3B82F6;"><i class="ri-eye-line"></i></div>
                    <div>
                        <div class="text-muted small">Website Visits</div>
                        <div class="fw-bold fs-4" id="stat-visits"><?php echo e(number_format($websiteVisits)); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-card card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stat-icon" style="background:#EDE9FE;color:#A855F7;"><i class="ri-cursor-line"></i></div>
                    <div>
                        <div class="text-muted small">Total Clicks</div>
                        <div class="fw-bold fs-4" id="stat-clicks"><?php echo e(number_format($totalClicks)); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-card card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stat-icon" style="background:#FCE7F3;color:#EC4899;"><i class="ri-shopping-bag-line"></i></div>
                    <div>
                        <div class="text-muted small">Total Orders</div>
                        <div class="fw-bold fs-4" id="stat-orders"><?php echo e(number_format($totalOrders)); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-card card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stat-icon" style="background:#D1FAE5;color:#10B981;"><i class="ri-money-dollar-circle-line"></i></div>
                    <div>
                        <div class="text-muted small">Revenue</div>
                        <div class="fw-bold fs-4" id="stat-revenue">PKR <?php echo e(number_format($revenue)); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card card">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h5 class="mb-0"><i class="ri-line-chart-line text-primary me-1"></i> Performance Analytics</h5>
                <div class="btn-group btn-group-sm" role="group" id="periodToggle">
                    <button type="button" class="btn btn-outline-secondary active" data-period="daily">Daily</button>
                    <button type="button" class="btn btn-outline-secondary" data-period="weekly">Weekly</button>
                    <button type="button" class="btn btn-outline-secondary" data-period="monthly">Monthly</button>
                </div>
            </div>
            <div id="performanceChart"></div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.0/dist/apexcharts.min.js"></script>
<script>
    const chartData = <?php echo json_encode($chartData, 15, 512) ?>;
    const STATS_URL = <?php echo json_encode(route('admin.overview.stats', [], false)) ?>;
    const POLL_MS = 5000;

    let performanceChart = null;
    let currentPeriod = 'daily';
    let polling = false;

    const fmt = (n) => Math.round(Number(n)).toLocaleString('en-US');

    function seriesFor(d) {
        return [
            { name: 'Sales (PKR)', data: d.sales },
            { name: 'Visits', data: d.visits },
        ];
    }

    function buildOptions(period) {
        const d = chartData[period];
        return {
            chart: { type: 'area', height: 380, toolbar: { show: false } },
            series: seriesFor(d),
            colors: ['#A855F7', '#34D399'],
            fill: { type: 'gradient', gradient: { opacityFrom: .45, opacityTo: .05 } },
            stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: false },
            xaxis: { categories: d.categories },
            // Sales (PKR) and visits are on different scales, so each gets its own axis.
            yaxis: [
                { seriesName: 'Sales (PKR)', min: 0, labels: { formatter: (v) => Math.round(v).toLocaleString() } },
                { seriesName: 'Visits', opposite: true, min: 0, labels: { formatter: (v) => Math.round(v).toLocaleString() } },
            ],
            legend: { position: 'bottom' },
        };
    }

    function renderChart(period) {
        if (performanceChart) {
            performanceChart.destroy();
        }
        performanceChart = new ApexCharts(document.querySelector('#performanceChart'), buildOptions(period));
        performanceChart.render();
    }

    document.querySelectorAll('#periodToggle button').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#periodToggle button').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            currentPeriod = btn.dataset.period;
            renderChart(currentPeriod);
            poll();
        });
    });

    /**
     * Live updates: every few seconds (while the tab is visible) asks the server
     * for the four totals and the shown period's chart, and patches them in place.
     */
    async function poll() {
        if (polling || document.visibilityState !== 'visible') return;
        polling = true;

        try {
            const period = currentPeriod;
            const res = await fetch(STATS_URL + '?period=' + period, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();

            document.getElementById('stat-visits').textContent = fmt(data.websiteVisits);
            document.getElementById('stat-clicks').textContent = fmt(data.totalClicks);
            document.getElementById('stat-orders').textContent = fmt(data.totalOrders);
            document.getElementById('stat-revenue').textContent = 'PKR ' + fmt(data.revenue);

            if (data.series && JSON.stringify(data.series) !== JSON.stringify(chartData[period])) {
                chartData[period] = data.series;
                if (period === currentPeriod) {
                    performanceChart.updateOptions({ series: seriesFor(data.series), xaxis: { categories: data.series.categories } });
                }
            }
        } catch (e) {
            // offline, signed out or server busy — try again on the next tick
        } finally {
            polling = false;
        }
    }

    setInterval(poll, POLL_MS);
    document.addEventListener('visibilitychange', poll);

    renderChart('daily');
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>