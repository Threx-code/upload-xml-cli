<?php

namespace App\Validators;
use App\Validators\ValidatorResponse as SEOMonitorValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RankTrackerValidator
{
    /**
     * @param Request $request
     * @return bool|void
     */
    public static function validateGetRequest(Request $request)
    {
        if(!$request->owner_id || !is_numeric($request->owner_id)){
            return ValidatorResponse::validationErrors("invalid endpoint");
        }

        if(!$request->campaign_id || !is_numeric($request->campaign_id)){
            return ValidatorResponse::validationErrors("invalid endpoint");
        }

        return true;
    }

    /**
     * @param Request $request
     * @return bool|void
     */
    public static function validateSpecificRequest(Request $request)
    {
        if(!$request->list_id || !is_numeric($request->list_id)){
            return ValidatorResponse::validationErrors("invalid endpoint");
        }

        if(!$request->owner_id || !is_numeric($request->owner_id)){
            return ValidatorResponse::validationErrors("invalid endpoint");
        }

        if(!$request->campaign_id || !is_numeric($request->campaign_id)){
            return ValidatorResponse::validationErrors("invalid endpoint");
        }

        return true;
    }

    /**
     * @param Request $request
     * @return bool|void
     */
    public static function validatePostRequest(Request $request){
        $validator = Validator::make(
            $request->all(), [
                'name' => ['required', 'string', 'max:35'],
                'owner_id' => ['required', 'numeric'],
                'campaign_id' => ['required', 'numeric'],
                'keyword_ids' => ['required', 'array'],
            ]
        );

        $keywordsIDs = $request->input('keyword_ids');

        if($keywordsIDs && is_array($keywordsIDs)){
            ValidatorResponse::isArray($keywordsIDs);
            ValidatorResponse::arrayChecker('keyword ids', $keywordsIDs);
            ValidatorResponse::arrayCounter('keyword ids', $keywordsIDs);
        }

        if ($validator->fails()) {
            return ValidatorResponse::validationErrors($validator->errors());
        }
        return true;
    }

    public static function validateAddKeywordToListRequest(Request $request){
        $validator = Validator::make(
            $request->all(), [
                'list_id' => ['required', 'numeric'],
                'keyword_ids' => ['required', 'array'],
            ]
        );

        $keywordsIDs = $request->input('keyword_ids');

        if($keywordsIDs && is_array($keywordsIDs)){
            ValidatorResponse::isArray($keywordsIDs);
            ValidatorResponse::arrayChecker('keyword ids', $keywordsIDs);
            ValidatorResponse::arrayCounter('keyword ids', $keywordsIDs);
        }

        if ($validator->fails()) {
            return ValidatorResponse::validationErrors($validator->errors());
        }
        return true;
    }

    /**
     * @param Request $request
     * @return bool|void
     */
    public static function validateDeleteAllRequest(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'list_id' => ['required', 'numeric'],
                'owner_id' => ['required', 'numeric'],
                'campaign_id' => ['required', 'numeric'],
            ]
        );

        if ($validator->fails()) {
            return ValidatorResponse::validationErrors($validator->errors());
        }
        return true;
    }

    /**
     * @param Request $request
     * @return bool|void
     */
    public static function validateUpdateListNameRequest(Request $request){
        $validator = Validator::make(
            $request->all(),
            [
                'list_id' => ['required', 'numeric'],
                'name' => ['required', 'string'],
            ]
        );

        if ($validator->fails()) {
            return ValidatorResponse::validationErrors($validator->errors());
        }
        return true;
    }
}
