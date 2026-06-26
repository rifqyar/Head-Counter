<?xml version="1.0" encoding="UTF-8"?>
@php
    $publicUrl = rtrim(config('app.public_url', 'https://hotel.rekayasadigital.com'), '/');
@endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
    <url>
        <loc>{{ $publicUrl }}/</loc>
        <xhtml:link rel="alternate" hreflang="en" href="{{ $publicUrl }}/?lang=en" />
        <xhtml:link rel="alternate" hreflang="id" href="{{ $publicUrl }}/?lang=id" />
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ $publicUrl }}/" />
        <lastmod>{{ date('c') }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ $publicUrl }}/login</loc>
        <lastmod>{{ date('c') }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.3</priority>
    </url>
</urlset>
