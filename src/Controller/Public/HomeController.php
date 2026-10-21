<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Entity\SmsProvider;
use App\Enum\ProviderType;
use App\Enum\VehicleTypeEnum;
use App\Repository\ReviewRepository;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(VehicleRepository $vehicles, ReviewRepository $reviews): Response
    {
        dd($this->clientIp());
        return $this->render('home/index.html.twig', [
            'pieces' => $vehicles->findPublished(6, VehicleTypeEnum::PIECE->value),
            'reserved' => $vehicles->findReserved(6),
            'featured_vehicles' => $vehicles->findPublished(6, VehicleTypeEnum::CAR->value),
            'featured_good_deals' =>  $vehicles->findGoodDeals(6,VehicleTypeEnum::CAR->value),
            'reviews'           => $reviews->findPublished(6),
        ]);
    }

    #[Route('/robots.txt', name: 'robots', methods: ['GET'])]
    public function robots(): Response
    {
        $body = "User-agent: *\nDisallow: /admin\nDisallow: /reservation/\nSitemap: /sitemap.xml\n";
        return new Response($body, 200, ['Content-Type' => 'text/plain']);
    }

    private function clientIp(array $trustedProxies = []): ?string
    {
        $ip = file_get_contents('https://api.ipify.org');

        echo "Adresse IP publique du serveur : " . $ip;

        return $ip;
    }

    #[Route('/sitemap.xml', name: 'sitemap', methods: ['GET'])]
    public function sitemap(VehicleRepository $vehicles, string $baseUrl): Response
    {
        $urls = [
            '/'          => new \DateTimeImmutable(),
            '/vehicules' => new \DateTimeImmutable(),
            '/avis'      => new \DateTimeImmutable(),
        ];
        foreach ($vehicles->findPublished() as $v) {
            $urls['/vehicule/'.$v->getSlug()] = $v->getUpdatedAt();
        }

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');
        foreach ($urls as $path => $date) {
            $u = $xml->addChild('url');
            $u->addChild('loc', htmlspecialchars(rtrim($baseUrl, '/').$path));
            $u->addChild('lastmod', $date->format('Y-m-d'));
        }
        return new Response($xml->asXML() ?: '', 200, ['Content-Type' => 'application/xml']);
    }

      #[Route('/credit', name: 'credit', methods: ['GET'])]
    public function saveCredit(Request $request, EntityManagerInterface $em){
        
        $provider = new SmsProvider();
        $provider->setCredit('0.00');
        $provider->setEnable(true);
        $provider->setProvider(ProviderType::BREVO);
        $provider->setBaseUrl(null);
        $em->persist($provider);
        $em->flush();

        $provider = new SmsProvider();
        $provider->setCredit('0.00');
        $provider->setEnable(true);
        $provider->setProvider(ProviderType::OVH);
        $provider->setBaseUrl(null);
        $em->persist($provider);
        $em->flush();

        $provider = new SmsProvider();
        $provider->setCredit('0.00');
        $provider->setEnable(true);
        $provider->setProvider(ProviderType::TWILO);
        $provider->setBaseUrl(null);
        $em->persist($provider);
        $em->flush();

        return true;
    }

    
    #[Route('/infos', name: 'infos', methods: ['GET'])]
    public function infos(VehicleRepository $vehicleRepository): Response
    {
        $vehicle = $vehicleRepository->findOneBy(['uuid' => '01a06981-3115-7c00-be02-b2ff341624f3']);

        return $this->render('emails/information_complementaire.html.twig', [
            'vehicle' => $vehicle
        ]);
    }

    #[Route('/fiche', name: 'fiche', methods: ['GET'])]
    public function fiche(VehicleRepository $vehicleRepository): Response
    {
        $vehicle = $vehicleRepository->findOneBy(['uuid' => '01a0104f-a3cd-78ab-a605-a65363205086']);
        return $this->render('emails/fiche_dentretien.html.twig', [
            'vehicle' => $vehicle
        ]);
    }
}
