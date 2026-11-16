@extends('layouts.app')

@section('title', 'Timetable')

@section('content')

    <div class="row">
        <div class="col-12">

            <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
                <div>
                    <h2 class="h3 font-weight-bold text-dark mb-0"><i class="fas fa-calendar-alt text-warning mr-2"></i> College Timetable</h2>
                    <p class="text-muted mb-0">View, manage, and export generated timetables.</p>
                </div>
            </div>

            {{-- ── GENERATION SUCCESS ── --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="fas fa-check-circle mr-2"></i><strong>{{ session('success') }}</strong>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            {{-- ── GENERATION ERRORS / WARNINGS ── --}}
            @if(session('generation_errors'))
                @php
                    $genErrors  = collect(session('generation_errors'));
                    $hardErrors = $genErrors->where('type', 'error');
                    $warnings   = $genErrors->where('type', 'warning');

                    // Helper to split "Option A/B" or "(1)/(2)/(3)" into bullet lines
                    function renderSuggestion($text) {
                        // Split on "(1)", "(2)", "(3)" or "Option A:", "Option B:"
                        $parts = preg_split('/(?=\s*(?:\(\d\)|\bOption [A-Z]:))/u', $text, -1, PREG_SPLIT_NO_EMPTY);
                        if (count($parts) <= 1) {
                            return '<span>' . e($text) . '</span>';
                        }
                        $html = '<ul class="mb-0 pl-3 mt-1">';
                        foreach ($parts as $part) {
                            $p = trim($part);
                            if ($p) $html .= '<li>' . e($p) . '</li>';
                        }
                        return $html . '</ul>';
                    }
                @endphp
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header {{ session('success') ? 'bg-warning' : 'bg-danger' }} text-white d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        @if(session('success'))
                            <strong>Timetable Generated With Incomplete Slots — Review the issues below</strong>
                        @else
                            <strong>Timetable Generation Blocked — Please fix the issues below first</strong>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        @if($hardErrors->count())
                            <div class="px-4 pt-3 pb-1">
                                <h6 class="text-danger font-weight-bold mb-0"><i class="fas fa-times-circle mr-1"></i> Errors <small class="text-muted font-weight-normal">(must fix before generating)</small></h6>
                            </div>
                            <ul class="list-group list-group-flush mb-2">
                                @foreach($hardErrors as $err)
                                <li class="list-group-item py-3">
                                    <div class="d-flex align-items-start">
                                        <span class="badge badge-danger mr-3 mt-1 flex-shrink-0" style="min-width:58px;text-align:center;">ERROR</span>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="badge badge-light border text-secondary mr-2" style="font-size:0.7rem;">{{ $err['section'] }}</span>
                                                <span class="font-weight-bold text-dark">{{ $err['message'] }}</span>
                                            </div>
                                            <div class="text-muted small mt-1 pl-1">
                                                <i class="fas fa-lightbulb text-warning mr-1"></i>
                                                <strong>How to fix:</strong>
                                                {!! renderSuggestion($err['suggestion']) !!}
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        @endif
                        @if($warnings->count())
                            <div class="px-4 pt-3 pb-1">
                                <h6 class="text-warning font-weight-bold mb-0"><i class="fas fa-exclamation-circle mr-1"></i> Warnings <small class="text-muted font-weight-normal">(generation may be incomplete)</small></h6>
                            </div>
                            <ul class="list-group list-group-flush mb-2">
                                @foreach($warnings as $warn)
                                <li class="list-group-item py-3">
                                    <div class="d-flex align-items-start">
                                        <span class="badge badge-warning mr-3 mt-1 flex-shrink-0" style="min-width:58px;text-align:center;">WARN</span>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="badge badge-light border text-secondary mr-2" style="font-size:0.7rem;">{{ $warn['section'] }}</span>
                                                <span class="font-weight-bold text-dark">{{ $warn['message'] }}</span>
                                            </div>
                                            <div class="text-muted small mt-1 pl-1">
                                                <i class="fas fa-lightbulb text-warning mr-1"></i>
                                                <strong>How to fix:</strong>
                                                {!! renderSuggestion($warn['suggestion']) !!}
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        @endif

                    </div>
                </div>
            @endif

            <!-- Section Selector Card -->

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Filter By</label>
                                        <select id="filterType" class="form-control">
                                            <option value="section">Section</option>
                                            <option value="faculty">Faculty</option>
                                            <option value="room">Room / Lab</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label id="targetLabel">Select Section</label>
                                        <select id="sectionSelect" class="form-control target-select">
                                            @foreach($sections as $section)
                                                <option value="{{ $section->id }}" {{ $section->id == $selectedSectionId ? 'selected' : '' }}>
                                                    {{ $section->name }} - {{ $section->department_name }} (Sem {{ $section->semester }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <select id="facultySelect" class="form-control target-select" style="display:none;">
                                            @foreach($faculties as $faculty)
                                                <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                            @endforeach
                                        </select>
                                        <select id="roomSelect" class="form-control target-select" style="display:none;">
                                            @foreach($rooms as $room)
                                                <option value="{{ $room->id }}">{{ $room->name }} ({{ strtoupper($room->type) }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button id="loadTimetable" class="btn btn-primary">
                                        <i class="fas fa-search mr-1"></i>
                                        View
                                    </button>

                                    <a id="editTimetable" href="#" class="btn btn-success ml-1">
                                        <i class="fas fa-edit mr-1"></i>
                                        Manual Edit
                                    </a>

                                    <div class="btn-group ml-1">
                                        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
                                            <i class="fas fa-cogs mr-1"></i> Generate
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" id="genThis">
                                                <i class="fas fa-calendar-check mr-2"></i> Only This Section
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="/generate" onclick="return confirm('Are you sure? This will delete ALL current timetables.')">
                                                <i class="fas fa-globe mr-2"></i> All Sections
                                            </a>
                                        </div>
                                    </div>

                                    <button id="printTimetable" class="btn btn-secondary ml-1">
                                        <i class="fas fa-print mr-1"></i>
                                        Print
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <strong class="mr-3">Legend:</strong>
                                <span class="badge badge-info mr-3">
                                    <i class="fas fa-book mr-1"></i> Lecture
                                </span>
                                <span class="badge badge-success mr-3">
                                    <i class="fas fa-flask mr-1"></i> Lab
                                </span>
                                <span class="badge badge-secondary mr-3">
                                    <i class="fas fa-coffee mr-1"></i> Break
                                </span>
                                <span class="badge badge-warning">
                                    <i class="fas fa-star mr-1"></i> Elective
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timetable Display Card -->
            <div class="card" id="timetableCard" style="display: none;">
                <div class="card-header bg-gradient-primary">
                    <h3 class="card-title text-white">
                        <i class="fas fa-table mr-2"></i>
                        Weekly Timetable - <span id="sectionName"></span>
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="timetableTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center align-middle"
                                        style="width: 150px; position: sticky; left: 0; background: #f8f9fa; z-index: 10;">
                                        <i class="far fa-clock mr-1"></i> Time
                                    </th>
                                    <!-- Days will be inserted here dynamically -->
                                </tr>
                            </thead>
                            <tbody id="timetableBody">
                                <!-- Timetable rows will be inserted here -->
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

            <!-- Loading Spinner -->
            <div class="text-center my-5" id="loadingSpinner" style="display: none;">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading timetable...</p>
            </div>

            <!-- No Data Message -->
            <div class="alert alert-warning text-center" id="noDataMessage" style="display: none;">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                No timetable data found for this section. Please generate the timetable first.
                <br>
                <a href="/generate" class="btn btn-sm btn-warning mt-2">
                    <i class="fas fa-cogs mr-1"></i>
                    Generate Timetable
                </a>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            
            // Auto-load timetable on page load
            loadTimetable();
            updateActionLinks();

            $('#filterType').on('change', function() {
                var type = $(this).val();
                
                // Hide all target selects
                $('.target-select').hide();
                
                // Show matching select and update label
                if(type === 'section') {
                    $('#targetLabel').text('Select Section');
                    $('#sectionSelect').show();
                } else if(type === 'faculty') {
                    $('#targetLabel').text('Select Faculty');$('#facultySelect').show();
                } else if(type === 'room') {
                    $('#targetLabel').text('Select Room / Lab');$('#roomSelect').show();
                }
                
                updateActionLinks();
                loadTimetable();
            });

            $('.target-select').on('change', function() {
                updateActionLinks();
                loadTimetable();
            });

            function updateActionLinks() {
                const type = $('#filterType').val();
                if(type === 'section') {
                    const sectionId = $('#sectionSelect').val();
                    if (sectionId) {
                        $('#genThis').attr('href', `/generate?section_id=${sectionId}`).parent().parent().show();
                        $('#editTimetable').attr('href', `/timetable/edit/${sectionId}`).show();
                    }
                } else {
                    $('#genThis').parent().parent().hide(); // hide generate button
                    $('#editTimetable').hide(); // hide manual edit button
                }
            }

            // Load timetable on button click
            $('#loadTimetable').click(function () {
                loadTimetable();
            });

            // Print functionality
            $('#printTimetable').click(function () {
                const type = $('#filterType').val();
                const targetId = $('#' + type + 'Select').val();
                if (targetId) {
                    window.open(`/timetable/print/${targetId}?type=${type}`, '_blank');
                } else {
                    alert('Please select a target first.');
                }
            });

            function loadTimetable() {
                const type = $('#filterType').val();
                const $targetSelect = $('#' + type + 'Select');
                const targetId = $targetSelect.val();
                const targetText = $targetSelect.find('option:selected').text();

                if(!targetId) return;

                // Show loading, hide others
                $('#loadingSpinner').show();
                $('#timetableCard').hide();
                $('#noDataMessage').hide();

                // Fetch timetable data
                $.ajax({
                    url: `/timetable/data/${targetId}?type=${type}`,
                    method: 'GET',
                    success: function (response) {
                        $('#loadingSpinner').hide();

                        if (Object.keys(response.timetableData).length === 0) {
                            $('#noDataMessage').show();
                            return;
                        }

                        // Update name
                        $('#sectionName').text(targetText);

                        response.type = type;
                        // Build timetable
                        buildTimetable(response);

                        $('#timetableCard').show();
                    },
                    error: function () {
                        $('#loadingSpinner').hide();
                        alert('Error loading timetable. Please try again.');
                    }
                });
            }

            function buildTimetable(data) {
                const { timeSlots, weekDays, timetableData, labOverflowSlots } = data;
                const labOverflow = labOverflowSlots || {};

                // Build header (days)
                let headerHtml = '<th class="text-center align-middle" style="width: 150px; position: sticky; left: 0; background: #f8f9fa; z-index: 10;"><i class="far fa-clock mr-1"></i> Time</th>';
                weekDays.forEach(day => {
                    headerHtml += `<th class="text-center bg-gradient-light" style="min-width: 200px;">${day.day_name}</th>`;
                });
                $('#timetableTable thead tr').html(headerHtml);

                // Build body (time slots x days)
                let bodyHtml = '';

                timeSlots.forEach((slot, slotIdx) => {
                    bodyHtml += '<tr>';

                    // Time column
                    if (slot.type === 'break') {
                        bodyHtml += `
                        <td class="text-center align-middle font-weight-bold bg-light" style="position: sticky; left: 0; z-index: 5;">
                            <i class="fas fa-coffee text-muted mr-1"></i>
                            <div class="text-muted small">BREAK</div>
                            <div class="text-xs">${formatTime(slot.start_time)} - ${formatTime(slot.end_time)}</div>
                        </td>
                    `;
                    } else {
                        bodyHtml += `
                        <td class="text-center align-middle font-weight-bold" style="position: sticky; left: 0; background: white; z-index: 5;">
                            <div class="text-primary">${formatTime(slot.start_time)}</div>
                            <div class="text-muted text-xs">to</div>
                            <div class="text-primary">${formatTime(slot.end_time)}</div>
                        </td>
                    `;
                    }

                    // Day columns
                    weekDays.forEach(day => {
                        const dayOverflow = labOverflow[day.id] || [];
                        if (dayOverflow.includes(slot.slot_no)) {
                            return; // Skip cell covered by rowspan
                        }

                        if (slot.type === 'break') {
                            bodyHtml += `<td class="text-center align-middle bg-secondary text-white font-weight-bold"><i class="fas fa-coffee mr-2"></i>BREAK</td>`;
                        } else {
                            const entries = timetableData[day.id] ? timetableData[day.id][slot.slot_no] : null;

                            if (entries && entries.length > 0) {
                                // Sort by batch 1, 2, 3
                                entries.sort((a, b) => (parseInt(a.batch) || 0) - (parseInt(b.batch) || 0));

                                const firstEntry = entries[0];
                                const isLab = entries.some(e => e.type === 'lab');
                                const isCreativeBreak = firstEntry.subject_name.toLowerCase().includes('creative break');
                                const isElective = firstEntry.subject_category === 'Elective' && !isLab;

                                // Determine rowspan
                                let rowspan = 1;
                                if (isLab) {
                                    const nextSlot = timeSlots[slotIdx+1];
                                    if (nextSlot && (labOverflow[day.id] || []).includes(nextSlot.slot_no)) {
                                        rowspan = 2;
                                    }
                                }

                                const bgClass = isCreativeBreak ? 'bg-warning' : (isLab ? 'bg-success' : (isElective ? 'bg-warning' : 'bg-info'));
                                const icon = isCreativeBreak ? 'fa-paint-brush' : (isLab ? 'fa-flask' : (isElective ? 'fa-star' : 'fa-book'));
                                const textColor = (isElective || isCreativeBreak) ? 'text-dark' : 'text-white';

                                let innerHtml = `
                                    <div class="p-2 h-100 d-flex flex-column justify-content-center">
                                        <div class="mb-2"><i class="fas ${icon} fa-lg"></i></div>
                                        <div class="font-weight-bold mb-2" style="font-size:0.95rem; line-height:1.2;">${firstEntry.subject_name}</div>
                                        <div class="border-top border-white-50 pt-2 text-left">`;

                                entries.forEach((e, idx) => {
                                    innerHtml += `
                                        <div class="${idx > 0 ? 'mt-2 border-top border-white-25 pt-2' : ''}" style="font-size:0.8rem;">
                                            ${e.type === 'lab' && e.batch ? `<div class="font-weight-bold text-uppercase mb-1" style="font-size:0.7rem; opacity:0.9;">Batch ${e.batch}</div>` : ''}
                                            <div class="mb-1"><i class="fas fa-user-tie mr-1 opacity-75"></i>${e.faculty_name || '-'}</div>
                                            <div><i class="fas fa-map-marker-alt mr-1 opacity-75"></i>${e.room_name || '-'}</div>
                                        </div>`;
                                });

                                innerHtml += `</div></div>`;

                                bodyHtml += `
                                    <td class="text-center align-middle ${bgClass} ${textColor} p-0" rowspan="${rowspan}" style="min-width:180px;">
                                        ${innerHtml}
                                    </td>`;

                            } else {
                                bodyHtml += `<td class="text-center align-middle bg-light text-muted" style="opacity:0.5;"><i class="fas fa-minus small"></i></td>`;
                            }
                        }
                    });

                    bodyHtml += '</tr>';
                });

                $('#timetableBody').html(bodyHtml);
            }

            function getNextNonBreakSlot(timeSlots, currentSlotNo) {
                const currentIndex = timeSlots.findIndex(s => s.slot_no === currentSlotNo);
                if (currentIndex === -1 || currentIndex === timeSlots.length - 1) return null;

                for (let i = currentIndex + 1; i < timeSlots.length; i++) {
                    if (timeSlots[i].type !== 'break') {
                        return timeSlots[i];
                    }
                }
                return null;
            }

            function formatTime(time) {
                // Convert 24hr format to 12hr format
                const [hours, minutes] = time.split(':');
                const hour = parseInt(hours);
                const ampm = hour >= 12 ? 'PM' : 'AM';
                const hour12 = hour % 12 || 12;
                return `${hour12}:${minutes} ${ampm}`;
            }
        });
    </script>

    <style>
        /* Print styles */
        @media print {

            .main-sidebar,
            .main-header,
            .main-footer,
            .card:first-child,
            #loadTimetable,
            #printTimetable {
                display: none !important;
            }

            .content-wrapper {
                margin: 0 !important;
                padding: 0 !important;
            }

            #timetableCard {
                box-shadow: none !important;
                border: none !important;
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

        /* Responsive table */
        @media screen and (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }

        /* Timetable cell styling */
        #timetableTable td {
            vertical-align: middle;
            font-size: 0.9rem;
            padding: 0.5rem;
        }

        #timetableTable th {
            font-weight: 600;
            padding: 0.75rem;
        }

        /* Sticky header for scrolling */
        #timetableTable thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f8f9fa;
        }

        /* Hover effect */
        #timetableTable tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
    </style>
@endpush