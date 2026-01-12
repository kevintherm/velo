<?php

namespace App\Http\Requests;

use App\Models\Collection;
use App\Services\IndexStrategies\MysqlIndexStrategy;
use App\Services\RecordRulesCompiler;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Str;

class RecordRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = app(RecordRulesCompiler::class)
            ->forCollection($this->route()->parameter('collection'))
            ->using(new MysqlIndexStrategy)
            ->withForm($this->all())
            ->ignoreId($this->route()->parameter('recordId'))
            ->compile();

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [];
        $rules = $this->validationRules();

        foreach ($rules as $ruleName => $rule) {
            if (str_ends_with($ruleName, '.*')) {
                $index = Str::between($ruleName, 'fields.', '.options');
                $attributes[$ruleName] = "value on [{$index}]";
                continue;
            }

            $newName = explode('.', $ruleName);
            $newName = end($newName);
            $attributes[$ruleName] = Str::lower(Str::headline($newName));
        }

        return $attributes;
    }
}
