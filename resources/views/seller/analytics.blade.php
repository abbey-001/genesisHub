@extends('seller.layouts.app')

@section('title', 'Analytics')

@section('content')
<div class="dashboard__content bgc-gmart-gray">
  <!-- Page Header -->
  <div class="row pb30">
    <div class="col-lg-9">
      <div class="dashboard_title_area">
        <h2>Analytics & Insights</h2>
        <p class="para">Track your store performance and growth</p>
      </div>
    </div>
    <div class="col-lg-3">
      <form method="GET" class="text-end">
        <select name="period" class="selectpicker show-tick" onchange="this.form.submit()">
          <option value="7" {{ $period == 7 ? 'selected' : '' }}>Last 7 Days</option>
          <option value="30" {{ $period == 30 ? 'selected' : '' }}>Last 30 Days</option>
          <option value="90" {{ $period == 90 ? 'selected' : '' }}>Last 90 Days</option>
          <option value="365" {{ $period == 365 ? 'selected' : '' }}>Last Year</option>
        </select>
      </form>
    </div>
  </div>

  <!-- Key Metrics -->
  <div class="row mb30">
    <div class="col-lg-3 col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <p class="text-muted mb-2">Total Revenue</p>
              <h3 class="mb-0">${{ number_format($totalRevenue, 2) }}</h3>
            </div>
            <div class="icon-box bg-success-light">
              <i class="fa fa-dollar-sign text-success"></i>
            </div>
          </div>
          <div class="mt-3">
            <span class="badge bg-success">
              <i class="fa fa-arrow-up"></i> 12.5%
            </span>
            <small class="text-muted ms-2">vs previous period</small>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <p class="text-muted mb-2">Total Orders</p>
              <h3 class="mb-0">{{ number_format($totalOrders) }}</h3>
            </div>
            <div class="icon-box bg-primary-light">
              <i class="fa fa-shopping-cart text-primary"></i>
            </div>
          </div>
          <div class="mt-3">
            <span class="badge bg-primary">
              <i class="fa fa-arrow-up"></i> 8.2%
            </span>
            <small class="text-muted ms-2">vs previous period</small>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <p class="text-muted mb-2">Avg Order Value</p>
              <h3 class="mb-0">${{ number_format($avgOrderValue, 2) }}</h3>
            </div>
            <div class="icon-box bg-warning-light">
              <i class="fa fa-chart-line text-warning"></i>
            </div>
          </div>
          <div class="mt-3">
            <span class="badge bg-warning">
              <i class="fa fa-minus"></i> 2.1%
            </span>
            <small class="text-muted ms-2">vs previous period</small>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <p class="text-muted mb-2">Conversion Rate</p>
              <h3 class="mb-0">{{ number_format($conversionRate, 1) }}%</h3>
            </div>
            <div class="icon-box bg-info-light">
              <i class="fa fa-percentage text-info"></i>
            </div>
          </div>
          <div class="mt-3">
            <span class="badge bg-success">
              <i class="fa fa-arrow-up"></i> 5.3%
            </span>
            <small class="text-muted ms-2">vs previous period</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts Row -->
  <div class="row">
    <!-- Revenue Trend -->
    <div class="col-xl-8">
      <div class="card mb30">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="mb-0">Revenue Trend</h4>
          <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-primary active">Revenue</button>
            <button type="button" class="btn btn-outline-primary">Orders</button>
            <button type="button" class="btn btn-outline-primary">Views</button>
          </div>
        </div>
        <div class="card-body">
          <canvas id="revenueTrendChart" height="300"></canvas>
        </div>
      </div>
    </div>

    <!-- Top Categories -->
    <div class="col-xl-4">
      <div class="card mb30">
        <div class="card-header">
          <h4 class="mb-0">Top Categories</h4>
        </div>
        <div class="card-body">
          <canvas id="categoriesChart" height="300"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Performance Metrics -->
  <div class="row">
    <!-- Sales by Day of Week -->
    <div class="col-xl-6">
      <div class="card mb30">
        <div class="card-header">
          <h4 class="mb-0">Sales by Day of Week</h4>
        </div>
        <div class="card-body">
          <canvas id="dayOfWeekChart" height="250"></canvas>
        </div>
      </div>
    </div>

    <!-- Top Products -->
    <div class="col-xl-6">
      <div class="card mb30">
        <div class="card-header">
          <h4 class="mb-0">Top Performing Products</h4>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Sales</th>
                  <th>Revenue</th>
                  <th>Growth</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <img src="{{ asset('images/product-1.jpg') }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                      <span class="ms-2">Product Name 1</span>
                    </div>
                  </td>
                  <td>234</td>
                  <td>$4,680</td>
                  <td><span class="badge bg-success"><i class="fa fa-arrow-up"></i> 15%</span></td>
                </tr>
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <img src="{{ asset('images/product-2.jpg') }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                      <span class="ms-2">Product Name 2</span>
                    </div>
                  </td>
                  <td>189</td>
                  <td>$3,780</td>
                  <td><span class="badge bg-success"><i class="fa fa-arrow-up"></i> 8%</span></td>
                </tr>
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <img src="{{ asset('images/product-3.jpg') }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                      <span class="ms-2">Product Name 3</span>
                    </div>
                  </td>
                  <td>156</td>
                  <td>$3,120</td>
                  <td><span class="badge bg-danger"><i class="fa fa-arrow-down"></i> 3%</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Traffic Sources & Customer Insights -->
  <div class="row">
    <div class="col-xl-4">
      <div class="card mb30">
        <div class="card-header">
          <h4 class="mb-0">Traffic Sources</h4>
        </div>
        <div class="card-body">
          <div class="traffic-source mb-3">
            <div class="d-flex justify-content-between mb-2">
              <span>Direct</span>
              <strong>45%</strong>
            </div>
            <div class="progress" style="height: 8px;">
              <div class="progress-bar bg-primary" style="width: 45%"></div>
            </div>
          </div>
          <div class="traffic-source mb-3">
            <div class="d-flex justify-content-between mb-2">
              <span>Search</span>
              <strong>30%</strong>
            </div>
            <div class="progress" style="height: 8px;">
              <div class="progress-bar bg-success" style="width: 30%"></div>
            </div>
          </div>
          <div class="traffic-source mb-3">
            <div class="d-flex justify-content-between mb-2">
              <span>Social</span>
              <strong>15%</strong>
            </div>
            <div class="progress" style="height: 8px;">
              <div class="progress-bar bg-info" style="width: 15%"></div>
            </div>
          </div>
          <div class="traffic-source">
            <div class="d-flex justify-content-between mb-2">
              <span>Referral</span>
              <strong>10%</strong>
            </div>
            <div class="progress" style="height: 8px;">
              <div class="progress-bar bg-warning" style="width: 10%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-8">
      <div class="card mb30">
        <div class="card-header">
          <h4 class="mb-0">Recent Activity</h4>
        </div>
        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
          @foreach($analytics as $day)
            <div class="activity-item d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
              <div>
                <strong>{{ $day->date->format('M d, Y') }}</strong>
                <p class="mb-0 text-muted small">
                  {{ $day->views }} views • {{ $day->orders }} orders • {{ $day->products_sold }} products sold
                </p>
              </div>
              <div class="text-end">
                <strong class="text-success">${{ number_format($day->revenue, 2) }}</strong>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>


