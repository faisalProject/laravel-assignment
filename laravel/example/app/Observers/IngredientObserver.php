<?php

namespace App\Observers;

use App\Models\Log;
use App\Models\Ingredients;

class IngredientObserver
{
    /**
     * Handle the Ingredient "created" event.
     *
     * @param  \App\Models\Ingredient  $ingredient
     * @return void
     */
    public function created(Ingredients $ingredients)
    {
        Log::create([
            'module' => 'tambah bahan',
            "action" => 'tambah bahan untuk id resep ' . $ingredients->resep_idresep . ' dengan bahan ' . $ingredients->nama,
            'useraccess' => '-'
        ]);
    }

    /**
     * Handle the Ingredient "updated" event.
     *
     * @param  \App\Models\Ingredient  $ingredient
     * @return void
     */
    public function updated(Ingredients $ingredients)
    {
        //
    }

    /**
     * Handle the Ingredient "deleted" event.
     *
     * @param  \App\Models\Ingredient  $ingredient
     * @return void
     */
    public function deleted(Ingredients $ingredients)
    {
        Log::create([
            'module' => 'hapus bahan',
            'action' => 'hapus bahan untuk id resep ' . $ingredients->resep_idresep,
            'useraccess' => "-"
        ]);
    }

    /**
     * Handle the Ingredient "restored" event.
     *
     * @param  \App\Models\Ingredient  $ingredient
     * @return void
     */
    public function restored(Ingredients $ingredients)
    {
        //
    }

    /**
     * Handle the Ingredient "force deleted" event.
     *
     * @param  \App\Models\Ingredient  $ingredient
     * @return void
     */
    public function forceDeleted(Ingredients $ingredients)
    {
        //
    }
}
