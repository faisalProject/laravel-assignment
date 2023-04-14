<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Tool;
use App\Models\Ingredients;
use Illuminate\Support\Facades\Validator;
use Helper\messageError;

class UserController extends Controller
{
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
                'nama' => $alat->nama_alat,
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
}
