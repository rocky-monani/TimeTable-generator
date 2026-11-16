@extends('layouts.app')

@section('title', 'Timetable - ' . $section->name)

@section('content')

    <div class="row">
        <div class="col-12">

            <!-- Header Card -->
            <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
                <div>
                    <h2 class="h3 font-weight-bold text-dark mb-0"><i class="fas fa-calendar-alt text-primary mr-2"></i> Timetable: {{ $section->name }}</h2>
                    <p class="text-muted mb-0">Department: {{ $section->department_name }} | Semester: {{ $section->semester }}</p>
                </div>
                <div>
                    <a href="/timetable" class="btn btn-outline-secondary shadow-sm mr-2">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Back
                    </a>
                    <button onclick="window.print()" class="btn btn-primary shadow-sm">
                        <i class="fas fa-print mr-1"></i>
                        Print
                    </button>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Section:</strong> {{ $section->name }}
                        </div>
                        <div class="col-md-4">
                            <strong>Department:</strong> {{ $section->department_name }}
                        </div>
                        <div class="col-md-4">
                            <strong>Semester:</strong> {{ $section->semester }}
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <strong class="mr-3">Legend:</strong>
                                <span class="badge badge-info mr-3">
                                    <i class="fas fa-book mr-1"></i> Lecture
                                </span>
                                <span class="badge badge-success mr-3">
                                    <i class="fas fa-flask mr-1"></i> Lab
                                </span>
                                <span class="badge badge-secondary">
                                    <i class="fas fa-coffee mr-1"></i> Break
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timetable Card -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center align-middle" style="width: 150px;">
                                        <i class="far fa-clock mr-1"></i> Time
                                    </th>
                                    @foreach($weekDays as $day)
                                        <th class="text-center bg-gradient-light" style="min-width: 200px;">
                                            {{ $day->day_name }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timeSlots as $slotIndex => $slot)
                                    <tr>
                                        {{-- Time Column --}}
                                        @if($slot->type === 'break')
                                            <td class="text-center align-middle font-weight-bold bg-light">
                                                <i class="fas fa-coffee text-muted mr-1"></i>
                                                <div class="text-muted small">BREAK</div>
                                                <div class="text-xs">
                                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} -
                                                    {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                                </div>
                                            </td>
                                        @else
                                            <td class="text-center align-middle font-weight-bold" style="width: 120px;">
                                                <div class="text-primary" style="font-size: 0.9rem;">
                                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}</div>
                                                <div class="text-muted text-xs" style="margin: -2px 0;">to</div>
                                                <div class="text-primary" style="font-size: 0.9rem;">
                                                    {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}</div>
                                            </td>
                                        @endif

                                        {{-- Day Columns --}}
                                        @foreach($weekDays as $day)
                                            @php
                                                $isOverflow = isset($labOverflowSlots[$day->id]) && in_array($slot->slot_no, $labOverflowSlots[$day->id]);
                                            @endphp

                                            @if($isOverflow)
                                                @continue
                                            @endif

                                            @if($slot->type === 'break')
                                                <td class="text-center align-middle bg-secondary text-white font-weight-bold">
                                                    <i class="fas fa-coffee mr-2"></i>BREAK
                                                </td>
                                            @else
                                                @php
                                                    $cellData = $structuredData[$day->id][$slot->slot_no] ?? null;
                                                    $entries = $cellData['entries'] ?? [];
                                                    $hasMultipleBatches = count($entries) > 1;
                                                    
                                                    // Determine if this cell should span 2 rows (is a non-overflow lab slot)
                                                    // Controller tells us if the NEXT slot for this day is an overflow slot
                                                    $nextSlotIndex = $slotIndex + 1;
                                                    $spanRows = 1;
                                                    if ($cellData['is_lab'] ?? false) {
                                                        // Look ahead to see if next slot is overflow for this day
                                                        $nextSlot = $timeSlots[$nextSlotIndex] ?? null;
                                                        if ($nextSlot && isset($labOverflowSlots[$day->id]) && in_array($nextSlot->slot_no, $labOverflowSlots[$day->id])) {
                                                            $spanRows = 2;
                                                        }
                                                    }
                                                @endphp

                                                @if(count($entries) > 0)
                                                    @php
                                                        // Sort entries by batch sequence (1, 2, 3...)
                                                        $entries = collect($entries)->sortBy('batch')->values()->all();
                                                        
                                                        $firstEntry = $entries[0];
                                                        $isLab = $cellData['is_lab'];
                                                        $isCreativeBreak = stripos($firstEntry->subject_name, 'Creative Break') !== false;

                                                        if ($isCreativeBreak) {
                                                            $bgClass = 'bg-warning';
                                                            $icon = 'fa-paint-brush';
                                                        } elseif ($isLab) {
                                                            $bgClass = 'bg-success';
                                                            $icon = 'fa-flask';
                                                        } else {
                                                            $bgClass = 'bg-info';
                                                            $icon = 'fa-book';
                                                        }
                                                    @endphp

                                                    <td class="text-center align-middle {{ $bgClass }} text-white p-0" 
                                                        @if($spanRows > 1) rowspan="{{ $spanRows }}" @endif>
                                                        <div class="h-100 d-flex flex-column justify-content-center p-2">
                                                            <div class="mb-1">
                                                                <i class="fas {{ $icon }} {{ $isLab ? 'fa-lg' : '' }}"></i>
                                                            </div>
                                                            <div class="font-weight-bold mb-1" style="font-size: 0.95rem; line-height: 1.1;">
                                                                {{ $firstEntry->subject_name }}
                                                            </div>
                                                            
                                                            <div class="border-top border-white-50 mt-1 pt-1">
                                                                @foreach($entries as $entry)
                                                                    <div class="small {{ $loop->last ? '' : 'mb-2 border-bottom border-white-25 pb-1' }}" style="font-size: 0.75rem;">
                                                                        <div class="font-weight-bold">
                                                                            @if($isLab && $entry->batch)
                                                                                <span class="text-uppercase">Batch {{ $entry->batch }}</span>
                                                                            @endif
                                                                        </div>
                                                                        <div class="opacity-75">
                                                                            <i class="fas fa-user-tie mr-1"></i>{{ $entry->faculty_name }}
                                                                        </div>
                                                                        <div class="opacity-75">
                                                                            <i class="fas fa-map-marker-alt mr-1"></i>{{ $entry->room_name }}
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </td>
                                                @else
                                                    <td class="text-center align-middle bg-light text-muted" style="opacity: 0.6;">
                                                        <i class="fas fa-minus small"></i>
                                                    </td>
                                                @endif
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <small>
                        <i class="fas fa-info-circle mr-1"></i>
                        Lab sessions span 2 continuous time slots
                    </small>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <style>
        /* Print styles */
        @media print {

            .main-sidebar,
            .main-header,
            .main-footer,
            .card-tools,
            .btn {
                display: none !important;
            }

            .content-wrapper {
                margin: 0 !important;
                padding: 0 !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #000 !important;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }
        }
    </style>
@endpush