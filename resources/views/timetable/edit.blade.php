@extends('layouts.app')

@section('title', 'Timetable Architect')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Area -->
        <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
            <div>
                <h1 class="h3 font-weight-bold text-dark mb-1">
                    <i class="fas fa-magic text-primary mr-2"></i>
                    Visual Architect
                </h1>
                <p class="text-muted mb-0"><span class="badge badge-primary">{{ $section->department_name }}</span> &bull; <span class="font-weight-bold">{{ $section->name }}</span></p>
            </div>
            <div class="btn-group">
                <a href="/timetable" class="btn btn-outline-secondary px-3 mr-2 rounded-pill">
                    <i class="fas fa-times mr-2"></i>Close
                </a>
                <button onclick="window.location.reload();" class="btn btn-primary px-4 rounded-pill shadow">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
            </div>
        </div>

        @php
            // Color map for subjects to keep it colorful but consistent
            $colorPalette = [
                '#E3F2FD', '#F1F8E9', '#FFF3E0', '#F3E5F5', '#E0F2F1', 
                '#FFFDE7', '#FCE4EC', '#FBE9E7', '#E8EAF6', '#EFEBE9'
            ];
            $borderPalette = [
                '#2196F3', '#4CAF50', '#FF9800', '#9C27B0', '#009688', 
                '#FBC02D', '#E91E63', '#FF5722', '#3F51B5', '#795548'
            ];
        @endphp

                <div class="table-responsive">
                    <table class="table table-bordered table-hover m-0" style="table-layout: fixed; min-width: 1000px;">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center align-middle" style="width: 120px; background: #f8f9fa;">
                                    <i class="far fa-clock mr-1"></i> Time
                                </th>
                                @foreach($weekDays as $day)
                                    <th class="text-center align-middle" style="color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 0.85rem;">
                                        {{ $day->day_name }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timeSlots as $slotIndex => $slot)
                                <tr>
                                    <td class="text-center align-middle font-weight-bold {{ $slot->type == 'break' ? 'bg-light text-muted' : 'text-primary' }}" style="background: #fff; height: 80px;">
                                        <div style="font-size: 1.1rem;">{{ \Carbon\Carbon::parse($slot->start_time)->format('h:i') }}</div>
                                        <div class="small" style="opacity: 0.8;">{{ \Carbon\Carbon::parse($slot->start_time)->format('A') }}</div>
                                    </td>
                                    
                                    @foreach($weekDays as $day)
                                        @php
                                            $isOverflow = isset($labOverflowSlots[$day->id]) && in_array($slot->slot_no, $labOverflowSlots[$day->id]);
                                        @endphp

                                        @if($isOverflow) @continue @endif

                                        @if($slot->type === 'break')
                                            <td class="text-center align-middle bg-secondary text-white font-weight-bold">
                                                <i class="fas fa-coffee mr-2"></i>BREAK
                                            </td>
                                        @else
                                            @php
                                                $entries = $structuredData[$day->id][$slot->slot_no] ?? [];
                                                
                                                $isLab = false;
                                                foreach($entries as $e) { if($e->type === 'lab') $isLab = true; }

                                                $spanRows = 1;
                                                if ($isLab) {
                                                    $nextSlot = $timeSlots[$slotIndex + 1] ?? null;
                                                    if ($nextSlot && isset($labOverflowSlots[$day->id]) && in_array($nextSlot->slot_no, $labOverflowSlots[$day->id])) {
                                                        $spanRows = 2;
                                                    }
                                                }

                                                $bgClass = 'bg-light text-muted';
                                                $icon = '';
                                                $isCreativeBreak = false;

                                                if (count($entries) > 0) {
                                                    // Sort for display consistency
                                                    $entries = collect($entries)->sortBy('batch')->values()->all();
                                                    $firstEntry = $entries[0];
                                                    $isCreativeBreak = stripos($firstEntry->subject_name, 'Creative Break') !== false;

                                                    if ($isCreativeBreak) {
                                                        $bgClass = 'bg-warning text-dark';
                                                        $icon = 'fa-paint-brush';
                                                    } elseif ($isLab) {
                                                        $bgClass = 'bg-success text-white';
                                                        $icon = 'fa-flask';
                                                    } else {
                                                        $bgClass = 'bg-info text-white';
                                                        $icon = 'fa-book';
                                                    }
                                                }
                                            @endphp

                                            <td class="text-center align-middle edit-trigger {{ $bgClass }} p-0" 
                                                data-day="{{ $day->id }}" 
                                                data-slot="{{ $slot->slot_no }}"
                                                data-toggle="modal" 
                                                data-target="#editSlotModal"
                                                style="cursor: pointer; transition: transform 0.15s; border: 1px solid #dee2e6;"
                                                @if($spanRows > 1) rowspan="{{ $spanRows }}" @endif>
                                                
                                                @if(count($entries) > 0)
                                                    <div class="h-100 d-flex flex-column justify-content-center p-2">
                                                        <div class="mb-1">
                                                            <i class="fas {{ $icon }} {{ $isLab ? 'fa-lg' : '' }}"></i>
                                                        </div>
                                                        <div class="font-weight-bold mb-1" style="font-size: 0.9rem; line-height: 1.1;">
                                                            {{ $entries[0]->subject_name }}
                                                        </div>
                                                        
                                                        <div class="border-top border-white-50 mt-1 pt-1">
                                                            @foreach($entries as $entry)
                                                                <div class="small {{ $loop->last ? '' : 'mb-2 border-bottom border-white-25 pb-1' }}" style="font-size: 0.7rem;">
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
                                                @else
                                                    <div class="p-2" style="opacity: 0.5;">
                                                        <i class="fas fa-plus-circle fa-lg"></i>
                                                        <div class="small mt-1 font-weight-bold">EMPTY</div>
                                                    </div>
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Vibrant Edit Modal -->
<div class="modal fade" id="editSlotModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl overflow-hidden" style="border-radius: 20px;">
            <div class="modal-header bg-white border-bottom-0">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-pen-nib text-primary mr-2"></i>Edit Schedule Slot</h5>
                <button type="button" class="close text-dark" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editSlotForm">
                @csrf
                <input type="hidden" name="section_id" value="{{ $section->id }}">
                <input type="hidden" name="day_id" id="modalDayId">
                <input type="hidden" name="slot_no" id="modalSlotNo">

                <div class="modal-body p-4 bg-light">
                    <div class="form-group mb-4">
                        <label class="font-weight-bold small text-muted text-uppercase mb-2">Academic Subject</label>
                        <select name="subject_id" id="modalSubjectId" class="form-control select-professional">
                            <option value="empty">🗑️ Remove Entry (Clear Slot)</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" 
                                    data-f1="{{ $s->faculty_id }}" 
                                    data-f2="{{ $s->faculty_id_2 }}">
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold small text-muted text-uppercase mb-2">Assign Teacher / Faculty</label>
                        <select name="faculty_id" id="modalFacultyId" class="form-control select-professional">
                            <!-- Dynamic populated -->
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold small text-muted text-uppercase mb-2">Location</label>
                                <select name="room_id" id="modalRoomId" class="form-control select-professional">
                                    @foreach($rooms as $r)
                                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold small text-muted text-uppercase mb-2">Modality</label>
                                <select name="type" id="modalType" class="form-control select-professional">
                                    <option value="lecture">Lecture</option>
                                    <option value="lab">Lab / Practical</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 justify-content-center bg-white">
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold rounded-pill shadow-lg">
                        SAVE CONFIGURATION
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Modern Styles */
.tracking-widest { letter-spacing: 0.15rem; }
.border-radius-xl { border-radius: 1.5rem; }

