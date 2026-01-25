<?php

namespace App\Providers;

use App\Models\AppConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class VeloServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (app()->runningInConsole()) return;

        $this->registerTrustProxies();
        $this->registerEmailConfig();
        $this->registerStorageConfig();
        $this->registerRateLimiter();
    }

    private function registerTrustProxies(): void
    {
        $project_id = 1;

        $ttl = config('larabase.cache_ttl');
        $proxies = Cache::remember('app_config.trusted_proxies.' . $project_id, $ttl, function () use ($project_id) {
            return AppConfig::firstWhere('project_id', $project_id)?->get('trusted_proxies');
        });

        if ($proxies) {
            Request::setTrustedProxies(
                proxies: $proxies->toArray(),
                trustedHeaderSet: SymfonyRequest::HEADER_X_FORWARDED_FOR |
                    SymfonyRequest::HEADER_X_FORWARDED_HOST |
                    SymfonyRequest::HEADER_X_FORWARDED_PORT |
                    SymfonyRequest::HEADER_X_FORWARDED_PROTO |
                    SymfonyRequest::HEADER_X_FORWARDED_AWS_ELB
            );
        }
    }

    private function registerEmailConfig(): void
    {
        $project_id = 1;
        $ttl = config('larabase.cache_ttl', 60);

        $config = Cache::remember('email_config.' . $project_id, $ttl, function () use ($project_id) {
            return \App\Models\EmailConfig::firstWhere('project_id', $project_id);
        });

        if ($config) {
            config([
                'mail.mailers.smtp.host' => $config->host,
                'mail.mailers.smtp.port' => $config->port,
                'mail.mailers.smtp.username' => $config->username,
                'mail.mailers.smtp.password' => $config->password,
                'mail.mailers.smtp.encryption' => $config->encryption,
                'mail.from.address' => $config->from_address,
                'mail.from.name' => $config->from_name,
            ]);
        }
    }

    private function registerStorageConfig(): void
    {
        $project_id = 1;
        $ttl = config('larabase.cache_ttl', 60);

        $config = Cache::remember('storage_config.' . $project_id, $ttl, function () use ($project_id) {
            return \App\Models\StorageConfig::firstWhere('project_id', $project_id);
        });

        if ($config) {
            if ($config->provider === 's3') {
                config([
                    'filesystems.disks.s3.endpoint' => $config->endpoint,
                    'filesystems.disks.s3.bucket' => $config->bucket,
                    'filesystems.disks.s3.region' => $config->region,
                    'filesystems.disks.s3.key' => $config->access_key,
                    'filesystems.disks.s3.secret' => $config->secret_key,
                    'filesystems.disks.s3.use_path_style_endpoint' => $config->s3_force_path_styling,
                ]);
            }
        }
    }

    private function registerRateLimiter(): void
    {
        $project_id = 1;
        $ttl = config('larabase.cache_ttl', 60);

        $rateLimit = Cache::remember('app_config.rate_limit.' . $project_id, $ttl, function () use ($project_id) {
            $config = AppConfig::firstWhere('project_id', $project_id);
            return $config && isset($config->rate_limits) ? $config->rate_limits : 120;
        });

        \Illuminate\Support\Facades\RateLimiter::for('dynamic-api', function (Request $request) use ($rateLimit) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute($rateLimit)->by($request->user()?->id ?: $request->ip());
        });
    }
}
