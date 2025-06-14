<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class Report implements FromView, ShouldAutoSize
{
    use Exportable;

    private array $data;

    public function __construct($data) {
        $this->data = $data;
    }
    public function view(): View
    {
        return view('exports.report', $this->data);
    }
}