/* Timetable cell styling matching view */
.edit-trigger:hover {
    filter: brightness(0.95);
    box-shadow: inset 0 0 0 2px rgba(0,0,0,0.1);
}

/* Modal Inputs */
.select-professional {
    height: 50px;
    border-radius: 10px;
    border: 2px solid #e3e6f0;
    font-weight: 600;
}
.select-professional:focus {
    border-color: #4e73df;
    box-shadow: none;
}
.small-info {
    font-size: 0.65rem;
    font-weight: 500;
    color: #475569;
    line-height: 1.3;
    display: flex;
    align-items: center;
}
.small-info i { width: 14px; opacity: 0.7; }
</style>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Shared faculty data
    const facultyNames = @json($faculties->pluck('name', 'id'));

    // Update faculty dropdown based on subject selection
    function updateFacultyList() {
        const option = $('#modalSubjectId').find(':selected');
        const f1 = option.data('f1');
        const f2 = option.data('f2');
        const select = $('#modalFacultyId');
        
        select.empty();
        
        if (option.val() === 'empty') {
            select.append('<option value="">N/A</option>');
            return;
        }
        
        if (f1 && facultyNames[f1]) {
            select.append(`<option value="${f1}">${facultyNames[f1]} (Primary)</option>`);
        }
        
        if (f2 && facultyNames[f2]) {
            select.append(`<option value="${f2}">${facultyNames[f2]} (Secondary)</option>`);
        }

        if (select.children().length === 0) {
            select.append('<option value="">No Faculty Assigned</option>');
        }
    }

    $('#modalSubjectId').change(updateFacultyList);

    $('.edit-trigger').click(function() {
        $('#modalDayId').val($(this).data('day'));
        $('#modalSlotNo').val($(this).data('slot'));
        // Trigger list refresh
        setTimeout(updateFacultyList, 100); 
    });

    $('#editSlotForm').submit(function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).text('PROCESSING...');
        
        $.ajax({
            url: '/timetable/update-slot',
            method: 'POST',
            data: $(this).serialize(),
            success: function() { 
                window.location.reload(); 
            },
            error: function(xhr) { 
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Save failed! Possible conflict or session timeout.';
                alert(msg); 
                btn.prop('disabled', false).text('SAVE CONFIGURATION');
            }
        });
    });
});
</script>
@endpush
