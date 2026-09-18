@extends('layouts.admin')

@section('title', 'Overview')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-card card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stat-icon" style="background:#DBEAFE;color:#3B82F6;"><i class="ri-eye-line"></i></div>
                    <div>
                        <div class="text-muted small">Website Visits</div>
                        <div class="fw-bold fs-4">{{ number_format($websiteVisits) }}</div>
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
                        <div class="fw-bold fs-4">{{ number_format($totalClicks) }}</div>
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
                        <div class="fw-bold fs-4">{{ number_format($totalOrders) }}</div>
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
                        <div class="fw-bold fs-4">PKR {{ number_format($revenue) }}</div>
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
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.0/dist/apexcharts.min.js"></script>
<script>
    const chartData = @json($chartData);

    let performanceChart = null;

    function buildOptions(period) {
        const d = chartData[period];
        return {
            chart: { type: 'area', height: 380, toolbar: { show: false } },
            series: [
                { name: 'Sales (PKR)', data: d.sales },
                { name: 'Visits', data: d.visits },
            ],
            colors: ['#A855F7', '#34D399'],
            fill: { type: 'gradient', gradient: { opacityFrom: .45, opacityTo: .05 } },
            stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: false },
            xaxis: { categories: d.categories },
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
            renderChart(btn.dataset.period);
        });
    });

    renderChart('daily');
</script>
@endsection
