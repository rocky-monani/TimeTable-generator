<!DOCTYPE html>
<html>
<head>
    <title>Timetable Print - {{ $target->name }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            background: #f0f2f5;
            color: #000;
        }

        .controls {
            text-align: center;
            padding: 20px;
            background: #fff;
            border-bottom: 1px solid #ccc;
            font-family: Arial, sans-serif;
        }

        .btn-print {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .btn-print:hover {
            background-color: #0069d9;
        }

        /* PRINT AREA */
        #printArea {
            display: none;
            background: #fff;
            margin: 20px auto;
            max-width: 1024px;
            box-sizing: border-box;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        /* THE BORDER BOX FROM IMAGE */
        .gardi-border-box {
            border: 1px solid black;
        }

        /* HEADER */
        .gardi-header {
            border-bottom: 1px solid black;
        }

        .gardi-header img {
            width: 100%;
            height: auto;
            max-height: 100px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        /* SUBHEADER */
        .gardi-subheader {
            text-align: center;
            font-family: 'Times New Roman', serif;
            font-weight: bold;
            font-size: 15px;
            padding: 4px 0;
            border-bottom: 1px solid black;
            text-transform: uppercase;
        }

        /* INFO ROW */
        .gardi-info-row {
            display: flex;
            border-bottom: 1px solid black;
            font-size: 13px;
            font-weight: bold;
        }

        .gardi-info-col {
            flex: 1;
            padding: 3px 8px;
            border-right: 1px solid black;
        }
        .gardi-info-col:last-child {
            border-right: none;
        }

        /* TABLE */
        .gardi-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            text-align: center;
        }

        .gardi-table th,
        .gardi-table td {
            border-right: 1px solid black;
            border-bottom: 1px solid black;
            padding: 4px;
            vertical-align: middle;
        }

        .gardi-table th:last-child,
        .gardi-table td:last-child {
            border-right: none;
        }
        .gardi-table tbody tr:last-child td {
            border-bottom: none;
        }

        .gardi-table th {
            font-weight: bold;
            font-size: 13px;
            padding: 6px 3px;
        }

        .gardi-table .col-lec {
            width: 35px;
            font-weight: bold;
        }

        .gardi-table .col-timing {
            width: 130px;
            font-weight: bold;
        }

        .gardi-table .break-row td {
            background-color: #dcdcdc !important;
            font-weight: bold;
            letter-spacing: 2px;
            padding: 3px;
        }

        .cell-content {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        /* SIGNATURES */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            font-weight: bold;
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            text-align: center;
        }

        .sig-block {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 40px;
        }

        /* LEGEND */
        .legend-title {
            margin-top: 15px;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .legend-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        .legend-table th, .legend-table td {
            border: 1px solid black;
            padding: 2px 4px;
        }

        .loading {
            text-align: center;
            padding: 50px;
            font-size: 18px;
            color: #666;
            font-family: Arial, sans-serif;
        }

        /* PRINT SETTINGS */
        @media print {
            body {
                background: #fff;
                margin: 0;
            }
            .controls {
                display: none;
            }
            #printArea {
                display: block !important;
                margin: 0 auto;
                padding: 0;
                box-shadow: none;
                max-width: 100%;
                width: 100%;
                /* This forces everything to scale down to fit essentially 1 page horizontally/vertically where possible */
                page-break-inside: avoid;
            }
            
            /* Apply specific constraints to guarantee one page */
            html, body {
                height: 100vh;
                overflow: hidden;
            }

            .gardi-logo-area, .gardi-header img {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .break-row td {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Adjust sizes extremely tight for guaranteed fitting */
            .gardi-table th, .gardi-table td { padding: 3px !important; font-size: 11px !important; }
            .cell-content div { font-size: 10px !important; }
            .signatures { margin-top: 15px !important; font-size: 11px !important; }
            .sig-block div[style*="margin-top: 50px"] { margin-top: 25px !important; }
            .legend-title { margin-top: 10px !important; font-size: 11px !important; }
            .legend-table td, .legend-table th { padding: 1px 3px !important; font-size: 10px !important; }
            
            @page {
                size: A4 landscape;
                margin: 5mm;
            }
        }
    </style>
</head>
<body>

<div class="controls">
    <button class="btn-print" onclick="window.print()">Print Timetable</button>
    <div style="margin-top: 10px;"><a href="/timetable" style="color: #666; text-decoration: none;">&larr; Back to Timetable</a></div>
</div>

<div id="loading" class="loading">Loading timetable data...</div>

<!-- PRINT AREA -->
<div id="printArea" style="display:none;">

    <!-- TOP SECTION WITH BORDER -->
    <div class="gardi-border-box">

        <!-- HEADER -->
        <div class="gardi-header">
            <img src="/logo/Picture1.jpg" alt="Logo" onerror="this.style.display='none'">           
        </div>

        <!-- SUBHEADER -->
        <div class="gardi-subheader">
            TIME TABLE FOR A.Y. {{ date('Y') }}-{{ date('y', strtotime('+1 year')) }} | {{ $target->semester === '-' ? 'ALL' : ($target->semester % 2 == 0 ? 'EVEN' : 'ODD') }} SEMESTER
        </div>

        <!-- INFO ROW -->
        <div class="gardi-info-row">
            <div class="gardi-info-col" style="text-transform: uppercase;">
                Mode: {{ strtoupper($type) }}
            </div>
            <div class="gardi-info-col">
                Name: {{ $target->name }}
            </div>
            <div class="gardi-info-col">
                Info: {{ $target->department_name }}
            </div>
            <div class="gardi-info-col">
                W. E. F. : 
            </div>
        </div>

        <!-- TABLE -->
        <table class="gardi-table">
            <thead>
                <tr id="printHeader">
                    <th class="col-lec">Lec</th>
                    <th class="col-timing">Timing</th>
                    <!-- Days dynamically inserted here -->
                </tr>
            </thead>
            <tbody id="printBody"></tbody>
        </table>

    </div> <!-- End gardi-border-box -->

    <!-- SIGNATURES -->
    <div class="signatures">
        <div class="sig-block" style="flex: 1; text-align: left;">
            <div>PREPARED BY</div>
            <div style="margin-top: 50px;">CTTC Member</div>
        </div>
        <div class="sig-block" style="flex: 1; text-align: center;">
            <div>&nbsp;</div>
            <div style="margin-top: 50px;">HEAD OF THE DEPARTMENT</div>
        </div>
        <div class="sig-block" style="flex: 1; text-align: center;">
            <div>&nbsp;</div>
            <div style="margin-top: 50px;">CTTC HEAD</div>
        </div>
        <div class="sig-block" style="flex: 1; text-align: right;">
            <div>APPROVED BY</div>
            <div style="margin-top: 50px;">PRINCIPAL</div>
        </div>
    </div>

    <!-- LEGEND -->
    <div class="legend-title">LEGEND OF THE SUBJECTS:</div>
    <table class="legend-table">
        <thead>
            <tr>
                <th style="width: 50px;">Sr. No.</th>
                <th>Subject Name (Code)</th>
                <th>Abbreviation</th>
                <th style="width: 80px;">Theory</th>
                <th style="width: 80px;">Tutorial</th>
                <th style="width: 80px;">Practical</th>
            </tr>
        </thead>
        <tbody id="legendBody"></tbody>
    </table>

</div>

<script>
    const targetId = {{ $target->id }};
    const type = '{{ $type }}';
    
    document.addEventListener("DOMContentLoaded", function() {
        fetch(`/timetable/data/${targetId}?type=${type}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('printArea').style.display = 'block';
                
                if (Object.keys(data.timetableData || {}).length === 0) {
                    document.getElementById('printBody').innerHTML = "<tr><td colspan='100%' style='padding:20px;'>No timetable data found for this section. Please generate it first.</td></tr>";
                    return;
                }
                
                buildPrintTable(data);
                buildLegend(data);
            })
            .catch(error => {
                document.getElementById('loading').innerText = 'Failed to load timetable data.';
                console.error('Error fetching timetable:', error);
            });
    });

    function formatTime(time) {
        if (!time) return '';
        const [h, m] = time.split(':');
        let hour = parseInt(h);
        let ampm = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12 || 12;
        return (hour < 10 ? '0'+hour : hour) + ":" + m + " " + ampm;
    }

    function getBreakLabel(start, end) {
        if (!start || !end) return "BREAK";
        let t1 = new Date("1970-01-01T" + start);
        let t2 = new Date("1970-01-01T" + end);
        let diff = (t2 - t1) / 60000;
        return diff >= 25 ? "RECESS" : "BREAK";
    }

    function buildPrintTable(data) {
        let headerRow = document.getElementById("printHeader");
        let thDays = "";
        data.weekDays.forEach(day => {
            thDays += `<th>${day.day_name}</th>`;
        });
        headerRow.innerHTML = `<th class="col-lec">Lec</th><th class="col-timing">Timing</th>` + thDays;

        let body = "";
        let lecCount = 1;

        const labOverflow = data.labOverflowSlots || {};

        data.timeSlots.forEach((slot, slotIdx) => {
            body += "<tr class='" + (slot.type === "break" ? "break-row" : "") + "'>";

            let timingText = `${formatTime(slot.start_time)} TO ${formatTime(slot.end_time)}`;

            if (slot.type === "break") {
                let breakLabel = getBreakLabel(slot.start_time, slot.end_time);
                body += `
                    <td colspan="2">${timingText}</td>
                    <td colspan="${data.weekDays.length}">${breakLabel}</td>
                `;
            } else {
                body += `
                    <td class="col-lec">${lecCount++}</td>
                    <td class="col-timing">${timingText}</td>
                `;

                data.weekDays.forEach(day => {
                    const dayOverflow = labOverflow[day.id] || [];
                    if (dayOverflow.includes(slot.slot_no)) {
                        // This cell is covered by a rowspan from above
                        return;
                    }

                    let entries = data.timetableData[day.id]?.[slot.slot_no] || [];

                    if (entries.length > 0) {
                        // Determine rowspan
                        let rowspan = 1;
                        let isLab = false;
                        entries.forEach(e => { if(e.type === 'lab') isLab = true; });

                        if (isLab) {
                            // Check if NEXT slot for THIS day is in overflow
                            const nextSlot = data.timeSlots[slotIdx + 1];
                            if (nextSlot && (labOverflow[day.id] || []).includes(nextSlot.slot_no)) {
                                rowspan = 2;
                            }
                        }

                        let cell = '<div class="cell-content">';
                        
                        // Sort entries by batch (1, 2, 3...)
                        entries.sort((a, b) => (parseInt(a.batch) || 0) - (parseInt(b.batch) || 0));

                        entries.forEach((e, idx) => {
                            let displayName = e.subject_name;
                            if (e.subject_category === 'Elective' && e.elective_group) {
                                displayName += ` (${e.elective_group})`;
                            }
                            
                            let batchTag = '';
                            if (e.type === "lab" && e.batch) {
                                batchTag = `<div style="font-weight: bold; text-transform: uppercase; font-size: 10px; margin-bottom: 1px;">BATCH ${e.batch}</div>`;
                            }

                            let subDetails = '';
                            if (type !== 'section' && e.section_name) {
                                subDetails += '<div style="font-size: 9px; opacity: 0.8;">Sec: ' + e.section_name + '</div>';
                            }
                            if (type !== 'faculty' && e.faculty_name) {
                                subDetails += '<div style="font-size: 9px; opacity: 0.8;">' + e.faculty_name + '</div>';
                            }
                            if (type !== 'room' && e.room_name) {
                                subDetails += '<div style="font-size: 9px; opacity: 0.8;">' + e.room_name + '</div>';
                            }

                            // Show subject name only for the first entry in the cell
                            let subjectHtml = idx === 0 ? `<div style="font-weight: bold; font-size: 11px; margin-bottom: 3px; border-bottom: 1px solid #eee; padding-bottom: 2px;">${displayName}</div>` : '';

                            cell += `
                                <div style="${idx > 0 ? 'margin-top: 4px; padding-top: 2px;' : ''}">
                                    ${subjectHtml}
                                    ${batchTag}
                                    ${subDetails}
                                </div>
                            `;
                        });
                        cell += '</div>';
                        body += `<td rowspan="${rowspan}">${cell}</td>`;
                    } else {
                        body += "<td></td>";
                    }
                });
            }

            body += "</tr>";
        });

        document.getElementById("printBody").innerHTML = body;
    }

    function buildLegend(data) {
        const uniqueSubjects = {};
        
        // Extract subjects
        Object.values(data.timetableData || {}).forEach(daySlots => {
            Object.values(daySlots || {}).forEach(entries => {
                (entries || []).forEach(e => {
                    if(!uniqueSubjects[e.subject_id]) {
                        uniqueSubjects[e.subject_id] = e;
                    }
                });
            });
        });

        let legendBody = "";
        let sr = 1;
        
        let sortedSubjects = Object.values(uniqueSubjects).sort((a, b) => a.subject_name.localeCompare(b.subject_name));
        
        sortedSubjects.forEach(sub => {
            let codeStr = sub.subject_code ? `(${sub.subject_code})` : '';
            legendBody += `
                <tr>
                    <td>${sr++}</td>
                    <td style="text-align: left; padding-left: 10px;">${sub.subject_name} ${codeStr}</td>
                    <td>${sub.subject_short_name || '-'}</td>
                    <td>${sub.theoryHoursPerWeek || '-'}</td>
                    <td>${sub.tutorialHoursPerWeek || '-'}</td>
                    <td>${sub.practicalHoursPerWeek || '-'}</td>
                </tr>
            `;
        });

        // Ensure at least 7 rows in legend
        for (let i = sr; i <= 7; i++) {
            legendBody += `
                <tr>
                    <td style="height: 22px;">${i}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            `;
        }

        document.getElementById("legendBody").innerHTML = legendBody;
    }
</script>

</body>
</html>
