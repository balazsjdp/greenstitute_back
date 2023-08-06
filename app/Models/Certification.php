<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Certification extends Model
{
    use HasFactory;
    
    protected $table = "certifications";

    protected $fillable = [
        'user_id',
        'company_type',
        'connected_companies',
        'last_closed_year_income',
        'enviromental_violation',
        'self_cleaning_procedure',
        'documents',
        'approved'
    ];

    protected $attributes = [
        'documents' => '',
        'approved' => false

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
