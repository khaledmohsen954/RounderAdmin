<?php

namespace App\Services;

use DOMDocument;
use DOMNode;

/** يسمح بتنسيق شروط العقود فقط؛ لا تُحفظ سكربتات أو خصائص HTML من المدخلات. */
final class RichTextSanitizer
{
    /**
     * إعادة بناء HTML من قائمة عناصر آمنة، دون أي خصائص أو روابط أو وسائط خارجية.
     * @param string|null $html
     * @return string|null
     */
    public function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8"><body>'.$html.'</body>', LIBXML_NONET);
            $body = $document->getElementsByTagName('body')->item(0);
            return $body ? $this->children($body) : '';
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    /**
     * @param DOMNode $node
     * @return string
     */
    private function children(DOMNode $node): string
    {
        $result = '';
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $result .= htmlspecialchars($child->textContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                continue;
            }
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            $tag = strtolower($child->nodeName);
            // إسقاط المحتوى النشط كله، بدلاً من إظهار شيفرته كنص داخل العقد.
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template'], true)) {
                continue;
            }
            $content = $this->children($child);
            if ($tag === 'br') {
                $result .= '<br>';
            } elseif (in_array($tag, ['p', 'div', 'h2', 'h3', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'blockquote'], true)) {
                $result .= '<'.$tag.'>'.$content.'</'.$tag.'>';
            } else {
                $result .= $content;
            }
        }
        return $result;
    }
}
