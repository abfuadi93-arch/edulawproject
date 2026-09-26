<?php

use App\Http\Middleware\TrackPageVisit;
use App\Models\PageVisit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Defer\DeferredCallbackCollection;
use Illuminate\Support\Facades\Hash;

test('public get requests are tracked as page visits', function () {
    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertCookie('edulaw_visitor_id');

    $visit = PageVisit::query()->first();

    expect($visit)->not->toBeNull()
        ->and($visit->path)->toBe('/')
        ->and($visit->route_name)->toBe('home')
        ->and($visit->method)->toBe('GET')
        ->and($visit->status_code)->toBe(200)
        ->and($visit->visitor_id)->not->toBeEmpty()
        ->and($visit->ip_hash)->not->toBeEmpty();
});

test('non get requests are not tracked', function () {
    $this->post('/kontak', []);

    expect(PageVisit::query()->count())->toBe(0);
});

test('bot user agents are not tracked', function () {
    $this
        ->withHeader('User-Agent', 'Googlebot/2.1')
        ->get('/')
        ->assertOk();

    expect(PageVisit::query()->count())->toBe(0);
});

test('admin dashboard renders traffic widget', function () {
    $user = User::query()->create([
        'name' => 'Traffic Admin',
        'email' => 'traffic-admin@example.test',
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    PageVisit::query()->create([
        'visitor_id' => 'visitor-one',
        'method' => 'GET',
        'path' => '/',
        'full_url' => url('/'),
        'route_name' => 'home',
        'status_code' => 200,
        'visited_at' => now(),
    ]);

    $this
        ->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Traffic Website')
        ->assertSee('Halaman Teratas')
        ->assertSee('Beranda');
});

test('analytics writes happen after the response phase', function () {
    $request = Request::create('/insight', 'GET', server: ['HTTP_USER_AGENT' => 'Mozilla/5.0']);
    $response = app(TrackPageVisit::class)->handle($request, fn () => response('Ready'));

    expect($response->getContent())->toBe('Ready')
        ->and(PageVisit::query()->count())->toBe(0);

    app(DeferredCallbackCollection::class)->invoke();

    expect(PageVisit::query()->count())->toBe(1)
        ->and(PageVisit::query()->first()->path)->toBe('insight');
});

test('preview crawlers do not receive analytics cookies or enqueue analytics writes', function () {
    $this->withHeader('User-Agent', 'WhatsApp/2.0')->get('/')
        ->assertOk()->assertCookieMissing('edulaw_visitor_id');

    expect(PageVisit::query()->count())->toBe(0);
});
