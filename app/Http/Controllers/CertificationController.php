<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certification;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Validator;

class CertificationController extends Controller
{
    //
        /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['query','removeFiles']]);
    }


    public function certificationRequest(Request $request)
    {
        // Get the users data from the auth token
        $authData = auth()->user();

        $validator = Validator::make($request->all(), [
            'certificationData.company_type' => 'required',
            'certificationData.last_closed_year_income' => 'required',
            'certificationData.enviromental_violation' => 'required',
            'certificationData.self_cleaning_procedure' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

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

    public function approve(Request $request)
    {
        $this->authorize('viewAny', Certification::class);
        $requestData = $request->json()->all();
        $certId = $requestData['certId'];

        $certificationToApprove = Certification::find($certId);
        $certificationToApprove->approved = 1;
        $certificationToApprove->save();

        // Delete uploaded files due to GDPR
        $this->deleteFolderByUserId($certificationToApprove->user_id);

        return response()->json(['message' => $certificationToApprove ]);
    }

    public function discard(Request $request)
    {
        $this->authorize('viewAny', Certification::class);
        $requestData = $request->json()->all();
        $certId = $requestData['certId'];

        $certificationToDelete = Certification::find($certId);

        $userId = $certificationToDelete->user_id;
        // Delete uploaded files due to GDPR
        $this->deleteFolderByUserId($userId);

        // Delete the certification request to let the user try again
        $certificationToDelete->delete();

        return response()->json(['message' => 'Discard']);
    }

    public function removeFiles(Request $request)
    {
        $userId = $request->json()->all()['userId'];

        try{
            $this->deleteFolderByUserId($userId);
            return response()->json(['message' => "Removed"]);
        }
        catch(\Exception $ex)
        {
            return response()->json(['message' => $ex]);
        }       
    }

    private function deleteFolderByUserId($userId)
    {
        $folderPath = 'uploads/' . $userId;

        if (Storage::disk('public')->exists($folderPath)) {
            Storage::disk('public')->deleteDirectory($folderPath);
        }
    }
}
