<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Emailer; 
use Validator;

class EmailController extends Controller
{
    // Method to send emails
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|string|email',
            'subject' => 'required',
            'phone' => 'required',
            'message' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $requestData = $request->json()->all();

        $name = $requestData["name"];
        $email = $requestData["email"];
        $sub = $requestData["subject"];
        $tel = $requestData["phone"];
        $message = $requestData["message"];

        $subject = "Greenstitute.hu - Kapcsolatfelvétel";
        $body = "
        Név: $name
        Email cím: $email
        Tárgy: $sub
        Telefonszám: $tel
        Üzenet: $message
        ";

        $senderEmail = 'balazs@balazshorvath.hu';
        $recipient = "dev.balazs.horvath@gmail.com";

        $data = [
            'subject' => $subject,
            'body' => $body,
        ];

        $error = "";

        try {
            Mail::to($recipient)->send(new Emailer($data, $senderEmail));
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return response()->json(['message' => 'Email sent successfully', 'error' => $error]);
    }
}