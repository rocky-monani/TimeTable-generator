@extends('layouts.app')

@section('title', 'Rule Configuration')

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
        <div>
            <h2 class="h3 font-weight-bold text-dark mb-0"><i class="fas fa-sliders-h text-purple mr-2"></i> System Rules Configuration</h2>
            <p class="text-muted mb-0">Set generation parameters, constraints, and operational rules.</p>
        </div>
        <button class="btn btn-primary shadow-sm" id="addRule"><i class="fas fa-plus mr-2"></i>Add Rule</button>
    </div>

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <table id="rulesTable" class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rule Key</th>
                        <th>Rule Value</th>
                        <th>Action</th>
                    </tr>
                </thead>

            </table>

        </div>

    </div>

    <!-- Modal -->

    <div class="modal fade" id="ruleModal">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">
                    <h4>Rule</h4>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="id">

                    <div class="form-group">
                        <label>Rule Key</label>
                        <input type="text" id="rule_key" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Rule Value</label>
                        <input type="text" id="rule_value" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">

                    <button class="btn btn-success" id="saveRule">
                        Save
                    </button>

                </div>

            </div>

        </div>

    </div>

@endsection

@push('scripts')

    <script>

        $(function () {

            let table = $('#rulesTable').DataTable({

                processing: true,
                serverSide: true,

                ajax: '/rules/list',

                columns: [

                    { data: 'id' },
                    { data: 'rule_key' },
                    { data: 'rule_value' },
                    { data: 'action', orderable: false, searchable: false }

                ]

            });


            $('#addRule').click(function () {

                $('#ruleModal').modal('show');

            });


            $('#saveRule').click(function () {

                $.post('/rules/store', {

                    id: $('#id').val(),
                    rule_key: $('#rule_key').val(),
                    rule_value: $('#rule_value').val(),

                    _token: '{{csrf_token()}}'

                }, function () {

                    $('#ruleModal').modal('hide');
                    table.ajax.reload();

                });

            });


            $(document).on('click', '.editBtn', function () {

                let id = $(this).data('id');

                $.get('/rules/edit/' + id, function (data) {

                    $('#id').val(data.id);
                    $('#rule_key').val(data.rule_key);
                    $('#rule_value').val(data.rule_value);

                    $('#ruleModal').modal('show');

                });

            });


            $(document).on('click', '.deleteBtn', function () {

                let id = $(this).data('id');

                if (confirm("Delete this rule?")) {

                    $.ajax({

                        url: '/rules/delete/' + id,
                        type: 'DELETE',

                        data: { _token: '{{csrf_token()}}' },

                        success: function () {

                            table.ajax.reload();

                        }

                    });

                }

            });

        });

    </script>

@endpush