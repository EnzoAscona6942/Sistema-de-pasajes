<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TripWebController extends Controller
{
    public function home(): Response
    {
        // Mantenemos las locations aquí porque se necesitan instantáneamente para el buscador
        $locations = Location::select('id', 'name', 'city', 'province')
            ->orderBy('city')
            ->get()
            ->map(fn($loc) => [
                'id' => $loc->id,
                'title' => "{$loc->city} ({$loc->name})",
                'value' => $loc->id
            ]);

        return Inertia::render('Home', [
            'locations' => $locations
        ]);
    }

    // Solo renderiza el esqueleto, Vue buscará los datos
    public function index(Request $request): Response
    {
        return Inertia::render('Trips/Index', [
            // Pasamos los filtros query string al frontend para que Vue haga la llamada a la API
            'filters' => $request->all() 
        ]);
    }

    // Solo renderiza el esqueleto, Vue buscará los asientos
    public function show(Request $request, string $id): Response
{
    // Recibimos "passengers" del query string, por defecto 1
    $passengers = $request->query('passengers', 1); 

    return Inertia::render('Trips/Show', [
        'tripId' => $id,
        'passengers' => (int) $passengers // Pasamos el dato a la vista
    ]);
}
}