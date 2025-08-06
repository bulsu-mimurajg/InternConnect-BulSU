<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    /**
     * Create a new request for a student after registration
     */
    public function createRequest($sectionId, $studentNumber)
    {
        try {
            $request = RequestModel::create([
                'stud_num' => $studentNumber,
                'section_id' => $sectionId,
            ]);

            return $request;
        } catch (\Exception $e) {
            return null;
        }
    }
} 