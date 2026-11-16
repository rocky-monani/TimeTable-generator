@extends('layouts.app')

@section('title', 'Rooms & Labs')

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
        <div>
            <h2 class="h3 font-weight-bold text-dark mb-0"><i class="fas fa-door-open text-danger mr-2"></i> Manage Rooms & Labs</h2>
            <p class="text-muted mb-0">Add, edit, or remove classrooms and practical labs.</p>
        </div>
        <button class="btn btn-primary shadow-sm" id="addRoom"><i class="fas fa-plus mr-2"></i>Add Room</button>
    </div>

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <table id="roomTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Capacity</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>

        </div>

    </div>

    <!-- Modal -->
    <div class="modal fade" id="roomModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h4>Room</h4>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="id">

                    <div class="form-group">
                        <label>Room Name</label>
                        <input type="text" id="name" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Type</label>
                        <select id="type" class="form-control">
                            <option value="classroom">Classroom</option>
                            <option value="lab">Lab</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Capacity</label>
                        <input type="number" id="capacity" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success" id="saveRoom">Save</button>
                </div>

            </div>
        </div>
    </div>

@endsection

@push('scripts')

    <script>

        $(function () {

            let table = $('#roomTable').DataTable({

                processing: true,
                serverSide: true,

                ajax: '/rooms/list',

                columns: [
                    { data: 'id' },
                    { data: 'name' },

                    // 🔥 BETTER TYPE UI
                    {
                        data: 'type',
                        render: function (data) {
                            if (data === 'lab') {
                                return '<span class="badge badge-info">Lab</span>';
                            }
                            return '<span class="badge badge-success">Classroom</span>';
                        }
                    },

                    { data: 'capacity' },
                    { data: 'action', orderable: false, searchable: false }
                ]

            });

            // 🔥 ADD ROOM (RESET FORM)
            $('#addRoom').click(function () {

                $('#id').val('');
                $('#name').val('');
                $('#type').val('classroom');
                $('#capacity').val('');

                $('#roomModal').modal('show');

            });

            // 🔥 SAVE ROOM (WITH VALIDATION)
            $('#saveRoom').click(function () {

                let name = $('#name').val();

                if (!name) {
                    alert('Room name required');
                    return;
                }

                $.post('/rooms/store', {

                    id: $('#id').val(),
                    name: name,
                    type: $('#type').val(),
                    capacity: $('#capacity').val(),

                    _token: '{{csrf_token()}}'

                }, function () {

                    $('#roomModal').modal('hide');
                    table.ajax.reload();

                });

            });

            // 🔥 EDIT
            $(document).on('click', '.editBtn', function () {

                let id = $(this).data('id');

                $.get('/rooms/edit/' + id, function (data) {

                    $('#id').val(data.id);
                    $('#name').val(data.name);
                    $('#type').val(data.type);
                    $('#capacity').val(data.capacity);

                    $('#roomModal').modal('show');

                });

            });

            // 🔥 DELETE
            $(document).on('click', '.deleteBtn', function () {

                let id = $(this).data('id');

                if (confirm('Delete this room?')) {

                    $.ajax({

                        url: '/rooms/delete/' + id,
                        type: 'DELETE',

                        data: { _token: '{{ csrf_token() }}' },

                        success: function () {
                            table.ajax.reload();
                        }

                    });

                }

            });

        });

    </script>

@endpush