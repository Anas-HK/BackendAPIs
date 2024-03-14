<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessSocialMediaLink;
use App\Models\BusinessTiming;
use App\Models\CardInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DataInsertionController extends Controller
{
    public function BusinessId() {

        // Create an instance of the Business model
        $business = new Business();

         $business->logo = null;
         $business->cover = null;
         $business->address = '';
         $business->city_id = 0;
         $business->state_id = 0;
         $business->zipcode = '';
         $business->description = null;
         $business->status = 0;
         $business->is_deleted = 0;
         $business->save();

         $id = $business->id;

         return $id;
    }

    // Now maaz will send me all data including business id which I need to insert in the relevant tables.
    // The tables are 1) business 2) business_social_media_links 3) business_timings
    public function insertData(Request $request)
    {
        // Define validation rules for each set of data
        $validationRules = [
            'business_data' => 'required|array',
            'social_media_data' => 'required|array',
            'timings_data' => 'required|array',
            'card_info_data' => 'required|array',
        ];

        // Perform validation
        $validator = Validator::make($request->all(), $validationRules);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Extract data from the request
        $businessData = $request->input('business_data');
        $socialMediaData = $request->input('social_media_data');
        $timingsData = $request->input('timings_data');
        $cardInfoData = $request->input('card_info_data');

        // Insert data into the respective tables
        $businessId = $this->insertBusiness($businessData);
        $this->insertSocialMediaLinks($businessId, $socialMediaData);
        $this->insertBusinessTimings($businessId, $timingsData);
        $this->insertCardInformation($businessId, $cardInfoData);

        // Return success response
        return response()->json(['message' => 'Data inserted successfully'], 200);
    }

    private function insertBusiness($data)
    {
        // Create an instance of the Business model
        $business = new Business();

        // Fill the business data
        $business->fill($data);

        // Save the business data
        $business->save();

        // Return the inserted business ID
        return $business->id;
    }

    private function insertSocialMediaLinks($businessId, $data)
    {
        // Create an instance of the BusinessSocialMediaLink model
        $socialMediaLink = new BusinessSocialMediaLink();

        // Fill the social media link data
        $socialMediaLink->business_id = $businessId;
        $socialMediaLink->fill($data);

        // Save the social media link data
        $socialMediaLink->save();
    }

    private function insertBusinessTimings($businessId, $data)
    {
        // Create an instance of the BusinessTiming model
        $businessTiming = new BusinessTiming();

        // Fill the timing data
        $businessTiming->business_id = $businessId;
        $businessTiming->fill($data);

        // Save the timing data
        $businessTiming->save();
    }

    private function insertCardInformation($businessId, $data)
    {
        // Create an instance of the CardInformation model
        $cardInformation = new CardInformation();

        // Fill the card information data
        $cardInformation->business_id = $businessId;
        $cardInformation->fill($data);

        // Save the card information data
        $cardInformation->save();
    }
}
