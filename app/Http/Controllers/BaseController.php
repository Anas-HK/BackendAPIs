<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class BaseController extends Controller
{
    abstract public function get($id);
    abstract public function getAll();
    abstract public function search($keyword);
    abstract public function insert(Request $request);
    abstract public function update($id, Request $request);
    abstract public function delete($id);
}