<style>
.icon-box {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.bg-success-light { background: rgba(91, 187, 123, 0.1); }
.bg-primary-light { background: rgba(13, 110, 253, 0.1); }
.bg-warning-light { background: rgba(255, 193, 7, 0.1); }
.bg-info-light { background: rgba(13, 202, 240, 0.1); }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Trend Chart
const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
new Chart(revenueTrendCtx, {
    type: 'line',
    data: {
        labels: @json($analytics->pluck('date')->map(fn($d) => $d->format('M d'))),
        datasets: [{
            label: 'Revenue',
            data: @json($analytics->pluck('revenue')),
            borderColor: '#5bbb7b',
            backgroundColor: 'rgba(91, 187, 123, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => '$' + value
                }
            }
        }
    }
});

// Categories Doughnut Chart
const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
new Chart(categoriesCtx, {
    type: 'doughnut',
    data: {
        labels: @json($topCategories->pluck('name')),
        datasets: [{
            data: @json($topCategories->pluck('total_sold')),
            backgroundColor: [
                '#5bbb7b',
                '#0d6efd',
                '#ffc107',
                '#dc3545',
                '#6c757d'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Day of Week Chart
const dayOfWeekCtx = document.getElementById('dayOfWeekChart').getContext('2d');
new Chart(dayOfWeekCtx, {
    type: 'bar',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Sales',
            data: [65, 59, 80, 81, 56, 95, 72],
            backgroundColor: '#5bbb7b'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
@endsection