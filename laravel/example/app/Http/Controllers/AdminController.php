<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Helper\messageError;
use Illuminate\Support\Facades\Route;
use App\Models\Recipe;
use App\Models\Tool;
use App\Models\Ingredients;
use Illuminate\Support\Facades\DB;


class AdminController extends Controller
{
    public function dashboard() {
        $totalRecipes = Recipe::count();
        $totalPublishRecipes = Recipe::where('status_resep', 'publish')->count();
        $popularRecipe = DB::table('resep')
                            ->select('judul', DB::raw('count(idresep_view) as jumlah'))
                            ->leftJoin('resep_view','resep.idresep','=','resep_view.resep_idresep')
                            ->groupBy('judul')
                            ->orderBy(DB::raw('count(idresep_view)'),'desc')
                            ->limit(10)
                            ->get();
        return response()->json([
            "data" => [
                "msg" => 'dashboard monitoring',
                'totalRecipes' => $totalRecipes,
                'totalPublishRecipes' => $totalPublishRecipes,
                'popularRecipes' => $popularRecipe
            ]
        ], 200);
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:user,email',
            'password' => 'required|min:8',
            'confirmation_password' => 'required|same:password',
            'role' => 'required|in:admin,user',
            'status' => 'required|in:aktif, non-aktif',
            'email_validate' => 'required|email'
        ]);

        if ($validator->fails()) {
            return messageError::message($validator->errors()->messages());
        }
         
        $user = $validator->validated();
        
        User::create($user);

        return response()->json([
            "data" => [
                'msg' => "berhasil login",
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
            ], 200);
    }

    public function show_register() {
        $users = User::where('role', 'user')->get();

        return response()->json([
            "data" => [
                'msg' => "user registrasi",
                'data' => $users
            ] 
        ], 200);
    }

    public function show_register_by_id($id) {
        $user = User::find($id);
        
        return response()->json([
            "data"=> [
                'msg' => "user id: {$id}",
                'data' => $user
            ]
        ], 200);
    }

    public function update_register(Request $request, $id) {
        $user = User::find($id);

        if ($user) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'password' => 'min:8',
                'confirmation_password' => 'same:password',
                'role' => 'required|in:admin,user',
                'status' => 'required|in:aktif,non-aktif',
                'email_validate' => 'required|email'
            ]);

            if ($validator->fails()) {
                return messageError::message($validator->errors()->messages());
            }

            $data = $validator->validated();

            User::where('id', $id)->update($data);

            return response()->json([
                'data' => [
                    "msg" => 'user dengan id: {$id} berhasil diupdate',
                    'name' => $data['name'],
                    'email' => $user['email'],
                    'role' => $data['role']
                ]
            ], 200);
        }

        return response()->json([
            "data" => [
                'msg' => 'user id: {$id}, tidak ditemukan'
            ]
        ], 422);
    }

    public function delete_register($id) {
        $user = User::find($id);

        if($user) {
            $user->delete();

            return response()->json([
                "data" => [
                    'msg' => 'user dengan id {$id}, berhasil dihapus'
                ]
            ], 200);
        }

        return response()->json([
            "data" => [
                'msg' => 'user dengan id {$id}, tidak ditemukan'
            ]
        ], 422);
    }

    public function activation_account($id) {
        $user = User::find($id);

        if($user) {
            User::where('id', $id)->update(['status' => 'aktif']);

            return response()->json([
                "data" => [
                    'msg' => 'user dengan id '. $id .' berhasil diaktifkan'
                ]
            ], 200);
        }

        return response()->json([
            "data" => [
                'msg' => 'user dengan '. $id .' tidak ditemukan'
            ]
        ], 422);
    }

    public function deactivation_account($id) {
        $user = User::find($id);

        if($user) {
            User::where('id', $id)->update(['status' => 'nonaktif']);

            return response()->json([
                "data" => [
                    'msg' => 'user dengan '. $id . ' berhasil dinonaktifkan' 
                ]
            ], 200);
        }

        return response()->json([
            "data" => [
                'msg' => 'user dengan '. $id .' tidak ditemukan'
            ]
        ], 422);
    }

    public function create_recipe(Request $request) {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|max:255',
            'gambar' => 'required|mimes:png,jpg,jpeg|max:2048',
            'cara_pembuatan' => 'required',
            'video' => 'required',
            'user_email' => 'required',
            'bahan' => 'required',
            'alat' => 'required'
        ]);

        if($validator->fails()) {
            return messageError::message($validator->errors()->messages());
        }

        $thumbnail = $request->file('gambar');
        // ubah nama file yang akan dimasukkan ke server
        $filename = now()->timestamp. "_" .$request->gambar->getClientOriginalName();
        $thumbnail->move('uploads', $filename);

        $recipeData = $validator->validated();

        $recipe = Recipe::create([
            'judul' => $recipeData['judul'],
            'gambar' => 'uploads/' .$filename,
            'cara_pembuatan' => $recipeData['cara_pembuatan'],
            'video' => $recipeData['video'],
            'user_email' => $recipeData['user_email'],
            'status_resep' => 'submit'
        ]);

        foreach(json_decode($request->bahan) as $bahan) {
            Ingredients::create([
                'nama' => $bahan->nama,
                'satuan' => $bahan->satuan,
                'banyak' => $bahan->banyak,
                'keterangan' => $bahan->keterangan,
                'resep_idresep' => $recipe->id
            ]);
        }

        foreach(json_decode($request->alat) as $alat) {
            Tool::create([
                'nama_alat' => $alat->nama,
                'keterangan' => $alat->keterangan,
                'resep_idresep' => $recipe->id,
            ]);
        }

        return response()->json([
            "data" => [
                'msg' => 'resep berhasil disimpan',
                'resep' => $recipeData['judul']
            ]
        ], 200);
    }

    public function update_recipe(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|max:255',
            'gambar' => 'required|mimes:png,jpg,jpeg|max:2048',
            'cara_pembuatan' => 'required',
            'video' => 'required',
            'user_email' => 'required',
            'bahan' => 'required',
            'alat' => 'required'
        ]);

        // dd($validator);
        if($validator->fails()) {
            return messageError::message($validator->errors()->messages());
        }

        $thumbnail = $request->file('gambar');
        $filename = now()->timestamp . "-" . $request->gambar->getClientOriginalName();
        $thumbnail->move('uploads', $filename);

        $recipeData = $validator->validated();

        Recipe::where('idresep', $id)->update([
            'judul' => $recipeData['judul'],
            'gambar' => 'uploads/' .$filename,
            'video' => $recipeData['video'],
            'user_email' => $recipeData['user_email'],
            'status_resep' => 'submit'
        ]);

        Ingredients::where('resep_idresep', $id)->delete();
        Tool::where('resep_idresep', $id)->delete();

        foreach(json_decode($request->bahan) as $bahan) {
            Ingredients::create([
                'nama' => $bahan->nama,
                'satuan' => $bahan->satuan,
                'banyak' => $bahan->banyak,
                'keterangan' => $bahan->keterangan,
                'resep_idresep' => $id,
            ]);
        }

        foreach(json_decode($request->alat) as $alat) {
            Tool::create([
                'nama_alat' => $alat->nama,
                // 'satuan' => $alat->satuan,
                // 'banyak' => $alat->banyak,
                'keterangan' => $alat->keterangan,
                'resep_idresep' => $id
            ]);
        }

        return response()->json([
            "data" => [
                "msg" => "resep berhasil disunting",
                "resep" => $recipeData['judul']
            ]
        ], 200);
    }

    public function delete_recipe($id) {
        Tool::where('idalat', $id)->delete();
        Ingredients::where('idbahan', $id)->delete();
        Recipe::where('idresep', $id)->delete();

        return response()->json([
            "data" => [
                "msg" => "resep berhasil dihapus",
                "resep_id" => $id
            ]
        ], 200);
    }

    public function publish_recipe($id) {
        $recipe = Recipe::where('idresep', $id)->get();

        if ($recipe) {
            Recipe::where('idresep', $id)->update(['status_resep' => 'publish']);
            \App\Models\Log::create([
                'module' => 'publish resep',
                'action' => 'publish resep dengan id '. $id,
                'useraccess' => 'administrator'
            ]);

            return response()->json([
                "data" => [
                    'msg' => 'resep dengan id ' . $id . ' berhasil di publish'
                ]
            ], 200);
        }

        return response()->json([
            "data" => [
                'msg' => 'resep dengan id ' . $id . ' tidak ditemukan'
            ]
        ], 422);
    }

    public function unpublish_recipe($id) {
        $recipe = Recipe::where('idresep', $id)->get();

        if ($recipe) {
            Recipe::where('idresep', $id)->update(['status_resep' => 'unpublished']);

            \App\Models\Log::create([
                'module' => 'publish resep',
                'action' => 'unpublish resep dengan id ' . $id,
                'useraccess' => 'administrator'
            ]);

            return response()->json([
                "data" => [
                    'msg' => 'resep dengan id ' . $id . ' berhasil di unpulish'
                ]
            ], 200);
        }

        return response()->json([
            "data" => [
                'msg' => 'resep dengan id ' . $id . ' tidak ditemukan'
            ]
            ], 422);
    }
}
