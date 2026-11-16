@extends('layouts.app')

@section('title', 'GTU Syllabus Paste Import')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0"><i class="fas fa-paste mr-2"></i> Paste GTU Syllabus Table</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info border-0 shadow-sm">
                    <i class="fas fa-info-circle mr-2"></i> <strong>How to use:</strong> Open the GTU Syllabus PDF or Excel, copy the entire subject table including the headers (Subject code, Subject Name, L, T, P, etc), and paste it into the box below.
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="departmentSelect" class="font-weight-bold">Target Department</label>
                        <select id="departmentSelect" class="form-control">
                            <option value="">-- Select Department --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="pasteArea" class="font-weight-bold">Paste Data Here</label>
                    <textarea id="pasteArea" class="form-control" rows="10" placeholder="Exp.&#9;Subject code&#9;Branch code&#9;With_Eff_from&#9;Subject Name&#9;...&#10;&#9;3160001&#9;07&#9;Dec-2020&#9;Design Engineering II B&#9;..."></textarea>
                </div>

                <div class="text-right">
                    <button class="btn btn-secondary mr-2" id="clearBtn"><i class="fas fa-eraser mr-1"></i> Clear</button>
                    <button class="btn btn-primary" id="validateBtn"><i class="fas fa-magic mr-1"></i> Parse & Preview</button>
                </div>

                <div id="previewSection" style="display: none;" class="mt-5">
                    <h4 class="font-weight-bold text-success mb-3"><i class="fas fa-eye mr-2"></i> Preview Extracted Data</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="previewTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Subject Name</th>
                                    <th>Category</th>
                                    <th>Primary Faculty</th>
                                    <th>Secondary Faculty</th>
                                    <th>Sem</th>
                                    <th>L</th>
                                    <th>T</th>
                                    <th>P</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    
                    <div class="text-right mt-3 mb-2">
                        <button class="btn btn-success btn-lg" id="importBtn"><i class="fas fa-upload mr-1"></i> Confirm & Import Subjects</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let parsedSubjects = [];
    const facultiesHtml = `@foreach($faculties as $fac)<option value="{{ $fac->id }}">{!! addslashes($fac->name) !!}</option>@endforeach`;

    $(document).ready(function() {
        $('#clearBtn').click(function() {
            $('#pasteArea').val('');
            $('#previewSection').hide();
            parsedSubjects = [];
        });

        $('#validateBtn').click(function() {
            const text = $('#pasteArea').val().trim();
            if (!text) {
                alert('Please paste some data first!');
                return;
            }

            const deptId = $('#departmentSelect').val();
            if(!deptId) {
                alert('Please select a target department first!');
                return;
            }

            const lines = text.split('\n');
            let headerLine = -1;
            let headers = [];

            // Identify header row
            for (let i = 0; i < lines.length; i++) {
                let row = lines[i].split('\t').map(x => x.trim().toLowerCase());
                if (row.includes('subject code') || row.includes('subject name')) {
                    headerLine = i;
                    headers = row;
                    break;
                }
            }

            if (headerLine === -1) {
                alert('Could not find recognizable headers (e.g., "Subject code", "Subject Name") in the pasted data. Make sure you copied the table headers!');
                return;
            }

            parsedSubjects = [];
            let tbody = '';

            for (let i = headerLine + 1; i < lines.length; i++) {
                let rowText = lines[i].trimEnd();
                if (!rowText) continue;

                let row = rowText.split('\t').map(x => x.trim());
                let subject = {
                    subjectCode: '',
                    name: '',
                    category: '',
                    semester: 1,
                    theory: 0,
                    tutorial: 0,
                    practical: 0,
                    faculty_id: '',
                    faculty_id_2: ''
                };

                for (let j = 0; j < headers.length; j++) {
                    let h = headers[j] || '';
                    let val = row[j] || '';

                    if (h === 'subject code') subject.subjectCode = val;
                    if (h === 'subject name') subject.name = val;
                    if (h === 'category') subject.category = val;
                    if (h.includes('sem')) subject.semester = parseInt(val) || 1;
                    if (h === 'l.' || h === 'l') subject.theory = parseInt(val) || 0;
                    if (h === 't.' || h === 't') subject.tutorial = parseInt(val) || 0;
                    if (h === 'p.' || h === 'p') subject.practical = parseInt(val) || 0;
                }

                if (subject.subjectCode && subject.name && subject.subjectCode.toLowerCase() !== "subject code") {
                    // Quick cleanups for artifacts like double spaces or leading spaces
                    subject.name = subject.name.replace(/\s{2,}/g, ' ').trim();
                    parsedSubjects.push(subject);
                }
            }

            // Count occurrences of each category
            let categoryCounts = {};
            parsedSubjects.forEach(sub => {
                let cat = sub.category ? sub.category.trim() : '';
                if (cat) {
                    categoryCounts[cat] = (categoryCounts[cat] || 0) + 1;
                }
            });

            // Second pass: Update Category to Elective/Core and set elective_group
            parsedSubjects.forEach((sub, idx) => {
                let originalCat = sub.category ? sub.category.trim() : '';
                
                if (originalCat && categoryCounts[originalCat] > 1) {
                    sub.category = 'Elective';
                    sub.elective_group = originalCat;
                } else {
                    sub.category = 'Core';
                    sub.elective_group = null;
                }

                sub.faculty_id = '';
                sub.faculty_id_2 = '';

                tbody += `<tr>
                    <td>${sub.subjectCode}</td>
                    <td>${sub.name}</td>
                    <td>
                        <span class="badge badge-${sub.category === 'Elective' ? 'warning' : 'primary'}">${sub.category}</span>
                        ${sub.elective_group ? '<br><small class="text-muted">' + sub.elective_group + '</small>' : ''}
                    </td>
                    <td>
                        <select class="form-control form-control-sm row-faculty" data-idx="${idx}">
                            <option value="">-- None --</option>
                            ${facultiesHtml}
                        </select>
                    </td>
                    <td>
                        <select class="form-control form-control-sm row-faculty-2" data-idx="${idx}">
                            <option value="">-- None --</option>
                            ${facultiesHtml}
                        </select>
                    </td>
                    <td>${sub.semester}</td>
                    <td>${sub.theory}</td>
                    <td>${sub.tutorial}</td>
                    <td>${sub.practical}</td>
                </tr>`;
            });

            if (parsedSubjects.length === 0) {
                alert('No valid rows extracted. Please ensure the columns match the headers.');
                $('#previewSection').hide();
                return;
            }

            $('#previewTable tbody').html(tbody);
            $('#previewSection').show();

            // Set the DOM selects to match the assigned global values
            $('.row-faculty').each(function() {
                let idx = $(this).data('idx');
                if (parsedSubjects[idx].faculty_id) {
                    $(this).val(parsedSubjects[idx].faculty_id);
                }
            });
            $('.row-faculty-2').each(function() {
                let idx = $(this).data('idx');
                if (parsedSubjects[idx].faculty_id_2) {
                    $(this).val(parsedSubjects[idx].faculty_id_2);
                }
            });

            // Listen for user changes in the table overrides
            $('.row-faculty').change(function() {
                let idx = $(this).data('idx');
                parsedSubjects[idx].faculty_id = $(this).val();
            });
            $('.row-faculty-2').change(function() {
                let idx = $(this).data('idx');
                parsedSubjects[idx].faculty_id_2 = $(this).val();
            });
        });

        $('#importBtn').click(function() {
            if (parsedSubjects.length === 0) return;

            const deptId = $('#departmentSelect').val();
            if (!deptId) {
                alert('Please select a Target Department!');
                return;
            }

            $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Importing...');

            $.ajax({
                url: '/import/paste',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    department_id: deptId,
                    subjects: parsedSubjects
                },
                success: function(res) {
                    if (res.success) {
                        alert(res.message);
                        window.location.href = '/subjects';
                    } else {
                        alert('Error: ' + res.message);
                        $('#importBtn').prop('disabled', false).html('<i class="fas fa-upload mr-1"></i> Confirm & Import Subjects');
                    }
                },
                error: function(err) {
                    console.error(err);
                    alert('Server error occurred during import.');
                    $('#importBtn').prop('disabled', false).html('<i class="fas fa-upload mr-1"></i> Confirm & Import Subjects');
                }
            });
        });
    });
</script>
@endpush
