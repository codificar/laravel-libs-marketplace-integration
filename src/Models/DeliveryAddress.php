<?php

namespace Codificar\MarketplaceIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryAddress extends Model
{
    use SoftDeletes;
    
    protected $table = 'delivery_address';
    protected $fillable = [
        'order_id',
        'customer_id',
        'street_name',
        'street_number',
        'formatted_address',
        'neighborhood',
        'complement',
        'postal_code',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'distance'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    /**
     * get formatted address attribute
     * @return string formatted address 
 	 **/
	public function getFormattedAttribute(){
		return sprintf("%s, %s - %s - %s/%s", $this->street_name, $this->street_number, $this->neighborhood, $this->city, $this->state);
	}

    /**
     * Function to parse addres
     * @return array with street_name, neighborhood, zipcode, street_number
     */
    public static function parseAddress($srcAddress){
	
        preg_match(
            "/([A-Za-z_ ]*)(.*),([A-Za-z_ ]*),([A-Za-z_ ]*)([0-9]*)(-([0-9]{4})){0,1}/",
            $srcAddress,
            $matches
        );

        if(!$matches) {
            return  [
                'street_name' 		=> $srcAddress ,
                'neighborhood' 		=> '' ,
                'zipcode' 			=> null ,
                'street_number' 	=> 0
            ];
        }

        list($original, $name, $street, $city, $state, $zipcode) = $matches;

        $number = 0 ;
        $neighborhood = '' ;

        if($street)
            list($number, $neighborhood) = explode(' ', $street);

        $return = [
			'street_name' 		=> $street ,
			'neighborhood' 		=> $neighborhood ,
			'zipcode' 			=> $zipcode ,
			'street_number' 	=> $number
		];

		return $return ;
	}
}
