<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;

class AutomaticDispatch extends Model
{
    protected $table = 'automatic_dispatch';

    protected $fillable = [
        'id',
        'institution_id',
        'provider_type_id',
        'wait_time_limit',
        'max_delivery'
    ];

    protected $dates = ['created_at', 'updated_at'];

    /**
	 * Gets institution associated record.
	 */
    public function institution()
    {
        return $this->hasOne(\Institution::class, 'institution_id');
    }

    /**
	 * Gets ProviderType associated record.
	 */
    public function providerType()
    {
        return $this->hasOne(\ProviderType::class, 'provider_type_id');
    }
}
