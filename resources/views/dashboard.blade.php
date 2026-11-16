@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<style>
    .dashboard-header {
        margin-bottom: 2rem;
    }
    .dashboard-header h1 {
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -0.025em;
    }
    .action-bar {
        background: #ffffff;
        padding: 1rem;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        display: flex;
        gap: 10px;
        margin-bottom: 2rem;
    }
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 24px;
        margin-bottom: 2.5rem;
    }
    .kpi-card {
        border-radius: 20px;
        padding: 24px;
        position: relative;
        overflow: hidden;
        color: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        cursor: pointer;
    }
    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    .kpi-card h3 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 4px;
        position: relative;
        z-index: 2;
    }
    .kpi-card p {
        font-size: 1rem;
        font-weight: 500;
        opacity: 0.9;
        margin: 0;
        position: relative;
        z-index: 2;
        letter-spacing: 0.025em;
        text-transform: uppercase;
    }
    .kpi-card .icon {
        position: absolute;
        right: -10px;
        bottom: -15px;
        font-size: 6rem;
        opacity: 0.15;
        transform: rotate(-10deg);
        transition: all 0.3s ease;
    }
    .kpi-card:hover .icon {
        transform: rotate(0deg) scale(1.1);
        opacity: 0.2;
    }

    /* Premium Gradients */
    .grad-primary { background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); }
    .grad-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .grad-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .grad-danger { background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); }
    .grad-info { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
    .grad-purple { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }
    .grad-dark { background: linear-gradient(135deg, #334155 0%, #0f172a 100%); }
    .grad-pink { background: linear-gradient(135deg, #ec4899 0%, #be185d 100%); }

    /* Status Cards */
    .status-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        height: 100%;
        border: 1px solid #f1f5f9;
    }
    .status-card h5 {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f5f9;
    }
    .modern-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .modern-list li {
        padding: 12px 16px;
        background: #f8fafc;
        border-radius: 8px;
        margin-bottom: 10px;
        font-weight: 600;
        color: #475569;
        display: flex;
        align-items: center;
        transition: all 0.2s;
    }
    .modern-list li:hover {
        background: #f1f5f9;
        color: #4f46e5;
        transform: translateX(4px);
    }
    .modern-list li::before {
        content: '•';
        color: #4f46e5;
        font-size: 1.5rem;
        margin-right: 12px;
        line-height: 0;
    }

    /* Alerts */
    .alert-modern {
        border-radius: 12px;
        border: none;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        font-weight: 600;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .alert-modern i { font-size: 1.4rem; margin-right: 12px; }
</style>

<div class="dashboard-header mt-2">
    <h1>Welcome back, Admin</h1>
    <p class="text-muted">Here's what's happening with your college timetable data today.</p>
</div>

<div class="action-bar">
    <a href="/excel-import" class="btn btn-success shadow-sm"><i class="fas fa-file-excel mr-2"></i> Import Excel</a>
    <a href="/subjects" class="btn btn-primary shadow-sm"><i class="fas fa-book mr-2"></i> Manage Subjects</a>
    <a href="/faculty" class="btn btn-info shadow-sm text-white"><i class="fas fa-user-tie mr-2"></i> Faculty</a>
    <a href="/rules" class="btn btn-dark shadow-sm"><i class="fas fa-cogs mr-2"></i> Rules Config</a>
</div>

{{-- ALERTS --}}
@if($sectionsWithoutRoom > 0)
<div class="alert alert-warning alert-modern mb-4 text-dark bg-light-warning" style="background-color: #fffbeb; color: #b45309;">
    <i class="fas fa-exclamation-triangle"></i>
    <span><strong>Action Needed:</strong> {{ $sectionsWithoutRoom }} sections are currently missing assigned classrooms.</span>
</div>
@endif

@if($subjectsWithoutLab > 0)
<div class="alert alert-danger alert-modern mb-4 text-dark bg-light-danger" style="background-color: #fef2f2; color: #b91c1c;">
    <i class="fas fa-times-circle"></i>
    <span><strong>Critical:</strong> {{ $subjectsWithoutLab }} practical subjects are missing lab assignments.</span>
</div>
@endif

{{-- KPI GRID --}}
<div class="dashboard-grid">

    <div class="kpi-card grad-info shadow-sm" onclick="window.location.href='/departments'">
        <h3>{{ $departments }}</h3>
        <p>Departments</p>
        <i class="fas fa-building icon"></i>
    </div>

    <div class="kpi-card grad-primary shadow-sm" onclick="window.location.href='/sections'">
        <h3>{{ $sections }}</h3>
        <p>Sections</p>
        <i class="fas fa-layer-group icon"></i>
    </div>

    <div class="kpi-card grad-warning shadow-sm" onclick="window.location.href='/subjects'">
        <h3>{{ $subjects }}</h3>
        <p>Subjects</p>
        <i class="fas fa-book icon"></i>
    </div>

    <div class="kpi-card grad-success shadow-sm" onclick="window.location.href='/faculty'">
        <h3>{{ $faculty }}</h3>
        <p>Faculty Members</p>
        <i class="fas fa-user-circle icon"></i>
    </div>

    <div class="kpi-card grad-pink shadow-sm" onclick="window.location.href='/rooms'">
        <h3>{{ $rooms }}</h3>
        <p>Classrooms</p>
        <i class="fas fa-door-open icon"></i>
    </div>

    <div class="kpi-card grad-purple shadow-sm" onclick="window.location.href='/subject-labs'">
        <h3>{{ $labs }}</h3>
        <p>Laboratories</p>
        <i class="fas fa-flask icon"></i>
    </div>

    <div class="kpi-card grad-dark shadow-sm" onclick="window.location.href='/rules'">
        <h3>{{ $rules }}</h3>
        <p>Active Rules</p>
        <i class="fas fa-sliders-h icon"></i>
    </div>

    <div class="kpi-card grad-danger shadow-sm" onclick="window.location.href='/generate'">
        <h3>{{ round($readiness) }}%</h3>
        <p>System Readiness</p>
        <i class="fas fa-check-circle icon"></i>
    </div>

</div>

{{-- RECENT DATA --}}
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="status-card">
            <h5><i class="fas fa-book-open text-primary mr-2"></i> Recently Added Subjects</h5>
            <ul class="modern-list">
                @foreach($recentSubjects as $s)
                    <li>{{ $s->name }} <span class="badge badge-light ml-auto text-muted">{{ $s->subjectCode }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="status-card">
            <h5><i class="fas fa-chalkboard-teacher text-success mr-2"></i> Recently Onboarded Faculty</h5>
            <ul class="modern-list">
                @foreach($recentFaculty as $f)
                    <li>{{ $f->name }} 
                        @if(isset($f->weeklyLoad)) <span class="badge badge-light ml-auto text-muted">Load: {{ $f->weeklyLoad }}</span> @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('deptChart');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                data: @json($chartData),
                backgroundColor: '#4f46e5'
            }]
        },
        options: {
            plugins: { legend: { display: false } }
        }
    });
</script>

@endsection