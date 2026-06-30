<?php

namespace App\Http\Controllers;

use App\Actions\SaveFilmAction;
use App\Http\Requests\CreateFilmRequest;
use App\Http\Requests\FilmIndexRequest;
use App\Http\Requests\UpdateFilmRequest;
use App\Http\Resources\FilmPreviewResource;
use App\Http\Responses\Success;
use App\Jobs\ProcessFilmImportJob;
use App\Models\Film;
use App\Models\Promo;
use App\Queries\FetchFilmsQuery;
use App\Queries\GetFilmWithMetadataQuery;
use App\Queries\GetSimilarFilmsQuery;
use Illuminate\Support\Facades\Cache;

/**
 * @psalm-api
 */
class FilmController extends Controller
{
    /**
     * Возвращает список фильмов с пагинацией.
     *
     * @param  FilmIndexRequest  $request  Запрос из формы.
     * @param  FetchFilmsQuery  $query  Формат запроса.
     * @return Success Формат ответа.
     */
    public function index(
        FilmIndexRequest $request,
        FetchFilmsQuery $query,
    ): Success {
        $params = $request->validated();

        $cacheKey = 'films_list_' . md5(serialize($params));

        $films = Cache::remember($cacheKey, 86400, function () use ($query, $params) {
            return $query->execute($params);
        });

        return new Success(FilmPreviewResource::collection($films));
    }

    /**
     * Добавляет фильм в БД и создаёт фоновую задачу на обновление информации
     * о фильме.
     *
     * @param  CreateFilmRequest  $request  Запрос из формы.
     * @return Success Формат ответа.
     */
    public function store(CreateFilmRequest $request): Success
    {
        $film = Film::create([
            'imdb_id' => $request['imdb_id'],
        ]);

        ProcessFilmImportJob::dispatch($request['imdb_id']);

        return new Success($film->toArray(), 201);
    }

    /**
     * Возвращает информацию о фильме.
     *
     * @param  Film  $film  Фильм.
     * @param  GetFilmWithMetadataQuery  $query  Запрос.
     * @return Success Формат ответа.
     */
    public function show(Film $film, GetFilmWithMetadataQuery $query): Success
    {
        $userId = auth('sanctum')->id();

        $filmResource = $query->execute($film->id, $userId);

        return new Success($filmResource);
    }

    /**
     * Обновляет информацию о фильме.
     *
     * @param  UpdateFilmRequest  $request  Запрос из формы.
     * @param  Film  $film  Фильм.
     * @param  SaveFilmAction  $action  Действие.
     * @return Success Формат ответа.
     */
    public function update(
        UpdateFilmRequest $request,
        Film $film,
        SaveFilmAction $action,
    ): Success {
        $film = $request->save($film, $action);

        $film->load(['actors', 'directors', 'genres']);

        return new Success($film->toArray());
    }

    /**
     * Возвращает 4 похожих фильма.
     *
     * @param  Film  $film  Фильм.
     * @param  GetSimilarFilmsQuery  $query  Запрос.
     * @return Success Формат ответа.
     */
    public function similar(Film $film, GetSimilarFilmsQuery $query): Success
    {
        $films = $query->execute($film);

        return new Success($films);
    }

    /**
     * Возвращает промо фильм.
     *
     * @param  GetFilmWithMetadataQuery  $query  Запрос.
     * @return Success Формат ответа.
     */
    public function promo(GetFilmWithMetadataQuery $query): Success
    {
        $userId = auth('sanctum')->id();

        $cacheKey = "promo_film_user_{$userId}";

        $filmResource = Cache::remember($cacheKey, 86400, function () use ($query, $userId) {
            $promo = Promo::firstOrFail();
            return $query->execute($promo->film_id, $userId);
        });

        return new Success($filmResource);
    }

    /**
     * Устанавливает промо фильм.
     *
     * @param  Film  $film  Фильм.
     * @return Success Формат ответа.
     */
    public function setPromo(Film $film): Success
    {
        Promo::truncate();

        $promo = Promo::create(
            ['film_id' => $film->id],
        );

        return new Success($promo, 201);
    }
}
