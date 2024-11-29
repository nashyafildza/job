<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::query()
            ->where('is_active', true)
            ->with('tags')
            ->latest();

        if ($request->has('s')) {
            $searchQuery = trim($request->get('s'));

            $query->where(function (Builder $builder) use ($searchQuery) {
                $builder
                    ->orWhere('title', 'like', "%{$searchQuery}%")
                    ->orWhere('company', 'like', "%{$searchQuery}%")
                    ->orWhere('location', 'like', "%{$searchQuery}%");
            });
        }

        if ($request->has('tag')) {
            $tag = $request->get('tag');
            $query->whereHas('tags', function (Builder $builder) use ($tag) {
                $builder->where('slug', $tag);
            });
        }

        $listings = $query->get();

        $tags = Tag::orderBy('name')
            ->get();

        return view('listings.index', compact('listings', 'tags'));
    }

    public function show(Listing $listing, Request $request)
    {
        return view('listings.show', compact('listing'));
    }

    public function apply(Listing $listing, Request $request)
    {
        $listing->clicks()
            ->create([
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);

        return redirect()->to($listing->apply_link);
    }

    public function create()
    {
        return view('listings.create');
    }

    public function store(Request $request)
    {
        // Validasi input tanpa payment_method_id
        $validationArray = [
            'title' => 'required',
            'company' => 'required',
            'location' => 'required',
            'apply_link' => 'required|url',
            'content' => 'required',
            'job_type' => 'required|in:Full-Time,Internship,Freelance,Part-Time',
            'work_policy' => 'required|in:On-Site,Remote,Hybrid',
            'salary_from' => 'nullable|string',
            'salary_to' => 'nullable|string',
        ];

        if (!Auth::check()) {
            $validationArray = array_merge($validationArray, [
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed|min:5',
                'name' => 'required'
            ]);
        }

        $request->validate($validationArray);

        // Cek apakah ada user yang login, jika tidak buat user baru
        $user = Auth::user();
        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            Auth::login($user); // Login user baru
        }

        // Proses pembuatan listing tanpa pembayaran
        try {
            $md = new \ParsedownExtra();

            $listing = $user->listings()->create([
                'title' => $request->title,
                'slug' => Str::slug($request->title) . '-' . rand(1111, 9999),
                'company' => $request->company,
                'location' => $request->location,
                'job_type' => $request->job_type,
                'work_policy' => $request->work_policy,
                'salary_from' => $request->salary_from,
                'salary_to' => $request->salary_to,
                'apply_link' => $request->apply_link,
                'content' => $md->text($request->input('content')),
                'is_highlighted' => $request->filled('is_highlighted'),
                'is_active' => true
            ]);

            foreach (explode(',', $request->tags) as $requestTag) {
                $tag = Tag::firstOrCreate(
                    ['slug' => Str::slug(trim($requestTag))],
                    ['name' => ucwords(trim($requestTag))]
                );
                $tag->listings()->attach($listing->id);
            }

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
