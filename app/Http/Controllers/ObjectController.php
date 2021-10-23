<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\BadRequestException;
use App\Repositories\ObjectRepository;
use App\Http\Requests\ObjectRequest;

class ObjectController extends Controller
{
    protected $objectRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ObjectRepository $objectRepository)
    {
        $this->objectRepository = $objectRepository;
    }

    /**
     * Returns a key-value map of all objects in repository
     */
    public function list() {
        $objects = $this->objectRepository->list();
        $results = array();
        foreach ($objects as $obj) {
            $results[$obj->key] = $obj->value;
        }
        return response()->json($results);
    }

    /**
     * Returns the value of the given key in the path
     */
    public function get(Request $request, $key) {
        $objRequest = new ObjectRequest();
        $objRequest->key = $key;
        $ts = $request->get("timestamp");
        if ($ts) {
            $objRequest->timestamp = intval($ts);
        }

        $obj = $this->objectRepository->get($objRequest);
        return response()->json($obj->value);
    }

    /**
     * Add supports the addition of a kvm in json format only
     */
    public function post(Request $request) {
        if (!$request->isJson()) {
            throw new BadRequestException("Malformed JSON payload received.");
        }
        $body = $request->json()->all();
        $ts = $this->objectRepository->create($body);
        return response()->json(array("timestamp" => $ts));
    }
}
