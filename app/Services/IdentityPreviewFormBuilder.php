<?php

namespace App\Services;

class IdentityPreviewFormBuilder
{
    /**
     * @param  array{
     *     first_name?: string,
     *     last_name?: string,
     *     father_name?: string,
     *     mother_name?: string,
     *     date_of_birth?: null|string,
     *     national_id?: string
     * }  $extractedData
     * @return list<array{
     *     key: string,
     *     label: string,
     *     type: string,
     *     value: null|string,
     *     editable: bool,
     *     required: bool
     * }>
     */
    public function build(array $extractedData): array
    {
        $fields = [
            [
                'key' => 'first_name',
                'label' => 'First Name',
                'type' => 'text',
                'value' => $extractedData['first_name'] ?? '',
                'editable' => true,
                'required' => true,
            ],
            [
                'key' => 'last_name',
                'label' => 'Last Name',
                'type' => 'text',
                'value' => $extractedData['last_name'] ?? '',
                'editable' => true,
                'required' => true,
            ],
            [
                'key' => 'father_name',
                'label' => 'Father Name',
                'type' => 'text',
                'value' => $extractedData['father_name'] ?? '',
                'editable' => true,
                'required' => true,
            ],
            [
                'key' => 'mother_name',
                'label' => 'Mother Name',
                'type' => 'text',
                'value' => $extractedData['mother_name'] ?? '',
                'editable' => true,
                'required' => true,
            ],
            [
                'key' => 'date_of_birth',
                'label' => 'Date of Birth',
                'type' => 'date',
                'value' => $extractedData['date_of_birth'] ?? null,
                'editable' => true,
                'required' => true,
            ],
            [
                'key' => 'national_id',
                'label' => 'National ID',
                'type' => 'text',
                'value' => $extractedData['national_id'] ?? '',
                'editable' => true,
                'required' => true,
            ],
        ];

        return array_map(function (array $field): array {
            if ($field['type'] === 'date') {
                $field['value'] = $field['value'] === '' ? null : $field['value'];
            } else {
                $field['value'] = (string) $field['value'];
            }

            return $field;
        }, $fields);
    }
}
