<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ExportReportRequest extends ReportRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->can('report.export');
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'format' => ['required', Rule::in(['xlsx', 'csv', 'pdf'])],
        ]);
    }
}
