<?php

namespace App\Http\Controllers;

use App\Models\AdminModel;
use App\Models\ProdiModel;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    protected UserServiceInterface $userService;
    protected AuthServiceInterface $authService;

    public function __construct(UserServiceInterface $userService, AuthServiceInterface $authService)
    {
        $this->userService = $userService;
        $this->authService = $authService;

        // Protect all admin routes with Laravel's built-in auth middleware.
        // This replaces the manual $this->authService->isAuthenticated() guards.
        $this->middleware('auth');
    }

    public function index()
    {
        return view('admin.index', [
            'breadcrumb' => (object) [
                'title' => 'Daftar Admin',
                'list' => ['Home', 'Daftar Admin'],
            ],
            'page' => (object) [
                'title' => 'Admin yang terdaftar dalam sistem',
            ],
            'activeMenu' => 'admin',
            'prodiList' => ProdiModel::all(),
            'admins' => AdminModel::with(['user', 'prodi'])->get(),
        ]);
    }

    public function list(Request $request)
    {
        try {
            return $this->adminDataTable($this->buildAdminQuery($request))->make(true);
        } catch (\Throwable $e) {
            return $this->dataTableErrorResponse($request, $e);
        }
    }

    public function create_ajax(Request $request)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        return view('admin.create_ajax', ['prodiList' => ProdiModel::all()]);
    }

    public function store_ajax(Request $request)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        // Whitelist only the expected fields to prevent mass-assignment via HTTP.
        $result = $this->userService->createAdmin($request->only([
            'username',
            'password',
            'admin_nidn',
            'admin_nama',
            'admin_noHp',
            'prodi_id',
        ]));

        return $this->jsonResponse($result, 201);
    }

    public function show_ajax(string $id)
    {
        $admin = AdminModel::with(['user', 'prodi'])->find($id);

        if (!$admin) {
            abort(404, 'Data admin tidak ditemukan!');
        }

        return view('admin.show_ajax', compact('admin'));
    }

    public function edit_ajax(string $id)
    {
        // Eager-load 'user' and 'prodi' to prevent N+1 queries in the view.
        $admin = AdminModel::with(['user', 'prodi'])->find($id);

        if (!$admin) {
            abort(404, 'Data admin tidak ditemukan!');
        }

        return view('admin.edit_ajax', [
            'admin' => $admin,
            'prodiList' => ProdiModel::all(),
        ]);
    }

    public function update_ajax(Request $request, $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $admin = AdminModel::find($id);
        if (!$admin) {
            return $this->notFoundErrorResponse('Data admin tidak ditemukan!', 'admin', $id);
        }

        // Whitelist only the expected fields to prevent mass-assignment via HTTP.
        $result = $this->userService->updateAdmin($admin->user_id, $request->only([
            'username',
            'password',
            'admin_nidn',
            'admin_nama',
            'admin_noHp',
            'prodi_id',
        ]));

        return $this->jsonResponse($result);
    }

    public function confirm_ajax(string $id)
    {
        $admin = AdminModel::with('user', 'prodi')->find($id);

        if (!$admin) {
            return $this->notFoundErrorResponse('Data admin tidak ditemukan!', 'admin', $id);
        }

        return view('admin.confirm_ajax', compact('admin'));
    }

    public function delete_ajax(Request $request, $id)
    {
        if (!$this->isAjaxRequest($request)) {
            return redirect('/');
        }

        $admin = AdminModel::find($id);
        if (!$admin) {
            return $this->notFoundErrorResponse('Data admin tidak ditemukan!', 'admin', $id);
        }

        $result = $this->userService->deleteUser($admin->user_id);

        return $this->jsonResponse($result, 200);
    }


    protected function buildAdminQuery(Request $request)
    {
        $query = AdminModel::select('m_admin.*')->with(['user', 'prodi']);

        if ($request->has('prodi_id') && $request->prodi_id != '') {
            $query->where('prodi_id', $request->prodi_id);
        }

        if ($request->filled('search.value')) {
            $query->where('admin_nama', 'like', '%' . $request->input('search.value') . '%');
        }

        return $query;
    }

    protected function adminDataTable($query)
    {
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('prodi_kode', fn($admin) => $admin->prodi ? $admin->prodi->prodi_kode : '-')
            ->addColumn('aksi', fn($admin) => $this->adminActionButtons($admin))
            ->rawColumns(['aksi']);
    }

    protected function adminActionButtons($admin): string
    {
        // Escape the admin_id before embedding into HTML attributes to prevent XSS.
        $adminId = e($admin->admin_id);

        $btn  = '<button onclick="modalAction(\'' . url('/admin/' . $adminId . '/show_ajax') . '\')" class="btn btn-outline-info btn-sm" title="Detail"><i class="fas fa-eye"></i></button>';
        $btn .= '<button onclick="modalAction(\'' . url('/admin/' . $adminId . '/edit_ajax') . '\')" class="btn btn-outline-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></button>';
        $btn .= '<button onclick="modalAction(\'' . url('/admin/' . $adminId . '/confirm_ajax') . '\')" class="btn btn-outline-danger btn-sm" title="Hapus"><i class="fas fa-trash"></i></button>';

        return $btn;
    }

    // dataTableErrorResponse(), isAjaxRequest(), and jsonResponse() are all
    // inherited from the HandlesResponses trait via the base Controller class.
    // Do NOT redeclare them here — doing so silently overrides the trait methods
    // and breaks future updates to the shared trait implementation.
}
