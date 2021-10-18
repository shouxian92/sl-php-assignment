<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class ObjectController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Returns all a key-value map of all objects in the db
     */
    public function list() {
        $results = app('db')->select("SELECT * FROM objects");
        return $results;
    }

    /**
     * Adds supports the addition of a kvm in json format only
     */
    public function post(Request $request) {
        if (!$request->isJson()) {
            // return error
            return;
        }
        $body = $request->json()->all();
        app('db')->transaction(function () use($body) {
            foreach($body as $key => $value) {
                app('db')->insert("INSERT INTO objects (obj_key, obj_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE obj_value=?", [$key, $value, $value]);
                app('db')->insert("INSERT INTO objects_log (obj_key, obj_value) VALUES (?, ?)", [$key, $value]);
            }
        });
    }
}
