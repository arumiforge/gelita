<?php

namespace App\Filters;

use App\Libraries\HtmlMinifier;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Minifies every full HTML page and puts the source watermark on top
 * (HtmlMinifier). JSON, files, PDFs, and HTML fragments pass through as
 * they are; a page may start with comments (the debug toolbar's
 * `DEBUG-VIEW` markers in development). Switched by Config\Gelita::$minifyHtml.
 */
class HtmlMinifyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if (! config('Gelita')->minifyHtml || $response instanceof DownloadResponse) {
            return $response;
        }

        $type = $response->getHeaderLine('Content-Type');
        $body = $response->getBody();

        if (($type !== '' && ! str_starts_with(strtolower($type), 'text/html'))
            || ! is_string($body)
            || preg_match('~^\s*+(?:<!--(?:(?!-->).)*+-->\s*+)*+(?:<!doctype html|<html[\s>])~is', $body) !== 1) {
            return $response;
        }

        return $response->setBody(HtmlMinifier::watermark(HtmlMinifier::minify($body)));
    }
}
