<?php

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use Auditable;

    protected $fillable = ['key', 'value', 'type', 'group', 'label', 'description'];

    public function getTypedValue(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'json'    => json_decode($this->value, true) ?? [],
            default   => $this->value,
        };
    }

    public function activityLogIdentifier(): ?string
    {
        return $this->label ?: $this->key;
    }
}
