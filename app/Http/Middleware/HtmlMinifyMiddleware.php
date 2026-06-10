<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use JShrink\Minifier;
use Throwable;
use voku\helper\HtmlMin;

class HtmlMinifyMiddleware
{
    protected $htmlMin;

    public function __construct()
    {
        $this->htmlMin = new HtmlMin;
        $this->htmlMin->doOptimizeAttributes(true);
        $this->htmlMin->doRemoveComments(true);
        $this->htmlMin->doRemoveWhitespaceAroundTags(true);
        $this->htmlMin->doRemoveSpacesBetweenTags(true);
    }

    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (! ($response instanceof Response)) {
            return $response;
        }

        $contentType = $response->headers->get('Content-Type');
        if (! $contentType || ! str_starts_with($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();

        if (! str_contains($content, '<script') && ! str_contains($content, '<html')) {
            return $response;
        }

        $content = $this->minifyScriptsWithDOM($content);

        $content = $this->htmlMin->minify($content);

        $response->setContent($content);

        return $response;
    }

    private function minifyScriptsWithDOM(string $html)
    {
        $dom = new \DOMDocument;

        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?>'.$html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();

        $scripts = $dom->getElementsByTagName('script');

        foreach ($scripts as $script) {
            $type = $script->getAttribute('type');

            if (in_array($type, ['application/json', 'application/ld+json', 'text/template'])) {
                continue;
            }

            $code = $script->nodeValue;
            $minified = $this->minifyJs($code);
            $script->nodeValue = $minified;
        }

        return $dom->saveHTML();
    }

    private function minifyJs(string $script)
    {
        $script = trim($script);

        if ($script === '') {
            return '';
        }

        try {
            return Minifier::minify($script, ['flaggedComments' => false]);
        } catch (Throwable $e) {
            return $script;
        }
    }
}
