<?php

namespace App\Http\Requests;

class GeneratePenerimaanStPembelianTonReportRequest extends BaseReportRequest
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
            'NoPenerimaanST' => ['required', 'string', 'max:13'],
        ];
    }

    public function noPenerimaanST(): string
    {
        return trim((string) $this->validated('NoPenerimaanST'));
    }
}
