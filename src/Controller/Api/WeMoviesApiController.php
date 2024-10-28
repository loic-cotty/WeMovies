<?php

namespace App\Controller\Api;

use App\Services\TmdbService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class WeMoviesApiController extends AbstractController
{
    private TmdbService $tmdbService;

    public function __construct(TmdbService $tmdbClientService)
    {
        $this->tmdbService = $tmdbClientService;
    }

    #[Route('/genres/{genreIds?}/movies', name: 'api_movies_by_genres', methods: ['GET'])]
    public function getMovieByGenre(?string $genreIds): JsonResponse
    {
        $films = $this->tmdbService->searchMoviesByGenre($genreIds);

        $htmlContent = '';
        foreach ($films as $film) {
            $htmlContent .= $this->renderView('_parts/film.html.twig', [
                'film' => $film
            ]);
        }

        return $this->json($htmlContent);
    }

    #[Route('/search/{text}/movie', name: 'api_movie_by_text', methods: ['GET'])]
    public function searchMovieByText(string $text): JsonResponse
    {
        return $this->json($this->tmdbService->searchByText($text));
    }

    #[Route('/film/{id}/modal', name: 'api_film_modal', methods: ['GET'])]
    public function getFilmModal(int $id): JsonResponse
    {
        return $this->json(
            $this->renderView('_parts/modal-film.html.twig', [
                'film' => $this->tmdbService->getFilmDetail($id)
            ])
        );
    }

    #[Route('/film/{id}/template', name: 'api_film_template', methods: ['GET'])]
    public function getFilmTemplate(?string $id): JsonResponse
    {
        return $this->json(
            $this->renderView('_parts/film.html.twig', [
                'film' => $this->tmdbService->getFilmDetail($id)
            ])
        );
    }
}