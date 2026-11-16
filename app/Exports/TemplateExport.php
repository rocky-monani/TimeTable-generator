<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class TemplateExport implements FromArray
{
    public function array(): array
    {
        return [

            ['DATA'],

            ['department|Computer Engineering|CE'],
            ['department|Information Technology|IT'],

            ['section|CE-4-A|1'],
            ['section|CE-4-B|1'],

            ['subject|DBMS|DB201|1|3|2|core'],
            ['subject|Operating System|OS301|1|3|2|core'],

            ['faculty|Dr Patel|3|18|1'],
            ['faculty|Dr Shah|3|18|2'],

            ['room|A-108|classroom|60'],
            ['room|CS-LAB-1|lab|40']

        ];
    }
}
