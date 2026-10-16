<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeSubdirectoryUrl
{
    /**
     * Ensure all internal redirects preserve the subdirectory prefix configured in APP_URL
     * (e.g. https://ligor.online/asset) when running behind IIS or reverse proxies.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appUrl = rtrim((string) config('app.url', ''), '/');
        $appBasePath = rtrim((string) parse_url($appUrl, PHP_URL_PATH), '/');

        if ($appBasePath !== '' && $appBasePath !== '/' && $request->hasSession()) {
            $session = $request->session();

            if ($prev = $session->previousUrl()) {
                $session->setPreviousUrl($this->normalizeUrl($prev, $appUrl, $appBasePath, $request));
            }

            if ($intended = $session->get('url.intended')) {
                $session->put('url.intended', $this->normalizeUrl($intended, $appUrl, $appBasePath, $request));
            }
        }

        $response = $next($request);

        if ($response instanceof RedirectResponse && $appBasePath !== '' && $appBasePath !== '/') {
            $targetUrl = $response->getTargetUrl();
            $normalized = $this->normalizeUrl($targetUrl, $appUrl, $appBasePath, $request);
            if ($normalized !== $targetUrl) {
                $response->setTargetUrl($normalized);
            }
        }

        return $response;
    }

    protected function normalizeUrl(string $targetUrl, string $appUrl, string $appBasePath, Request $request): string
    {
        $appHost = parse_url($appUrl, PHP_URL_HOST);
        $targetHost = parse_url($targetUrl, PHP_URL_HOST);
        $targetPath = parse_url($targetUrl, PHP_URL_PATH) ?? '/';
        $targetQuery = parse_url($targetUrl, PHP_URL_QUERY);
        $targetFragment = parse_url($targetUrl, PHP_URL_FRAGMENT);

        // Only rewrite internal URLs (same host as APP_URL, current request host, localhost, or relative path)
        $isInternal = $targetHost === null
            || $targetHost === $appHost
            || $targetHost === $request->getHost()
            || in_array($targetHost, ['localhost', '127.0.0.1'], true);

        if (!$isInternal) {
            return $targetUrl;
        }

        // Check if path already starts with the base path (e.g. /asset or /asset/...)
        if ($targetPath === $appBasePath || str_starts_with($targetPath, $appBasePath . '/')) {
            // Still ensure scheme/host match APP_URL if target was localhost or http
            $relativeAfterBase = substr($targetPath, strlen($appBasePath));
            $rebuilt = $appUrl . ($relativeAfterBase !== '' ? $relativeAfterBase : '');
        } else {
            $cleanPath = ltrim($targetPath, '/');
            $rebuilt = $cleanPath !== '' ? ($appUrl . '/' . $cleanPath) : $appUrl;
        }

        if ($targetQuery) {
            $rebuilt .= '?' . $targetQuery;
        }
        if ($targetFragment) {
            $rebuilt .= '#' . $targetFragment;
        }

        return $rebuilt;
    }
}
