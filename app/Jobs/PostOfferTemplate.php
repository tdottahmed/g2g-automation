<?php

namespace App\Jobs;

use App\Models\OfferTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class PostOfferTemplate implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */

    protected OfferTemplate $template;

    public function __construct(OfferTemplate $template)
    {
        $this->template = $template;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Call Node.js API with template data
            $response = Http::post(env('NODE_API_URL') . '/create-offer', [
                'title' => $this->template->title,
                'description' => $this->template->description,
                'th_level' => $this->template->th_level,
                'king_level' => $this->template->king_level,
                'queen_level' => $this->template->queen_level,
                'warden_level' => $this->template->warden_level,
                'champion_level' => $this->template->champion_level,
                'price' => $this->template->price,
                'currency' => $this->template->currency,
                'medias' => $this->template->medias,
                'delivery_method' => $this->template->delivery_method,
            ]);

            // Optional: store last_posted_at or response status if needed
        } catch (\Throwable $e) {
            logger()->error('Offer post failed for template ' . $this->template->id . ': ' . $e->getMessage());
        }
    }
}
