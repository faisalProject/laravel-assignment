<?php

namespace App\Http\Controllers;

use App\Models\Ingredients;
use App\Models\Recipe;
use App\Models\Tool;
// use Dotenv\Validator;
use Illuminate\Support\Facades\Validator;
use Helper\messageError;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function show_recipes() {
        $recipes = Recipe::with('user')->where('status_resep', 'publish')->get();

        $data = [];
        foreach($recipes as $recipe) {
            array_push($data, [
                'idresep' => $recipe->idresep,
                'judul' => $recipe->judul,
                'gambar' => url($recipe->gambar),
                'nama' => $recipe->user->name
            ]);
        }
        return response()->json($data, 200);
    }

    public function recipe_by_id(Request $request) {
        $validator = Validator::make($request->all(), [
            'idresep' => 'required',
            'email' => 'email'
        ]);

        if ($validator->fails()) {
            return messageError::message($validator->errors()->messages());
        }

        $recipe = Recipe::where('status_resep', 'publish')
                            ->where('idresep', $request->idresep)
                            ->get();

        $tools = Tool::where('resep_idresep', $request->idresep)->get();
        $ingredients = Ingredients::where('resep_idresep', $request->idresep)->get();

        $data = [];
        foreach($recipe as $recipe) {
            array_push($data, [
                'idresep' => $recipe->idresep,
                'judul' => $recipe->judul,
                'gambar' => url($recipe->gambar),
                'cara_pembuatan' => $recipe->cara_pembuatan,
                'video' => $recipe->video,
                'nama' => $recipe->user->name
            ]);
        }

        $recipeData = [
            'recipe' => $data,
            "tools" => $tools,
            "ingredients" => $ingredients
        ];

        \App\Models\RecipeView::create([
            'email' => $request->email,
            'date' => now(),
            'resep_idresep' => $request->idresep
        ]);

        return response()->json($recipeData, 200);
    }

    public function rating(Request $request) {
        $validator = Validator::make($request->all(), [
            'idresep' => 'required',
            'email' => 'required|email',
            'rating' => 'required|in:1,2,3,4,5',
        ]);

        if($validator->fails()) {
            return messageError::message($validator->errors()->messages());
        }

        \App\Models\Rating::create([
            'rating' => $request->rating,
            'review' => $request->review,
            'resep_idresep' => $request->idresep,
            'email_user' => $request->email
        ]);

        return response()->json([
            "data" => [
                'msg' => "rating berhasil disimpan"
            ]
        ]);
    }
}
