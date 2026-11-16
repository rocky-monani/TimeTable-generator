@extends('layouts.app')

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
        <div>
            <h2 class="h3 font-weight-bold text-dark mb-0"><i class="fas fa-layer-group text-primary mr-2"></i> Manage Sections</h2>
            <p class="text-muted mb-0">Organize student sections across semesters.</p>
        </div>
        <button class="btn btn-primary shadow-sm" id="addSection"><i class="fas fa-plus mr-2"></i>Add Section</button>
    </div>

    <div class="card shadow-sm border-0">

    <div class="card-body">

        <table id="sectionTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Section</th>
                    <th>Semester</th>
                    <th>Batches</th>
                    <th>Department</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>

    </div>

</div>

<!-- MODAL -->
<div class="modal fade" id="sectionModal">
    <div class="modal-dialog">
        <div class="modal-content p-3">

            <input type="hidden" id="id">

            <input type="text" id="name" class="form-control mb-2" placeholder="Section Name">

            <select id="semester" class="form-control mb-2">
                <option value="1">Sem 1</option>
                <option value="2">Sem 2</option>
                <option value="3">Sem 3</option>
                <option value="4">Sem 4</option>
                <option value="5">Sem 5</option>
                <option value="6">Sem 6</option>
                <option value="7">Sem 7</option>
                <option value="8">Sem 8</option>
            </select>

            <select id="departmentId" class="form-control mb-2">
                <option value="">Department</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>

            <select id="batches" class="form-control mb-2">
                <option value="1">1 Batch (Default)</option>
                <option value="2">2 Batches</option>
                <option value="3">3 Batches</option>
            </select>

            <button class="btn btn-success" id="saveSection">Save</button>

        </div>
    </div>
</div>

@endsection

@push('scripts')

<script>

$(function(){

    let table = $('#sectionTable').DataTable({
        processing:true,
        serverSide:true,
        ajax:'/sections/list',
        columns:[
            {data:'id'},
            {data:'name'},
            {data:'semester'},
            {data:'batches'},
            {data:'department'},
            {data:'action'}
        ]
    });

    $('#addSection').click(function(){
        $('#id').val('');
        $('#name').val('');
        $('#semester').val('1'); // ✅ FIXED
        $('#departmentId').val('');
        $('#batches').val('1');
        $('#sectionModal').modal('show');
    });

    $('#saveSection').click(function(){

        let id = $('#id').val();
        let url = id ? '/sections/update/'+id : '/sections/store';

        $.post(url,{
            _token:'{{ csrf_token() }}',
            name: $('#name').val(),
            semester: $('#semester').val(),
            departmentId: $('#departmentId').val(),
            batches: $('#batches').val()
        },function(){
            $('#sectionModal').modal('hide');
            table.ajax.reload();
        });

    });

    $(document).on('click','.editBtn',function(){

        let id=$(this).data('id');

        $.get('/sections/edit/'+id,function(data){

            $('#id').val(data.id);
            $('#name').val(data.name);
            $('#semester').val(data.semester);
            $('#departmentId').val(data.departmentId);
            $('#batches').val(data.batches || '1');

            $('#sectionModal').modal('show');

        });

    });

    $(document).on('click','.deleteBtn',function(){

        let id=$(this).data('id');

        if(confirm("Delete this section?")){
            $.ajax({
                url:'/sections/delete/'+id,
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