@extends('layouts.app')

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
        <div>
            <h2 class="h3 font-weight-bold text-dark mb-0"><i class="fas fa-user-circle text-success mr-2"></i> Manage Faculty</h2>
            <p class="text-muted mb-0">Add, edit, or remove teaching staff and set their workloads.</p>
        </div>
        <button class="btn btn-primary shadow-sm" id="addFaculty"><i class="fas fa-plus mr-2"></i>Add Faculty</button>
    </div>

    <div class="card shadow-sm border-0">

    <div class="card-body">
        <table id="facultyTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Max Daily Hours</th>
                    <th>Weekly Load</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="facultyModal">
    <div class="modal-dialog">
        <div class="modal-content p-3">

            <input type="hidden" id="id">

            <input type="text" id="name" class="form-control mb-2" placeholder="Name">
            <input type="number" id="maxDailyHours" class="form-control mb-2" placeholder="Max Daily Hours">
            <input type="number" id="weeklyLoad" class="form-control mb-2" placeholder="Weekly Load">

            <button class="btn btn-success" id="saveFaculty">Save</button>

        </div>
    </div>
</div>

@endsection

@push('scripts')

<script>

$(function () {

    let table = $('#facultyTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/faculty/list',
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'maxDailyHours' },
            { data: 'weeklyLoad' },
            { data: 'action' }
        ]
    });

    $('#addFaculty').click(function () {
        $('#id').val('');
        $('#name').val('');
        $('#maxDailyHours').val('');
        $('#weeklyLoad').val('');
        $('#facultyModal').modal('show');
    });

    $('#saveFaculty').click(function () {

        $.post('/faculty/store', {
            _token: '{{ csrf_token() }}',
            id: $('#id').val(),
            name: $('#name').val(),
            maxDailyHours: $('#maxDailyHours').val(),
            weeklyLoad: $('#weeklyLoad').val()
        }, function () {

            $('#facultyModal').modal('hide');
            table.ajax.reload();

        });

    });

    $(document).on('click', '.editBtn', function () {

        let id = $(this).data('id');

        $.get('/faculty/edit/' + id, function (data) {

            $('#id').val(data.id);
            $('#name').val(data.name);
            $('#maxDailyHours').val(data.maxDailyHours);
            $('#weeklyLoad').val(data.weeklyLoad);

            $('#facultyModal').modal('show');
        });

    });

    $(document).on('click', '.deleteBtn', function () {

        let id = $(this).data('id');

        if (confirm("Delete this faculty?")) {

            $.ajax({
                url: '/faculty/delete/' + id,
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