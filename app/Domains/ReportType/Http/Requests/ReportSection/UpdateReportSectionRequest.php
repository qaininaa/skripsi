<?php

namespace App\Domains\ReportType\Http\Requests\ReportSection;

class UpdateReportSectionRequest extends ReportSectionRequest
{
    protected function extraRules(): array
    {
        return [
            'order' => ['required', 'integer', 'min:0'],
        ];
    }
}
