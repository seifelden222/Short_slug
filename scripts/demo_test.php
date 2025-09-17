<?php

// Boot the framework
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create demo user and link
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Jobs\Webhook as WebhookJob;

// create or get demo user
$user = User::firstOrCreate(['email' => 'demo@example.test'], [
    'name' => 'Demo User',
    'password' => Hash::make('password'),
]);

// create demo link
$link = Link::firstOrCreate([
    'slug' => 'demo-sample',
], [
    'user_id' => $user->id,
    'target_url' => 'https://example.com',
    'is_active' => true,
]);

echo "Created link id={$link->id} slug={$link->slug} clicks_count={$link->clicks_count}\n";

// For testing: set a fake webhook target on the link (this will be used by the Webhook job)
$link->target_url = 'https://example.test/webhook';
$link->save();

echo "Set link.target_url to {$link->target_url} (used as webhook target for this test)\n";

// Fake HTTP responses so the job can run without external network
Http::fake([
    '*' => Http::response(['ok' => true], 200)
]);

// Build a request to the redirect endpoint
$request = Request::create('/api/r/' . $link->slug, 'POST', [], [], [], [
    'REMOTE_ADDR' => '127.0.0.1'
]);

// Provide an idempotency_key
$request->request->set('idempotency_key', 'DEMO-CLICK-1');

$response = $kernel->handle($request);

$status = $response->getStatusCode();
$body = (string) $response->getContent();

echo "Response status: $status\n";
echo "Body: $body\n";

$kernel->terminate($request, $response);

// Simulate webhook delivery synchronously (bypass queue worker) to test sending
$job = new WebhookJob($link, 10, $link->clicks_count);
try {
    $job->handle();
    echo "Webhook job executed synchronously (fake HTTP).\n";
} catch (\Exception $e) {
    echo "Webhook job failed: " . $e->getMessage() . "\n";
}

