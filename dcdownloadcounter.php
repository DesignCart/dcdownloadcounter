<?php
/**
 * DC Download Counter - system plugin for Joomla 5
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Document\HtmlDocument;

class PlgSystemDcdownloadcounter extends CMSPlugin{
    protected $app;
    protected $autoloadLanguage = true;
    protected $assetsInjected = false;

    public function onAfterRoute(){
        if ($this->app->isClient('administrator')) {
            return;
        }

        $input = $this->app->input;

        if ($input->getInt('dcdownloadcounter', 0) !== 1) {
            return;
        }

        $this->handleAjaxRequest();
    }

    protected function handleAjaxRequest(): void{
        $app   = $this->app;
        $input = $app->input;

        $href = $input->post->getString('file', '');

        if ($href === '') {
            $this->sendJson([
                'success' => false,
                'message' => 'Missing file parameter',
            ]);
        }

        $id = $this->slugFromHref($href);

        $ip = $input->server->getString('REMOTE_ADDR', '0.0.0.0');

        $logDir = JPATH_ROOT . '/media/plg_system_dcdownloadcounter/logs';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/' . $id . '.log';

        $already = false;

        if (file_exists($logFile)) {
            $lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            foreach ($lines as $line) {
                if (str_ends_with($line, '|' . $ip)) {
                    $already = true;
                    break;
                }
            }
        }

        if (!$already) {
            $line = gmdate('Y-m-d H:i:s') . '|' . $ip . PHP_EOL;
            file_put_contents($logFile, $line, FILE_APPEND);
        }

        $count = 0;
        if (file_exists($logFile)) {
            $lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            $count = count($lines);
        }

        $this->sendJson([
            'success' => true,
            'count'   => $count,
            'id'      => $id,
        ]);
    }


    protected function sendJson(array $data): void{
        $app = $this->app;
        $app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
        echo json_encode($data);
        $app->close();
    }

    protected function slugFromHref(string $href): string{
        $href = preg_replace('#^https?://#i', '', $href);
        $slug = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $href);
        $slug = preg_replace('/_+/', '_', $slug);
        return substr($slug, 0, 255);
    }

    public function onContentPrepare($context, &$row, &$params, $page = 0){
        if ($this->app->isClient('administrator')) {
            return;
        }

        if (is_object($row)) {
            if (empty($row->text)) {
                return;
            }
            $text = $row->text;
        } else {
            $text = $row;
        }

        $text = $this->replaceShortcodes($text);

        if (stripos($text, 'dc-download-counter') === false) {

            if (is_object($row)) {
                $row->text = $text;
            } else {
                $row = $text;
            }

            return;
        }

        $modified = $this->processText($text);

        if (is_object($row)) {
            $row->text = $modified;
        } else {
            $row = $modified;
        }

        $this->injectAssets();
    }


    protected function processText(string $html): string{
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $encodingWrap = '<?xml encoding="utf-8" ?>';

        $dom->loadHTML(
            $encodingWrap . '<div id="__dcroot__">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $xpath = new \DOMXPath($dom);

        $nodes = $xpath->query(
            '//a[contains(concat(" ", normalize-space(@class), " "), " dc-download-counter ")]'
        );

        $logDir = JPATH_ROOT . '/media/plg_system_dcdownloadcounter/logs';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        foreach ($nodes as $a) {
            /** @var \DOMElement $a */
            $href = $a->getAttribute('href');
            if ($href === '') {
                continue;
            }

            $id = $this->slugFromHref($href);

            $a->setAttribute('data-dc-id', $id);

            $logFile = $logDir . '/' . $id . '.log';

            $count = 0;

            if (file_exists($logFile)) {
                $lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
                $count = count($lines);
            }

            $span = $dom->createElement('span', (string) $count);
            $span->setAttribute('class', 'dc-download-counter-badge');
            $span->setAttribute('data-dc-id', $id);

            if ($a->nextSibling) {
                $a->parentNode->insertBefore($span, $a->nextSibling);
            } else {
                $a->parentNode->appendChild($span);
            }
        }

        $container = $dom->getElementById('__dcroot__');
        $innerHtml = '';

        foreach ($container->childNodes as $child) {
            $innerHtml .= $dom->saveHTML($child);
        }

        libxml_clear_errors();

        return $innerHtml;
    }


    protected function replaceShortcodes(string $text): string{
        return preg_replace_callback(
            '#\{download\s+href="([^"]+)"\}(.*?)\{/download\}#si',
            function ($matches) {
                $href  = trim($matches[1]);
                $inner = trim($matches[2]);

                return '<a href="' . $href . '" class="dc-download-counter">' . $inner . '</a>';
            },
            $text
        );
    }

    protected function injectAssets(): void{
        if ($this->assetsInjected) return;
        $this->assetsInjected = true;

        $document = $this->app->getDocument();
        if (!$document instanceof HtmlDocument) return;

        $bg   = $this->params->get('counter_bg_color', '#007ec3');
        $fg   = $this->params->get('counter_text_color', '#ffffff');
        $size = $this->params->get('counter_size', 'm');

        switch ($size) {
            case 'xl': $diam = 32; $font = 16; break;
            case 'l':  $diam = 24; $font = 14; break;
            default:   $diam = 18; $font = 12; break;
        }

        $css = <<<CSS
            .dc-download-counter-badge {
                display:inline-flex;
                justify-content:center;
                align-items:center;
                border-radius:999px;
                background:{$bg};
                color:{$fg};
                min-width:{$diam}px;
                min-height:{$diam}px;
                font-size:{$font}px;
                line-height:1;
                margin-left:0.35em;
            }
            CSS;

        $document->addStyleDeclaration($css);

        $root = rtrim(Uri::root(true), '/');
        $document->addScript(
            $root . '/media/plg_system_dcdownloadcounter/js/dc-counter.js',
            ['version' => 'auto'],
            ['defer' => true]
        );

        $endpoint = Uri::root() . 'index.php?dcdownloadcounter=1';
        $document->addScriptDeclaration(
            'window.DC_DOWNLOAD_COUNTER={endpoint:"' . $endpoint . '"};'
        );
    }
}
