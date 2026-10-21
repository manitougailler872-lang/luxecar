<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Entity\Reservation;
use App\Entity\Review;
use App\Enum\FuelType;
use App\Form\ReservationType;
use App\Enum\VehicleTypeEnum;
use App\Form\ReviewType;
use App\Repository\VehicleRepository;
use App\Service\Co2GradeCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class VehicleController extends AbstractController
{
    #[Route('/vehicules', name: 'vehicle_list', methods: ['GET'])]
    public function list(Request $request, VehicleRepository $repo): Response
    {
        $q          = trim((string) $request->query->get('q', ''));
        $brand      = trim((string) $request->query->get('brand', ''));
        $fuel       = trim((string) $request->query->get('fuel', ''));
        $sort       = (string) $request->query->get('sort', 'newest');
        $year       = $request->query->get('year');
        $priceMin   = $request->query->get('price_min');
        $priceMax   = $request->query->get('price_max');
        $categories = (array) $request->query->all('categories');
        $transmission =  trim((string) $request->query->get('transmission', ''));
        $category = trim((string) $request->query->get('category', ''));

        $qb = $repo->createQueryBuilder('v')->andWhere('v.published = true');
        $qb = $repo->createQueryBuilder('v')->andWhere('v.vehicleType = :vehicleType')->setParameter('vehicleType', VehicleTypeEnum::CAR->value);

        if ($q !== '') {
            $qb->andWhere('LOWER(v.brand) LIKE :q OR LOWER(v.model) LIKE :q OR LOWER(v.version) LIKE :q')
               ->setParameter('q', '%'.strtolower($q).'%');
        }
        if ($brand !== '') {
            $qb->andWhere('v.brand = :brand')->setParameter('brand', $brand);
        }
        if ($transmission !== '') {
            $qb->andWhere('v.transmission = :transmission')->setParameter('transmission', $transmission);
        }
        if ($category !== '') {
            $qb->andWhere('v.category = :category')->setParameter('category', $category);
        }
        if ($year !== null && $year !== '' && (int) $year > 1900) {
            $qb->andWhere('v.year = :year')->setParameter('year', (int) $year);
        }
        if ($priceMin !== null && $priceMin !== '') {
            $qb->andWhere('v.price >= :pmin')->setParameter('pmin', (float) $priceMin);
        }
        if ($priceMax !== null && $priceMax !== '') {
            $qb->andWhere('v.price <= :pmax')->setParameter('pmax', (float) $priceMax);
        }
        if ($fuel !== '' && ($fuelEnum = FuelType::tryFrom($fuel)) !== null) {
            $qb->andWhere('v.fuel = :fuel')->setParameter('fuel', $fuelEnum);
        }
        if (!empty($categories)) {
            $qb->andWhere('v.category IN (:cats)')->setParameter('cats', $categories);
        }
        match ($sort) {
            'price_asc'  => $qb->orderBy('v.price', 'ASC'),
            'price_desc' => $qb->orderBy('v.price', 'DESC'),
            'mileage'    => $qb->orderBy('v.mileage', 'ASC'),
            'rating'     => $qb->orderBy('v.rating', 'DESC'),
            default      => $qb->orderBy('v.createdAt', 'DESC'),
        };
        return $this->render('vehicle/list.html.twig', [
            'vehicles'   => $qb->getQuery()->getResult(),
            'q'  => $q,
            'brand'      => $brand,
            'transmission' => $transmission,
            'category' => $category,
            'fuel'       => $fuel,
            'sort'       => $sort,
            'categoriesList' => [
                'Citadine' => 'Citadine',
                'Berline' => 'Berline',
                'SUV' => 'SUV',
                '4x4' => '4x4',
                'Utilitaire' => 'Utilitaire',
                'Break' => 'Break',
                'Camping-Car' => 'Camping-Car',
                'Coupé' => 'Coupé',
                'Fourgon' => 'Fourgon',
                'Pick-Up' => 'Pick-Up',
                'Sport' => 'Sport',
                'Van' => 'Van',
            ],
            'year'       => $year,
            'price_min'  => $priceMin,
            'price_max'  => $priceMax,
            'categories' => $categories,
        ]);
    }

    #[Route('/track', name: 'track_my_reservation', methods: ['GET'])]
    public function trackMyReservation(Request $request, VehicleRepository $repository): Response{
        return $this->render('vehicle/track.html.twig', []);
    }

    #[Route('/pieces', name: 'piece_list', methods: ['GET'])]
    public function pieceList(Request $request, VehicleRepository $repo): Response
    {
        $q          = trim((string) $request->query->get('q', ''));
        $sort       = (string) $request->query->get('sort', 'newest');
        $priceMin   = $request->query->get('price_min');
        $priceMax   = $request->query->get('price_max');

        $qb = $repo->createQueryBuilder('v')->andWhere('v.published = true');
        $qb->andWhere('v.vehicleType =:vehicleType')->setParameter('vehicleType', VehicleTypeEnum::PIECE->value);

        if ($q !== '') {
            $qb->andWhere('LOWER(v.brand) LIKE :q')
                ->setParameter('q', '%'.strtolower($q).'%');
        }
        if ($priceMin !== null && $priceMin !== '') {
            $qb->andWhere('v.price >= :pmin')->setParameter('pmin', (float) $priceMin);
        }
        if ($priceMax !== null && $priceMax !== '') {
            $qb->andWhere('v.price <= :pmax')->setParameter('pmax', (float) $priceMax);
        }

        match ($sort) {
            'price_asc'  => $qb->orderBy('v.price', 'ASC'),
            'price_desc' => $qb->orderBy('v.price', 'DESC'),
            'mileage'    => $qb->orderBy('v.mileage', 'ASC'),
            'rating'     => $qb->orderBy('v.rating', 'DESC'),
            default      => $qb->orderBy('v.createdAt', 'DESC'),
        };

        return $this->render('vehicle/pieces.html.twig', [
            'vehicles'   => $qb->getQuery()->getResult(),
            'q'          => $q,
            'sort'       => $sort,
            'price_min'  => $priceMin,
            'price_max'  => $priceMax,
        ]);
    }

    #[Route('/vehicule/{slug}', name: 'vehicle_detail', requirements: ['slug' => '[a-z0-9-]+'], methods: ['GET'])]
    public function detail(string $slug, VehicleRepository $repo, Co2GradeCalculator $calculator): Response
    {
        $vehicle = $repo->findOnePublishedBySlug($slug);
        if ($vehicle === null) {
            throw new NotFoundHttpException('Véhicule introuvable.');
        }

        $reservationForm = $this->createForm(ReservationType::class, new Reservation());
        $reviewForm      = $this->createForm(ReviewType::class, new Review());

        return $this->render('vehicle/detail.html.twig', [
            'vehicle'          => $vehicle,
            'form'             => $reservationForm,
            'review_form'      => $reviewForm,
            'co2Grade' => $calculator->getGrade(100)
        ]);
    }

    
    #[Route('/rapport/{slug}', name: 'vehicle_rapport', requirements: ['slug' => '[a-z0-9-]+'], methods: ['GET'])]
    public function rapport(string $slug, VehicleRepository $repo, Co2GradeCalculator $calculator): Response
    {
        $vehicle = $repo->findOnePublishedBySlug($slug);
        if ($vehicle === null) {
            throw new NotFoundHttpException('Véhicule introuvable.');
        }
        return $this->render('emails/fiche_dentretien.html.twig', [
            'vehicle'  => $vehicle
        ]);
    }

    #[Route('/pv/{slug}', name: 'vehicle_proces_verbale', requirements: ['slug' => '[a-z0-9-]+'], methods: ['GET'])]
    public function procesVerbal(string $slug, VehicleRepository $repo, Co2GradeCalculator $calculator): Response
    {
        $vehicle = $repo->findOnePublishedBySlug($slug);
        if ($vehicle === null) {
            throw new NotFoundHttpException('Véhicule introuvable.');
        }
        return $this->render('emails/ct.html.twig', [
            'vehicle'  => $vehicle
        ]);
    }

    #[Route('/photos-supplementaires/{slug}', name: 'vehicle_photo_supplementaire', requirements: ['slug' => '[a-z0-9-]+'], methods: ['GET'])]
    public function photoSup(string $slug, VehicleRepository $repo, Co2GradeCalculator $calculator): Response
    {
        $vehicle = $repo->findOnePublishedBySlug($slug);
        if ($vehicle === null) {
            throw new NotFoundHttpException('Véhicule introuvable.');
        }
        return $this->render('vehicle/photo.html.twig', [
            'vehicle'  => $vehicle
        ]);
    }
}
