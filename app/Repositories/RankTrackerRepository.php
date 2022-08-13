<?php

namespace App\Repositories;

use App\Contracts\RankTrackerInterface;
use App\Services\RankTrackerService;
use Illuminate\Support\Facades\DB;
use App\Models\{
    RankTrackerList,
    KeywordList,
    ListAggregateAndStat,
};

use \Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use  App\Validators\RepositoryValidator;

class RankTrackerRepository implements RankTrackerInterface
{

    /**
     * @param $request
     * @return JsonResponse
     */
    public static function getRankKeyword($request): JsonResponse
    {
        return RankTrackerService::allRankKeywords($request);
    }


    /**
     * @param $request
     * @return mixed
     */
    public static function createRankKeyword($request): mixed
    {
        return RankTrackerService::newRankKeywordCreation($request);
    }



    public static function updateRankList($request): JsonResponse
    {
        return RankTrackerService::updateRankList($request);
    }


    /**
     * @param $request
     * @return JsonResponse
     */
    public static function deleteRankKeyword($request): JsonResponse
    {
        return RankTrackerService::deleteSpecificRankKeyword($request);

    }

    /**
     * @param $request
     * @return JsonResponse
     */
    public static function deleteAllRankKeyword($request): JsonResponse
    {
        return RankTrackerService::deleteAllRankKeyword($request);
    }

    public static function addKeywordToList($request)
    {
        return RankTrackerService::addKeywordToList($request);
    }
}
