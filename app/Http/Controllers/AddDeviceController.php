<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddDeviceController extends Controller
{
    // making an API method for organizations module, but naming it as addDevice, because that what it is responsible for
    public function addDevice(Request $request)
    {
        try {
            // Extract parameters from the request
            $dname = $request->input('dname');
            $location_id = $request->input('location_id');
            $sanctionedLoad = $request->input('sanctionedLoad');
            $node_mac = $request->input('node_mac');
            $currentlimit = $request->input('currentlimit');

            // Your existing logic for adding a device goes here...

            // Example success response
            $responseData = [
                'success' => true,
                'message' => 'Device added successfully.',
                // Other relevant data you want to include in the response
            ];

            // Return the response as JSON
            return response()->json($responseData);
        } catch (\Exception $exception) {
            // Handle exceptions
            $errorResponse = [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
            return response()->json($errorResponse, 500);
        }
    }
}
