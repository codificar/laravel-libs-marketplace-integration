<?php

use Codificar\MarketplaceIntegration\Repositories\DispatchRepository;

$institutionId = DispatchRepository::getInstitutionIdFromGuard();

?>
window.marketplaceSettings = {
    providerType: '<?= DispatchRepository::getProviderType($institutionId) ?>',
    paymentMode: '<?= DispatchRepository::getPaymentMode($institutionId) ?>',
    institutionId: '<?= $institutionId ?>',
    autocompleteUrl: '<?= URL::Route('corpAutocompleteUrl') ?>',
    geocodeUrl: '<?= URL::Route('corpGeocodeUrl') ?>',
    placeDetailUrl: '<?= URL::Route('adminGeocodeGetPlaceDetail') ?>',
    placesProvider: '<?= \Settings::getPlacesProvider() ?>',
    mapsProvider: '<?= \Settings::getMapProvider() ?>',
    googleMapsKey: '<?= \Settings::getGoogleMapsApiKey() ?>',
}