<?php

namespace App\Models;

use App\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use LogsActivity;

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

    protected function activityLogIdentifier(): ?string
    {
        return $this->label ?: $this->key;
    }
}
