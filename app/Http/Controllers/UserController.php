<?php

namespace App\Http\Controllers;

use App\Services\ControllerResponseService;
use App\Services\UserManagementService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected UserManagementService $users, protected ControllerResponseService $responses)
    {
    }

    public function index()
    {
        return view('user.index', $this->users->indexData());
    }

    public function list(Request $request)
    {
        return $this->users->dataTable($request);
    }

    public function create_ajax()
    {
        return view('user.create_ajax')->with('level', $this->users->levels());
    }

    public function create_detail_ajax(Request $request)
    {
        $user = $this->users->findUser((int) $request->session()->get('user_id'));
        return view('user.create_detail_ajax', ['user' => $user, 'level_id' => $request->session()->get('level_id')]);
    }

    public function store_ajax(Request $request)
    {
        $result = $this->users->create($request->all());
        return $this->responses->isAjax($request) ? response()->json($result) : redirect('user/create_detail_ajax')->with($result);
    }

    public function show_ajax(string $id)
    {
        return view('user.show_ajax', ['user' => $this->users->findUser((int) $id), 'level' => $this->users->levels()]);
    }

    public function edit_ajax(string $id)
    {
        return view('user.edit_ajax', ['user' => $this->users->findUser((int) $id), 'level' => $this->users->levels()]);
    }

    public function update_ajax(Request $request, $id)
    {
        return $this->responses->isAjax($request) ? response()->json($this->users->update((int) $id, $request->all())) : redirect('/');
    }

    public function confirm_ajax(string $id)
    {
        return view('user.confirm_ajax', ['user' => $this->users->findUser((int) $id)]);
    }

    public function delete_ajax(Request $request, $id)
    {
        return $this->responses->isAjax($request) ? response()->json($this->users->delete((int) $id)) : redirect('/');
    }

    public function import()
    {
        return view('user.import');
    }

    public function import_ajax(Request $request)
    {
        return $this->responses->isAjax($request) ? response()->json($this->users->import($request->file('file_user'))) : redirect('/');
    }
}
