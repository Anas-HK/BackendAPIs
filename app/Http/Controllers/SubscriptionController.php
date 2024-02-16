<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscription;

class SubscriptionController extends BaseController
{
    public function get($id)
    {
        return Subscription::where('id', $id)->where('is_deleted', 0)->first();
    }
    public function getAll()
    {
        return Subscription::where('is_deleted', 0)->get(['name', 'price', 'days', 'tax', 'status']);
    }


    public function search($keyword)
    {
        return Subscription::where('name', 'like', '%' . $keyword . '%')->where('is_deleted', 0)->get();
    }

    public function insert(Request $request)
    {
        $subscription = new Subscription;
        $subscription->name = $request->name;
        $subscription->price = $request->price;
        $subscription->days = $request->days;
        $subscription->tax = $request->tax;
        $subscription->status = $request->status;
        $subscription->is_deleted = $request->is_deleted;
        $subscription->save();

        return $subscription;
    }

    public function update($id, Request $request)
    {
        $subscription = Subscription::where('id', $id)->where('is_deleted', 0)->first();
        $subscription->name = $request->name;
        $subscription->price = $request->price;
        $subscription->days = $request->days;
        $subscription->tax = $request->tax;
        $subscription->status = $request->status;
        $subscription->is_deleted = $request->is_deleted;
        $subscription->save();

        return $subscription;
    }

    public function delete($id)
    {
        $subscription = Subscription::find($id);
        $subscription->is_deleted = 1;
        $subscription->save();

        return response()->json(null, 204);
    }
}
