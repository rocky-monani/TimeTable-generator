@extends('layouts.app')

@section('title', 'Subject Lab Mapping')

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
        <div>
            <h2 class="h3 font-weight-bold text-dark mb-0"><i class="fas fa-flask text-success mr-2"></i> Subject → Lab Mapping</h2>
            <p class="text-muted mb-0">Assign practical subjects to specific laboratory rooms.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <table id="mappingTable" class="table table-bordered table-striped">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Subject</th>
                        <th>Labs</th>
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

            let table = $('#mappingTable').DataTable({

                processing: true,
                serverSide: true,
                ajax: '/subject-labs/list',

                columns: [
                    { data: 'id' },
                    { data: 'subject' },
                    { data: 'labs' },
                    { data: 'action', orderable: false, searchable: false }
                ],
                drawCallback: function() {
                    $('.select2').select2({
                        theme: 'classic',
                        placeholder: "Select labs...",
                        allowClear: true
                    });
                }

            });

            $(document).on('click', '.saveBtn', function () {

                let subject = $(this).data('id');

                let labs = $('.labSelect_' + subject).val() || [];

                $.post('/subject-labs/save', {

                    subject_id: subject,
                    labs: labs,
                    _token: '{{csrf_token()}}'

                }, function () {

                    // 🔥 BETTER UX
                    alert('✅ Mapping Saved');
                    table.ajax.reload(null, false);

                });

            });

        });
    </script>

@endpush