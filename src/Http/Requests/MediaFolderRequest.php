<?php

namespace SkalDoe\MediaLibrary\Http\Requests;

use SkalDoe\MediaLibrary\Models\MediaFolder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MediaFolderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $folder = $this->route('folder');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('media_folders', 'name')
                    ->where('parent_id', $this->input('parent_id'))
                    ->ignore($folder?->id),
            ],
            'parent_id' => [
                'nullable',
                'exists:media_folders,id',
                function ($attribute, $value, $fail) use ($folder) {
                    if (! $folder || ! $value) {
                        return;
                    }

                    if ($value === $folder->id) {
                        $fail('Un dossier ne peut pas être son propre parent.');
                        return;
                    }

                    $current = MediaFolder::find($value);

                    while ($current) {
                        if ($current->id === $folder->id) {
                            $fail('Impossible de déplacer un dossier dans l\'un de ses propres sous-dossiers.');
                            return;
                        }
                        $current = $current->parent;
                    }
                },
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nom',
            'parent_id' => 'Dossier parent',
        ];
    }
}
