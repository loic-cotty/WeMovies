<?php

namespace App\Services;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class TmdbService {
    private HttpClientInterface $httpClient;

    private array $tmdbConfig;

    const IMG_POSTER_SIZE = 'w154';
    const IMG_BACKDROP_SIZE = 'w1280';

    const GENRE_LIST_URI = 'genre/movie/list';
    const TOP_RATED_URI = 'movie/top_rated';
    const DISCOVERY_MOVIE_URI = 'discover/movie';
    const SEARCH_MOVIE_URI = 'search/movie';
    const FILM_DETAIL_URI = 'movie/';
    const CONFIGURATION_URI = 'configuration';

    /**
     * @param HttpClientInterface $httpClient
     * @param array $tmdbConfig
     */
    public function __construct(HttpClientInterface $httpClient, array $tmdbConfig)
    {
        $this->httpClient = $httpClient;
        $this->tmdbConfig = $tmdbConfig;
    }

    /**
     * @param string $language
     * @return array
     */
    public function getGenres(string $language = 'fr-FR'): array
    {
        $response = $this->requestTmdbApi(self::GENRE_LIST_URI, [
            'language' => $language
        ]);

        return $response['genres'] ?? [];
    }

    /**
     * @param string $language
     * @return array
     */
    public function getTopRatedFilm(string $language = 'fr-FR'): array
    {
        $topRatedFilms = $this->requestTmdbApi(self::TOP_RATED_URI, [
            'language' => $language
        ]);

        return $this->getFilmDetail($topRatedFilms['results'][0]['id']);
    }

    public function getFilmDetail(string $filmId, string $language = 'fr-FR'): array
    {
        $response = $this->requestTmdbApi(self::FILM_DETAIL_URI. $filmId, [
            'language' => $language,
            'append_to_response' => 'videos'
        ]);

        foreach ($response['videos']['results'] as $movie) {
            if ($movie['type'] === 'Trailer') {
                $response['videos'] = $movie;
            }
        }

        return $this->updateImgUrl($response) ?? [];
    }

    /**
     * @param string $genreIds
     * @param string $language
     * @return array
     */
    public function searchMoviesByGenre(string $genreIds, string $language = 'fr-FR'): array
    {
        $query['with_genres'] = $genreIds;
        $query['language'] = $language;
        $response =  $this->requestTmdbApi(self::DISCOVERY_MOVIE_URI, $query);

        return array_map(function ($movie) {
            return $this->updateImgUrl($movie);
        }, $response['results']);
    }

    /**
     * @param string $text
     * @return array
     */
    public function searchByText(string $text): array
    {
        if (empty($text) || strlen($text) <= 3) {
            return [];
        }
        $query['query'] = $text;
        $response = $this->requestTmdbApi(self::SEARCH_MOVIE_URI, $query);

        return $response['results'] ?? [];
    }

    /**
     * @return string
     */
    private function getImgBaseUrl(): string
    {
        try {
            $cache = new FilesystemAdapter();
            return $cache->get('img_base_url', function (ItemInterface $item) {
                $item->expiresAfter(3600 * 24);
                $response = $this->requestTmdbApi(self::CONFIGURATION_URI);
                if (!empty($response)) {
                    return $response['images']['secure_base_url'];
                }
                return '';
            });
        } catch (Throwable $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * @param array $response
     * @return array
     */
    private function updateImgUrl(array $response): array
    {
        if (empty($response)) {
            return $response;
        }
        if (isset($response['backdrop_path'])) {
            $response['backdrop_path'] = sprintf('%s%s%s', $this->getImgBaseUrl(), self::IMG_BACKDROP_SIZE, $response['backdrop_path']);
        }
        if (isset($response['poster_path'])) {
            $response['poster_path'] = sprintf('%s%s%s', $this->getImgBaseUrl(), self::IMG_POSTER_SIZE, $response['poster_path']);
        }
        return $response;
    }

    /**
     * @param string $uri
     * @param array $query
     * @return array
     */
    private function requestTmdbApi(string $uri, array $query = []): array
    {
        try {
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->tmdbConfig['api_token'],
                ],
                'query' => $query
            ];

            $response = $this->httpClient->request('GET', $this->tmdbConfig['endpoint'] . $uri, $options);

            return ($response->getStatusCode() === Response::HTTP_OK) ?
                $response->toArray() : [];

        } catch (Throwable $exception) {
            return [
                "code" => $exception->getCode(),
                "message" => $exception->getMessage()
            ];
        }

    }
}