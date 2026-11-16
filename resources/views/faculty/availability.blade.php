@extends('layouts.app')

@section('title', 'Faculty Availability')

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
        <div>
            <h2 class="h3 font-weight-bold text-dark mb-0"><i class="fas fa-user-clock text-warning mr-2"></i> Faculty Availability</h2>
            <p class="text-muted mb-0">Manage and specify available time slots for teachers.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <!-- Faculty Select -->
            <div class="form-group">
                <label><b>Select Faculty</b></label>
                <select id="faculty_id" class="form-control" onchange="window.location='?faculty_id='+this.value">

                    <option value="">-- Select Faculty --</option>

                    @foreach($faculties as $faculty)
                        <option value="{{$faculty->id}}" {{ request()->faculty_id == $faculty->id ? 'selected' : '' }}>
                            {{$faculty->name}}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(request()->faculty_id)

                <div class="alert alert-info">
                    Click to mark UNAVAILABLE (grey). Default is AVAILABLE (green)
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead class="bg-light">
                            <tr>
                                <th>Slot</th>
                                <th>Mon</th>
                                <th>Tue</th>
                                <th>Wed</th>
                                <th>Thu</th>
                                <th>Fri</th>
                                <th>Sat</th>
                            </tr>
                        </thead>

                        <tbody>

                            @for($slot = 1; $slot <= 8; $slot++)
                                <tr>
                                    <td><b>Slot {{$slot}}</b></td>

                                    @foreach(['mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day)

                                        @php
                                            $value = $availability[$day][$slot] ?? 1;
                                        @endphp

                                        <td>
                                            <button id="slot_{{$day}}_{{$slot}}"
                                                class="btn btn-sm toggle-btn {{ $value ? 'btn-success' : 'btn-danger' }}"
                                                data-day="{{$day}}" data-slot="{{$slot}}">

                                                <i class="fas {{ $value ? 'fa-check' : 'fa-times' }}"></i>
                                            </button>
                                        </td>

                                    @endforeach
                                </tr>
                            @endfor

                        </tbody>
                    </table>
                </div>

            @endif

        </div>
    </div>

@endsection


@push('scripts')

    <script>
        $(document).ready(function () {

            $('.toggle-btn').click(function () {

                let btn = $(this);
                let faculty = $('#faculty_id').val();
                let day = btn.data('day');
                let slot = btn.data('slot');

                $('.toggle-btn').prop('disabled', true);
                btn.html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "{{ route('faculty.availability.toggle') }}",
                    type: "POST",
                    data: {
                        faculty_id: faculty,
                        day: day,
                        slot: slot,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (res) {

                        if (res.state === 'unavailable') {
                            btn.removeClass('btn-success').addClass('btn-secondary');
                            btn.html('<i class="fas fa-times"></i>');
                        } else {
                            btn.removeClass('btn-secondary').addClass('btn-success');
                            btn.html('<i class="fas fa-check"></i>');
                        }

                        $('.toggle-btn').prop('disabled', false);
                    },
                    error: function () {
                        alert('Something went wrong');
                        $('.toggle-btn').prop('disabled', false);
                    }
                });

            });

        });
    </script>

@endpush