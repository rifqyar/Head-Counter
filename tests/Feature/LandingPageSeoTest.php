<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageSeoTest extends TestCase
{
    public function test_landing_page_emits_review_structured_data(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertSee('"@type":"Review"', false);
        $response->assertSee('"reviewBody"', false);
        $response->assertSee('Oria Hotel Jakarta', false);
    }

    public function test_landing_page_emits_faq_structured_data(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('"acceptedAnswer"', false);
    }

    public function test_landing_page_uses_https_canonical_and_hreflang(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://', false)
            ->assertSee('hreflang="en"', false)
            ->assertSee('hreflang="id"', false)
            ->assertSee('hreflang="x-default"', false);
    }

    public function test_landing_page_og_image_has_dimensions_and_secure_url(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('og:image:width', false)
            ->assertSee('og:image:height', false)
            ->assertSee('og:image:secure_url', false);
    }

    public function test_landing_page_includes_robots_and_theme_color_meta(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('name="robots" content="index, follow', false)
            ->assertSee('name="theme-color"', false);
    }

    public function test_sitemap_returns_valid_xml_with_homepage_url(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false)
            ->assertSee('<loc>https://', false)
            ->assertSee('hreflang="en"', false)
            ->assertSee('hreflang="id"', false);
    }
}
