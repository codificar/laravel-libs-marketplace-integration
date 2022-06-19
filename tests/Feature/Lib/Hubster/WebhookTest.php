<?php

namespace Codificar\MarketplaceIntegration\Test\Feature\Lib\Hubster;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Codificar\MarketplaceIntegration\Test\TestCase;
use Codificar\MarketplaceIntegration\Lib\HubsterLib;


class WebhookTest extends TestCase
{
    protected $clientId = "2a8bdb0b-bbe0-454d-8514-525eb4adf8de";
    protected $clientSecret = "wz4k8m9egdt18z23gbkg66deew9kj3doha3xq5ph15qh0pv37tkvfwjv7xmfe374jaszts5z1g0f7kp617ftaiwuxwqzy7brl7v";

    public function setUp(): void
    {
        parent::setUp();
    }    

    /** @test */
    public function test_webhook()
    {       

        $body = '{
            "eventId": "d6703cc8-9e79-415d-ac03-a4dc7f6ab43c",
            "eventTime": "2007-12-03T10:15:30+01:00",
            "eventType": "orders.new_order",
            "metadata": {
              "storeId": "ckdss-store-id",
              "applicationId": "ad4ff59d-04c0-4c7d-8ca3-e3a673f8443d",
              "resourceId": "resource-id-if-needed",
              "payload": {
                "externalIdentifiers": {
                  "id": "69f60a06-c335-46d9-b5a1-97f1a211c514",
                  "friendlyId": "ABCDE",
                  "source": "ubereats"
                },
                "currencyCode": "EUR",
                "status": "NEW_ORDER",
                "items": [
                  {
                    "quantity": 1,
                    "skuPrice": 5.9,
                    "id": "33e0418f-3d56-4360-ba03-18fc5f8844a3",
                    "name": "Juicy Cheeseburger",
                    "note": "Please cook to well done!",
                    "categoryId": "303de078-870d-4349-928b-946869d4d69b",
                    "categoryName": "Burgers",
                    "price": 5.9,
                    "modifiers": [
                      {
                        "quantity": 1,
                        "skuPrice": 1,
                        "id": "d7a21692-9195-43aa-a58f-5395bba8a804",
                        "name": "Avocado",
                        "price": 1,
                        "groupName": "Add ons",
                        "groupId": "fb52b138-7ac4-42c1-bfd8-664d57113a41",
                        "modifiers": [
                          {}
                        ]
                      }
                    ]
                  }
                ],
                "orderedAt": "2007-12-03T10:15:30+01:00",
                "customer": {
                  "name": "Jane Doe",
                  "phone": "+1-555-555-5555",
                  "phoneCode": "111 11 111",
                  "email": "email@email.com",
                  "personalIdentifiers": {
                    "taxIdentificationNumber": 1234567890
                  }
                },
                "customerNote": "Please include extra napkins!",
                "deliveryInfo": {
                  "courier": {
                    "name": "Jane Doe",
                    "phone": "+1-555-555-5555",
                    "phoneCode": "111 11 111",
                    "email": "email@email.com",
                    "personalIdentifiers": {
                      "taxIdentificationNumber": 1234567890
                    }
                  },
                  "destination": {
                    "fullAddress": "123 Sample Street Ste 100, San Francisco, CA 94103",
                    "postalCode": "20500",
                    "city": "Washington",
                    "state": "DC",
                    "countryCode": "US",
                    "addressLines": [
                      "1600 Pennsylvania Avenue NW"
                    ],
                    "location": {
                      "latitude": 38.8977,
                      "longitude": 77.0365
                    },
                    "linesOfAddress": [
                      "1600 Pennsylvania Avenue NW"
                    ]
                  },
                  "licensePlate": "ABC 123",
                  "makeModel": "Honda CR-V",
                  "lastKnownLocation": {
                    "latitude": 38.8977,
                    "longitude": 77.0365
                  },
                  "note": "Gate code 123"
                },
                "orderTotal": {
                  "subtotal": 11.97,
                  "claimedSubtotal": 0,
                  "discount": 1,
                  "tax": 1.1,
                  "tip": 2,
                  "deliveryFee": 5,
                  "total": 19.07,
                  "couponCode": "VWXYZ98765"
                },
                "orderTotalV2": {
                  "customerTotal": {
                    "foodSales": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "feeForRestaurantProvidedDelivery": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "restaurantFundedDiscount": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "tipForRestaurant": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "adjustments": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "packingFee": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "bagFee": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "serviceProviderDiscount": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "tipForServiceProviderCourier": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "feeForServiceProviderDelivery": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "smallOrderFee": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "serviceFee": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "otherFee": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "couponCodes": [
                      "10OFF"
                    ]
                  },
                  "customerPayment": {
                    "customerPaymentDue": 1,
                    "customerPrepayment": 1,
                    "customerAmountToReturn": 1,
                    "paymentDueToRestaurant": 1
                  },
                  "serviceProviderCharge": {
                    "salesTaxWithheld": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "commission": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "processingFee": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "deliveryFeeForRestaurant": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "chargesAdjustments": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    },
                    "otherFees": {
                      "breakdown": [
                        {
                          "subType": "VALUE",
                          "name": "sales tax.",
                          "value": 3.4
                        }
                      ]
                    }
                  },
                  "payout": {
                    "payoutFromServiceProvider": 1,
                    "payoutFrom3rdParty": 1,
                    "cashPayout": 1
                  }
                },
                "customerPayments": [
                  {
                    "value": 2,
                    "processingStatus": "COLLECTABLE",
                    "paymentMethod": "CASH"
                  }
                ],
                "fulfillmentInfo": {
                  "pickupTime": "2007-12-03T10:15:30+01:00",
                  "deliveryTime": "2007-12-03T10:15:30+01:00",
                  "fulfillmentMode": "DELIVERY",
                  "schedulingType": "ASAP",
                  "courierStatus": "COURIER_ASSIGNED"
                }
              },
              "resourceHref": "resource-href-id-if-needed"
            }
          }';
        

        $response = $this->postJson('/libs/marketplace-integration/hubster/webhook', json_decode($body, true))->assertStatus(200);

        dd($response);
    }

   
}
