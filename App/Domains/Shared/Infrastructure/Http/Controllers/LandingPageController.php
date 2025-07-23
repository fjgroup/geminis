<?php

namespace App\Domains\Shared\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class LandingPageController extends Controller
{
    /**
     * Load service data from JSON file.
     *
     * @return array|null
     */
    private function loadServiceData(): ?array
    {
        $servicesDataPath = public_path('data/services.json');
        if (!file_exists($servicesDataPath)) {
            Log::error('services.json not found at ' . $servicesDataPath);
            return null;
        }

        $servicesDataJson = file_get_contents($servicesDataPath);
        $servicesData = json_decode($servicesDataJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Error decoding services.json: ' . json_last_error_msg());
            return null;
        }
        return $servicesData;
    }

    /**
     * Display the home landing page.
     *
     * @param Request $request
     * @return InertiaResponse
     */
    public function showHome(Request $request): InertiaResponse
    {
        $servicesData = $this->loadServiceData();

        return Inertia::render('LandingPage', [
            'serviceData' => $servicesData,
            'activeCategorySlug' => null,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            // 'laravelVersion' => Application::VERSION, // From original route, if needed
            // 'phpVersion' => PHP_VERSION, // From original route, if needed
        ]);
    }

    /**
     * Display a specific category on the landing page.
     *
     * @param Request $request
     * @param string $categorySlug
     * @return InertiaResponse
     */
    public function showCategory(Request $request, string $categorySlug): InertiaResponse
    {
        $servicesData = $this->loadServiceData();
        $activeCategory = null;

        if ($servicesData && isset($servicesData['serviceCategories'])) {
            foreach ($servicesData['serviceCategories'] as $category) {
                // Assuming categoryId is used as the slug for now.
                // If a dedicated 'slug' field exists, use that: $category['slug'] === $categorySlug
                if (isset($category['categoryId']) && $category['categoryId'] === $categorySlug) {
                    $activeCategory = $category; // Pass the whole category if needed, or just the slug
                    break;
                }
            }
        }

        // If category not found, activeCategorySlug will correctly be the one from URL,
        // but the frontend might need to handle a case where this slug doesn't match any category data.
        // Or, redirect/abort if categorySlug must be valid:
        // if (!$activeCategory && $categorySlug) {
        //     abort(404, "Service category not found.");
        // }

        return Inertia::render('LandingPage', [
            'serviceData' => $servicesData,
            'activeCategorySlug' => $categorySlug, // Pass the slug from URL
            // 'activeCategory' => $activeCategory, // Optionally pass the full active category object
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }
}
