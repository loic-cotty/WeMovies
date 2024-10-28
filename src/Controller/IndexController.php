<?php

namespace App\Controller;

use App\Services\TmdbService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(TmdbService $tmdbService): Response
    {
        return $this->render('index/index.html.twig', [
            'genres' => $tmdbService->getGenres(),
            'TopRatedMovie' => $tmdbService->getTopRatedFilm()
        ]);
    }
}
