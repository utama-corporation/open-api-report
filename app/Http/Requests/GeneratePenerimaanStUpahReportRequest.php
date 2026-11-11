<?php

namespace App\Http\Requests;

class GeneratePenerimaanStUpahReportRequest extends BaseReportRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'NoUpah' => ['required', 'string', 'max:13'],
        ];
    }

    public function noUpah(): string
    {
        return trim((string) $this->validated('NoUpah'));
    }
}
