<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(protected ProfileService $profileService)
    {
    }

    public function show_ajax()
    {
        return view('profile.show_ajax', $this->profileService->showData(Auth::user()));
    }

    public function edit_ajax()
    {
        return view('profile.edit_ajax', $this->profileService->editData(Auth::user()));
    }

    public function update_ajax(Request $request)
    {
        return response()->json($this->profileService->update(Auth::user(), $request->all()));
    }
}
