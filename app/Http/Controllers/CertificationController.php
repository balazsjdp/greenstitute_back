<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certification;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class CertificationController extends Controller
{
    //
        /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['query']]);
    }


    public function certificationRequest(Request $request)
    {
        // Get the users data from the auth token
        $authData = auth()->user();
        // Get the data sent in the request
        $requestData = $request->json()->all();
        $certData = $requestData["certificationData"];

        // Creating a new instance of Certification
        $certification = new Certification();
        $certification->user_id = $authData->id;
        $certification->company_type = $certData["company_type"];
        $certification->connected_companies = $certData["connected_companies"];
        $certification->last_closed_year_income = $certData["last_closed_year_income"];
        $certification->enviromental_violation = $certData["enviromental_violation"];
        $certification->self_cleaning_procedure = $certData["self_cleaning_procedure"];


        $certification->save();
        
        return response()->json(['message' => $certData ]);
    }

    public function uploadDocuments(Request $request)
    {
         $paths = "";
         // Get the users data from the auth token
         $authData = auth()->user();

         $files = $request->file('files');

         foreach ($files as $file) {
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('uploads/'.$authData->id, $filename, 'public');
            $paths .= $path . ";";
        }

        // Update the certification request with the document paths
        $certRequest = Certification::firstWhere("user_id", $authData->id);
        $certRequest->documents = $paths;
        $certRequest->save();

        return response()->json(['message' => $paths]);
    }

    public function userRequestedCert()
    {
        $user = auth()->user();
        return response()->json(['message' => $user->certificationRequest]);
    }

    public function query(Request $request) {
        $taxNumber = $request->query('taxNumber');
        $company = User::firstWhere("company_tax_number", $taxNumber);

        if($company)
        {
            $company = $company->load("certificationRequest");
            if($company->certificationRequest)
            {
                if($company->certificationRequest->approved == 1)
                {
                    return response()->json(['certified' => true]);
                }
                else{
                    return response()->json(['certified' => false]);
                }
            }

        }else{
            return response()->json(['certified' => false]);
        }

    }

    public function all(Request $request)
    {
        // Only admin user can access this
        $this->authorize('viewAny', Certification::class);

        // Get all certification requests
        $certifications = Certification::whereHas('user', function ($query) {
            $query->where('is_admin', false);
        })->with('user')->get();
        return response()->json(['certifications' => $certifications]);
    }
}
