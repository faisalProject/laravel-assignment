<?php

namespace App\Observers;

use App\Models\Log;
use App\Models\Recipe;

class RecipeObserver
{
    /**
     * Handle the Recipe "created" event.
     *
     * @param  \App\Models\Recipe  $recipe
     * @return void
     */
    public function created(Recipe $recipe)
    {
        Log::create([
            'module' => 'tambah resep',
            'action' => 'tambah resep ' . $recipe->judul . ' dengan id ' . $recipe->id,
            'useraccess' => $recipe->user_email 
        ]);
    }

    /**
     * Handle the Recipe "updated" event.
     *
     * @param  \App\Models\Recipe  $recipe
     * @return void
     */
    public function updated(Recipe $recipe)
    {
        Log::create([
            'module' => 'sunting resep',
            'action' => 'sunting resep menjadi ' .$recipe->judul . ' dengan id ' . $recipe->id,
            'useraccess' => $recipe->user_email
        ]);
    }

    /**
     * Handle the Recipe "deleted" event.
     *
     * @param  \App\Models\Recipe  $recipe
     * @return void
     */
    public function deleted(Recipe $recipe)
    {
        Log::create([
            'module' => 'hapus resep',
            'action' => 'hapus resep' . $recipe->judul . ' dengan id ' . $recipe->id,
            'useraccess' => $recipe->user_email
        ]);
    }

    /**
     * Handle the Recipe "restored" event.
     *
     * @param  \App\Models\Recipe  $recipe
     * @return void
     */
    public function restored(Recipe $recipe)
    {
        //
    }

    /**
     * Handle the Recipe "force deleted" event.
     *
     * @param  \App\Models\Recipe  $recipe
     * @return void
     */
    public function forceDeleted(Recipe $recipe)
    {
        //
    }
}
