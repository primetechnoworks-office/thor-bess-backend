<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

/**
 * Blog article bodies are stored as HTML written in CKEditor (admin → Blog posts).
 *
 * - normalize(): what every saved body goes through. Old-style bodies (the website's original
 *   "## heading / - bullet / > quote / !! highlight" text format) are converted to HTML first.
 * - sanitize(): keeps only the markup the editor can produce, so nothing pasted or typed
 *   (scripts, event handlers, javascript: links, inline styles) can reach the website.
 */
class PostBody
{
    /** CSS classes the editor uses and the website styles; any other class is dropped. */
    public const ALLOWED_CLASSES = ['callout', 'lead', 'image', 'table', 'image-style-side', 'image_resized'];

    private static ?HtmlSanitizerInterface $sanitizer = null;

    public static function normalize(?string $body): string
    {
        $body = trim((string) $body);

        return self::sanitize(self::isHtml($body) ? $body : self::fromLegacy($body));
    }

    public static function isHtml(string $body): bool
    {
        return str_starts_with(ltrim($body), '<');
    }

    public static function sanitize(string $html): string
    {
        self::$sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowElement('p', ['class'])
                ->allowElement('h2')->allowElement('h3')->allowElement('h4')
                ->allowElement('strong')->allowElement('b')->allowElement('em')->allowElement('i')
                ->allowElement('u')->allowElement('s')->allowElement('sub')->allowElement('sup')
                ->allowElement('br')->allowElement('hr')->allowElement('code')->allowElement('pre')
                ->allowElement('a', ['href', 'title', 'target'])
                ->allowElement('ul')->allowElement('ol', ['start', 'reversed'])->allowElement('li')
                ->allowElement('blockquote')
                ->allowElement('figure', ['class'])->allowElement('figcaption')
                ->allowElement('img', ['src', 'alt', 'width', 'height'])
                ->allowElement('table')->allowElement('caption')->allowElement('colgroup')->allowElement('col', ['span'])
                ->allowElement('thead')->allowElement('tbody')->allowElement('tfoot')->allowElement('tr')
                ->allowElement('th', ['colspan', 'rowspan', 'scope'])->allowElement('td', ['colspan', 'rowspan'])
                ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
                ->allowRelativeLinks()
                ->allowMediaSchemes(['http', 'https'])
                ->allowRelativeMedias()
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
                ->withAttributeSanitizer(new class implements AttributeSanitizerInterface
                {
                    public function getSupportedElements(): ?array
                    {
                        return null;
                    }

                    public function getSupportedAttributes(): ?array
                    {
                        return ['class', 'target'];
                    }

                    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
                    {
                        if ($attribute === 'target') {
                            return $value === '_blank' ? '_blank' : null;
                        }
                        $kept = array_intersect(preg_split('/\s+/', trim($value)), PostBody::ALLOWED_CLASSES);

                        return $kept ? implode(' ', $kept) : null;
                    }
                })
                ->withMaxInputLength(1_000_000)
        );

        return trim(self::$sanitizer->sanitize($html));
    }

    /**
     * Converts the website's original text format to HTML (same rules as the Next.js ArticleBody):
     * "## " h2 · "### " h3 · "- " bullet · "> " quote · "!! " highlight box · **bold** · blank line = new paragraph.
     */
    public static function fromLegacy(string $text): string
    {
        $inline = fn (string $s) => preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', e($s));
        $html = [];
        $list = [];
        $flush = function () use (&$list, &$html) {
            if ($list) {
                $html[] = '<ul>'.implode('', array_map(fn ($li) => "<li>{$li}</li>", $list)).'</ul>';
                $list = [];
            }
        };

        foreach (preg_split('/\R/', $text) as $raw) {
            $line = trim($raw);
            if (str_starts_with($line, '- ')) {
                $list[] = $inline(substr($line, 2));

                continue;
            }
            $flush();
            if ($line === '') {
                continue;
            }

            $html[] = match (true) {
                str_starts_with($line, '## ') => '<h2>'.e(substr($line, 3)).'</h2>',
                str_starts_with($line, '### ') => '<h3>'.e(substr($line, 4)).'</h3>',
                str_starts_with($line, '> ') => '<blockquote><p>'.$inline(substr($line, 2)).'</p></blockquote>',
                str_starts_with($line, '!! ') => '<p class="callout">'.$inline(substr($line, 3)).'</p>',
                default => '<p>'.$inline($line).'</p>',
            };
        }
        $flush();

        return implode("\n", $html);
    }
}
