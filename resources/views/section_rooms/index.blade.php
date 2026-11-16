@extends('layouts.app')

@section('title', 'Section Classroom Mapping')

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
        <div>
            <h2 class="h3 font-weight-bold text-dark mb-0"><i class="fas fa-school text-info mr-2"></i> Section → Classroom Mapping</h2>
            <p class="text-muted mb-0">Assign student sections to their primary default classrooms.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <table id="sectionRoomTable" class="table table-bordered table-striped">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Section</th>
                        <th>Classroom</th>
                        <th>Action</th>
                    </tr>

                </thead>

            </table>

        </div>

    </div>

@endsection

@push('scripts')

    <script>

        $(function () {

            let table = $('#sectionRoomTable').DataTable({

                processing: true,
                serverSide: true,
                ajax: '/section-rooms/list',

                columns: [
                    { data: 'id' },
                    { data: 'section' },
                    { data: 'room' },
                    { data: 'action', orderable: false, searchable: false }
                ]

            });

            $(document).on('click', '.saveRoom', function () {

                let section = $(this).data('id');
                let room = $('#room_' + section).val();

                if (!room) {
                    alert('Select classroom');
                    return;
                }

                $.post('/section-rooms/save', {

                    section_id: section,
                    room_id: room,
                    _token: '{{csrf_token()}}'

                }, function (res) {

                    if (res.success) {
                        alert('✅ Mapping Saved');
                        table.ajax.reload(null, false);
                    } else {
                        alert(res.error);
                    }

                });

            });

        });
    </script>

@endpush