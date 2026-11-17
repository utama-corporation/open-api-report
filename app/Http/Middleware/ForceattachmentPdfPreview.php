<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceattachmentPdfPreview extends ForceInlinePdfPreview
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = parent::handle($request, $next);

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (stripos($contentType, 'application/pdf') === false) {
            return $response;
        }

        $disposition = (string) $response->headers->get('Content-Disposition', '');
        if ($disposition === '') {
            return $response;
        }

        // Web download endpoints must always download, even if a controller sets inline.
        $response->headers->set(
            'Content-Disposition',
            preg_replace('/^\s*inline\s*;/i', 'attachment;', $disposition, 1) ?? $disposition
        );

        return $response;
    }
}
