<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => Str::slug($this->input('name')),
            ]);
        } elseif ($this->filled('slug')) {
            $this->merge([
                'slug' => Str::slug($this->input('slug')),
            ]);
        }
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $categoryId = $category instanceof Category ? $category->id : $category;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($categoryId)],
            'slug' => ['required', 'string', 'max:120', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'slug.required' => 'Slug kategori wajib diisi.',
            'slug.unique' => 'Slug kategori sudah digunakan.',
            'slug.max' => 'Slug maksimal 120 karakter.',
            'sort_order.integer' => 'Urutan harus berupa angka.',
            'sort_order.min' => 'Urutan minimal 0.',
        ];
    }
}
