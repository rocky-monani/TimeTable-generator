@extends('layouts.app')

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
        <div>
            <h2 class="h3 font-weight-bold text-dark mb-0"><i class="fas fa-book text-primary mr-2"></i> Manage Subjects</h2>
            <p class="text-muted mb-0">Add, edit, or remove academic subjects and common curriculum</p>
        </div>
        <button class="btn btn-primary shadow-sm" id="addSubject"><i class="fas fa-plus mr-2"></i>Add New Subject</button>
    </div>

    <div class="card shadow-sm border-0">

        <div class="card-body">
            <table id="subjectTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Semester</th>
                        <th>Department</th>
                        <th>Theory</th>
                        <th>Practical</th>
                        <th>Category</th>
                        <th>Elective Group</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="subjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="subjectModalTitle">Add Subject</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="id">

                    <div class="form-group mb-2">
                        <label class="small text-muted">Subject Name</label>
                        <input type="text" id="name" class="form-control" placeholder="e.g. Machine Learning">
                    </div>

                    <div class="form-group mb-2">
                        <label class="small text-muted">Subject Code</label>
                        <input type="text" id="subjectCode" class="form-control" placeholder="e.g. CS401">
                    </div>

                    <div class="form-group mb-2">
                        <label class="small text-muted">Semester</label>
                        <input type="number" id="semester" class="form-control" placeholder="e.g. 4">
                    </div>

                    <div class="form-group mb-2">
                        <label class="small text-muted">Department</label>
                        <select id="departmentId" class="form-control">
                            <option value="">-- Select Department --</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-2">
                        <label class="small text-muted">Primary Faculty</label>
                        <select id="faculty_id" class="form-control">
                            <option value="">-- No Primary Faculty --</option>
                            @foreach($faculties as $f)
                                <option value="{{ $f->id }}">{{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-2">
                        <label class="small text-muted">Secondary Faculty (Optional)</label>
                        <select id="faculty_id_2" class="form-control">
                            <option value="">-- No Secondary Faculty --</option>
                            @foreach($faculties as $f)
                                <option value="{{ $f->id }}">{{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-2">
                        <label class="small text-muted">Theory Hours / Week</label>
                        <input type="number" id="theoryHoursPerWeek" class="form-control" placeholder="e.g. 3">
                    </div>

                    <div class="form-group mb-2">
                        <label class="small text-muted">Practical Hours / Week</label>
                        <input type="number" id="practicalHoursPerWeek" class="form-control" placeholder="e.g. 2">
                    </div>

                    <div class="form-group mb-2">
                        <label class="small text-muted">Category</label>
                        <select id="category" class="form-control">
                            <option value="">-- Select Category --</option>
                            <option value="Common">Common</option>
                            <option value="Elective">Elective</option>
                        </select>
                    </div>

                    <!-- Elective Group field (only relevant when category = Elective) -->
                    <div class="form-group mb-2" id="electiveGroupWrapper" style="display:none;">
                        <label class="small text-muted">
                            Elective Group Name
                            <span class="badge badge-warning ml-1">Elective Only</span>
                        </label>
                        <input type="text" id="elective_group" class="form-control"
                               placeholder="e.g. Open Elective A, Professional Elective 1">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Subjects with the <strong>same Elective Group name</strong> will be
                            scheduled at the <strong>exact same time slot</strong> across all sections.
                        </small>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" id="saveSubject">Save</button>
                </div>

            </div>
        </div>
    </div>

@endsection

@push('scripts')

    <script>

        $(function () {

            let table = $('#subjectTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/subjects/list',
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'subjectCode' },
                    { data: 'semester' },
                    { data: 'department' },
                    { data: 'theoryHoursPerWeek' },
                    { data: 'practicalHoursPerWeek' },
                    {
                        data: 'category',
                        render: function(data) {
                            if (data === 'Elective') {
                                return '<span class="badge badge-warning">Elective</span>';
                            }
                            return '<span class="badge badge-info">' + (data || 'Common') + '</span>';
                        }
                    },
                    {
                        data: 'elective_group',
                        render: function(data) {
                            if (data) {
                                return '<span class="badge badge-secondary">' + data + '</span>';
                            }
                            return '<span class="text-muted">—</span>';
                        }
                    },
                    { data: 'action', orderable: false, searchable: false }
                ]
            });

            // ========================
            // Show/hide elective group field based on category
            // ========================
            $(document).on('change', '#category', function () {
                if ($(this).val() === 'Elective') {
                    $('#electiveGroupWrapper').slideDown(200);
                } else {
                    $('#electiveGroupWrapper').slideUp(200);
                    $('#elective_group').val('');
                }
            });

            // ========================
            // ADD SUBJECT
            // ========================
            $('#addSubject').click(() => {
                $('#id').val('');
                $('#name').val('');
                $('#subjectCode').val('');
                $('#semester').val('');
                $('#departmentId').val('');
                $('#faculty_id').val('');
                $('#faculty_id_2').val('');
                $('#theoryHoursPerWeek').val('');
                $('#practicalHoursPerWeek').val('');
                $('#category').val('').trigger('change');
                $('#elective_group').val('');
                $('#subjectModalTitle').text('Add Subject');
                $('#subjectModal').modal('show');
            });

            // ========================
            // SAVE (Add or Update)
            // ========================
            $('#saveSubject').click(function () {

                let id = $('#id').val();
                let url = id ? '/subjects/update/' + id : '/subjects/store';

                $.post(url, {
                    _token: '{{ csrf_token() }}',
                    name: $('#name').val(),
                    subjectCode: $('#subjectCode').val(),
                    semester: $('#semester').val(),
                    departmentId: $('#departmentId').val(),
                    faculty_id: $('#faculty_id').val(),
                    faculty_id_2: $('#faculty_id_2').val(),
                    theoryHoursPerWeek: $('#theoryHoursPerWeek').val(),
                    practicalHoursPerWeek: $('#practicalHoursPerWeek').val(),
                    category: $('#category').val(),
                    elective_group: $('#elective_group').val()
                }, function () {
                    $('#subjectModal').modal('hide');
                    table.ajax.reload();
                });

            });

            // ========================
            // EDIT
            // ========================
            $('body').on('click', '.editBtn', function () {

                let id = $(this).data('id');

                $.get('/subjects/edit/' + id, function (data) {

                    $('#id').val(data.id);
                    $('#name').val(data.name);
                    $('#subjectCode').val(data.subjectCode);
                    $('#semester').val(data.semester);
                    $('#departmentId').val(data.departmentId);
                    $('#faculty_id').val(data.faculty_id);
                    $('#faculty_id_2').val(data.faculty_id_2);
                    $('#theoryHoursPerWeek').val(data.theoryHoursPerWeek);
                    $('#practicalHoursPerWeek').val(data.practicalHoursPerWeek);
                    $('#category').val(data.category || '').trigger('change');
                    $('#elective_group').val(data.elective_group || '');
                    $('#subjectModalTitle').text('Edit Subject');

                    $('#subjectModal').modal('show');
                });
            });

            // ========================
            // DELETE
            // ========================
            $('body').on('click', '.deleteBtn', function () {

                let id = $(this).data('id');

                if (confirm('Delete this subject?')) {

                    $.ajax({
                        url: '/subjects/delete/' + id,
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