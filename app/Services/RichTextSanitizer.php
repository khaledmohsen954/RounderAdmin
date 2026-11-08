<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

/** يسمح بتنسيق شروط العقود فقط؛ لا تُحفظ سكربتات أو خصائص HTML النشطة من المدخلات. */
final class RichTextSanitizer
{
    /**
     * إعادة بناء HTML من قائمة عناصر آمنة، دون أي خصائص خطرة أو سكربتات.
     * @param string|null $html
     * @return string|null
     */
    public function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }
        $allowContractLayout = str_contains($html, 'rounder-contract-doc');
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8"><body>'.$html.'</body>', LIBXML_NONET);
            $body = $document->getElementsByTagName('body')->item(0);
            return $body ? $this->children($body, $allowContractLayout) : '';
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    /**
     * @param DOMNode $node
     * @param bool $allowContractLayout
     * @return string
     */
    private function children(DOMNode $node, bool $allowContractLayout = false): string
    {
        $result = '';
        $basicTags = ['p', 'div', 'h2', 'h3', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'blockquote'];
        $contractExtraTags = ['span', 'h1', 'h4', 'h5', 'h6', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'section', 'header', 'footer'];

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
            $content = $this->children($child, $allowContractLayout);
            if ($tag === 'br') {
                $result .= '<br>';
            } elseif ($allowContractLayout && $tag === 'hr') {
                $attrs = $child instanceof DOMElement ? $this->safeAttributes($child) : '';
                $result .= '<hr'.$attrs.'>';
            } elseif ($allowContractLayout && $tag === 'img' && $child instanceof DOMElement) {
                $attrs = $this->safeAttributes($child, true);
                if (str_contains($attrs, 'src="')) {
                    $result .= '<img'.$attrs.'>';
                }
            } elseif (in_array($tag, $basicTags, true) || ($allowContractLayout && in_array($tag, $contractExtraTags, true))) {
                $attrs = ($allowContractLayout && $child instanceof DOMElement) ? $this->safeAttributes($child) : '';
                $result .= '<'.$tag.$attrs.'>'.$content.'</'.$tag.'>';
            } else {
                $result .= $content;
            }
        }
        return $result;
    }

    /**
     * استخراج الخصائص الآمنة لتصميم العقد فقط مع منع أي بروتوكول نشط.
     * @param DOMElement $element
     * @param bool $isImage
     * @return string
     */
    private function safeAttributes(DOMElement $element, bool $isImage = false): string
    {
        $allowedAttrs = ['class', 'style', 'dir', 'data-contract-field', 'colspan', 'rowspan', 'width', 'align', 'valign'];
        if ($isImage) {
            $allowedAttrs[] = 'src';
            $allowedAttrs[] = 'alt';
        }

        $out = '';
        foreach ($allowedAttrs as $attrName) {
            if (!$element->hasAttribute($attrName)) {
                continue;
            }
            $val = trim($element->getAttribute($attrName));
            if ($val === '') {
                continue;
            }
            if ($attrName === 'style') {
                if (preg_match('/(expression|javascript:|vbscript:|url\s*\()/i', $val)) {
                    continue;
                }
            }
            if ($attrName === 'src') {
                if (!preg_match('#^(https?://|/storage/|/assets/|/admin/providers/|data:image/(png|jpeg|jpg|webp);base64,)#i', $val)) {
                    continue;
                }
            }
            $out .= ' '.$attrName.'="'.htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"';
        }

        return $out;
    }
}
