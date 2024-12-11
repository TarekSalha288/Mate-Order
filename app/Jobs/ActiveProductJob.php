<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ActiveProductJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    private $super_user_id;
    public function __construct($super_user_id)
    {
        $super_user_id = $this->super_user_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $store_id = Store::where('user_id', $this->super_user_id);
        $products = Product::where('store_id', $store_id)->where('amount', 0);
        foreach ($products as $product) {
            $product->update([
                'active' => 0
            ]);
        }
    }
}
