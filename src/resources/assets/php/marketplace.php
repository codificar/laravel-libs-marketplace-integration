<?php

use Codificar\MarketplaceIntegration\Repositories\DispatchRepository;

$institutionId = DispatchRepository::getInstitutionIdFromGuard();

?>
window.marketplaceSettings = {
    providerType: '<?= DispatchRepository::getProviderType($institutionId) ?>',
    paymentMode: '<?= DispatchRepository::getPaymentMode($institutionId) ?>',
}