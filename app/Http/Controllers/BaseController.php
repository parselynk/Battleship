<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function validateParams($rules, $messages)
    {
        return \Validator::make(
            request()->all(),
            $rules,
            $messages
        );
    }

    public function returnErrorMessageIfNotValid($validate)
    {
        if (!$validate->passes()) {
            return response()->json([
               'data' => [ 'errors' => $validate->errors() ],
               'success' => false
            ], 404);
        }
    }
}
