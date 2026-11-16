@extends('layouts.app')

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
        <div>
            <h2 class="h3 font-weight-bold text-dark mb-0"><i class="fas fa-building text-info mr-2"></i> Manage Departments</h2>
            <p class="text-muted mb-0">Organize and structure the college departments.</p>
        </div>
        <button class="btn btn-primary shadow-sm" id="addDepartment"><i class="fas fa-plus mr-2"></i>Add Department</button>
    </div>

    <div class="card shadow-sm border-0">

    <div class="card-body">

        <table id="departmentTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Department</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>

    </div>

</div>

<!-- MODAL -->
<div class="modal fade" id="departmentModal">
    <div class="modal-dialog">
        <div class="modal-content p-3">

            <input type="hidden" id="id">
            <input type="text" id="name" class="form-control mb-2" placeholder="Department Name">

            <button class="btn btn-success" id="saveDepartment">Save</button>

        </div>
    </div>
</div>

@endsection

@push('scripts')

<script>

$(function(){

    let table = $('#departmentTable').DataTable({
        processing:true,
        serverSide:true,
        ajax:'/departments/list',
        columns:[
            {data:'id'},
            {data:'name'},
            {data:'action',orderable:false,searchable:false}
        ]
    });

    $('#addDepartment').click(function(){
        $('#id').val('');
        $('#name').val('');
        $('#departmentModal').modal('show');
    });

    $('#saveDepartment').click(function(){

        let id = $('#id').val();
        let url = id ? '/departments/update/'+id : '/departments/store';

        $.post(url,{
            name: $('#name').val(),
            _token:'{{ csrf_token() }}'
        },function(){
            $('#departmentModal').modal('hide');
            table.ajax.reload();
        });

    });

    $(document).on('click','.editBtn',function(){

        let id=$(this).data('id');

        $.get('/departments/edit/'+id,function(data){
            $('#id').val(data.id);
            $('#name').val(data.name);
            $('#departmentModal').modal('show');
        });

    });

    $(document).on('click','.deleteBtn',function(){

        let id=$(this).data('id');

        if(confirm("Delete this department?")){
            $.ajax({
                url:'/departments/delete/'+id,
                type:'DELETE',
                data:{ _token:'{{ csrf_token() }}' },
                success:function(){
                    table.ajax.reload();
                }
            });
        }

    });

});

</script>

@endpush