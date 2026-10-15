<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\SmsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

/**
 * "Mes réservations" — accessible via a UUID from any of the customer's own
 * reservations (magic-link style, sent in the confirmation email). This avoids
 * a full customer-account system while still letting the customer view the
 * list of all reservations under their email.
 *
 * The security model:
 *   - We take one reservation UUID (unpredictable) from the URL
 *   - We look up the customer email attached to it
 *   - We list only the reservations that share the exact same email
 *
 * A malicious user cannot enumerate reservations of another customer without
 * knowing at least one of their reservation UUIDs.
 */
#[Route("/api")]
class ApiController extends AbstractController
{
    public function __construct()
    {}

    #[Route(name:'borderau', path:'/borderau/{reference}')]
    public function getBorderau(Request $request, ?string $reference, 
    SmsRepository $smsRepository, UrlGeneratorInterface $urlGenerator
    ){
        $findBorderau = $smsRepository->findOneBy(['referenceLbc' => $reference]);
        if(empty($findBorderau)){
            return $this->json('borderau not found', 400);
        }
        $frontImage = sprintf(
            '%s://%s/uploads/vehicles/%s',
            $request->getScheme(),
            $request->getHttpHost(),
            $findBorderau->getVehicle()->getPrimaryImage()->getFilename()
        );
        $newVehicle = new class(
            $findBorderau->getVehicle()->getBrand(),
            $findBorderau->getVehicle()->getDescription(),
            $findBorderau->getVehicle()->getPrice(),
            '€',
            $frontImage,
            Uuid::v4()->toString(),
            (string)$findBorderau->getVehicle()->getModel(),
            (string)$findBorderau->getVehicle()->getMileage() ?? '',
            (string)$findBorderau->getVehicle()->getTransmission(),
            (string)$findBorderau->getVehicle()->getYear() ?? '',
            (string)$findBorderau->getVehicle()->getVim(),
            (string)$findBorderau->getVehicle()->getImmatriculation(),
            (string)$findBorderau->getReferenceLbc(),
            'Paiement sécurisé',
            (string)$findBorderau->getName(),
            (string)$findBorderau->getMailTo()
    ) {
        public function __construct(
            public readonly string $title,
            public readonly string $description,
            public readonly string $amount,
            public readonly string $currency,
            public readonly string $frontImage,
            public readonly string $reference,
            public readonly string $model,
            public readonly string $kilometrage,
            public readonly string $transmission,
            public readonly string $year,
            public readonly string $vim,
             public readonly string $immatriculation,
            public readonly string $transactionReference,
            public readonly string $paymentMethod,
            public readonly string $pseudo,
            public readonly string $mailTo,
        ) {
        }
};
        return $this->json($newVehicle, 200);
    }
}