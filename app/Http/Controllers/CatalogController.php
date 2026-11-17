<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function home(): View
    {
        $meals = Meal::published()->with('outlet')->orderBy('id')->limit(4)->get();

        return view('home', compact('meals'));
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', Rule::in(Meal::CATEGORIES)],
            'outlet' => ['nullable', 'integer', 'exists:outlets,id'],
            'sort' => ['nullable', Rule::in(['recommended', 'price_asc', 'price_desc'])],
            'max_price' => ['nullable', 'integer', 'min:1000', 'max:100000'],
        ]);
        $query = Meal::published()->with('outlet');
        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$filters['q'].'%');
        }
        if ($request->filled('category')) {
            $query->where('category', $filters['category']);
        }
        if ($request->filled('outlet')) {
            $query->where('outlet_id', $filters['outlet']);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $filters['max_price']);
        }
        match ($filters['sort'] ?? 'recommended') {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->orderByDesc('stock'),
        };
        $meals = $query->orderBy('id')->paginate(9)->withQueryString();
        $outlets = Outlet::where('is_active', true)->orderBy('name')->get();

        return view('menu.index', compact('meals', 'outlets'));
    }

    public function show(Meal $meal): View
    {
        abort_unless($meal->is_active, 404);
        $meal->load('outlet');
        $related = Meal::published()->with('outlet')->whereKeyNot($meal->id)->orderBy('price')->limit(3)->get();

        return view('menu.show', compact('meal', 'related'));
    }

    public function outlets(): View
    {
        $outlets = Outlet::where('is_active', true)->withCount(['meals' => fn ($q) => $q->published()])->orderBy('id')->get();

        return view('outlets', compact('outlets'));
    }

    public function about(): View
    {
        return view('about');
    }
}
