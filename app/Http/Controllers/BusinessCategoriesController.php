<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class BusinessCategoriesController extends Controller
{
    public function getAll()
    {
        $categories = DB::table('business_categories')->select('id', 'name', 'status', 'is_deleted', 'created_at', 'updated_at')->get();

        return response()->json($categories);
    }
}
